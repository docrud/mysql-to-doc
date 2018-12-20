<?php
/**
 * 功能：连接数据库，读取表结构，根据定义的模板输出字符串
 *
 * @Author DoCRUD <chao@docrud.com>
 */

class MysqlToDoc {
    private $dbName;
    private $db;

    /**
     * 构造函数
     * 
     * 连接数据库
     *
     * @param string $host         数据库地址，包含端口。例：127.0.0.1:3306
     * @param string $dbName       数据库名字
     * @param string $user         数据库用户名
     * @param string $password     数据库密码
     */
    public function __construct ($host, $dbName, $user, $password = '')
    {
        $dsn = sprintf("mysql:host=%s;dbname=%s", $host, $dbName);

        try {
            $this->db = new PDO($dsn, $user, $password);
            $this->db->exec("SET CHARACTER SET utf8");
        } catch (PDOException $e) {
            echo '连接失败：' . $e->getMessage();
        }

        $this->dbName = $dbName;
    }

    /**
     * 获取数据库中所有表名和表注释
     *
     * @return array $tables       所有表名和表注释的二位数组
     */
    private function getTables ()
    {
        $query = $this->db->prepare('SELECT table_name, table_comment FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = :dbName');
        $query->bindParam(':dbName', $this->dbName, PDO::PARAM_STR);
        $query->execute();

        $tables = $query->fetchAll(PDO::FETCH_ASSOC);

        return $tables;
    }

    /**
     * 获取指定表的所有字段信息
     *
     * @param string $tableName    指定表名
     *
     * @return array $columns      返回指定表所有字段的数组
     */
    private function getColumns ($tableName)
    {
        $query = $this->db->prepare('SHOW FULL COLUMNS FROM ' . $tableName);
        $query->execute();

        $columns = $query->fetchAll(PDO::FETCH_ASSOC);

        return $columns;
    }

    /**
     * 在实例化后直接指定此方法就能直接输出
     *
     * @param array $template      表模板和字段模板
     *
     * @return string $string      返回字符串
     */
    public function run ($template = array())
    {
        $markdown = "\n\r";
        $template = count($template) ? $template : ['table' => $this->tableTemplate(), 'column' => $this->columnTemplate()];

        $tables = $this->getTables();
        foreach ($tables as $table) {
            $columns = $this->getColumns($table['table_name']);
            $columnMarkdown = '';
            foreach ($columns as $column) {
                $columnMarkdown .= $this->replaceTemplate($column, $template['column']) . "\n\r";
            }
            $markdown .= str_replace(['{tableName}', '{tableComment}', '{columns}'], [$table['table_name'], $table['table_comment'] ?: $table['table_name'], $columnMarkdown], $template['table']) . "\n\r\n\r";
        }

        $markdown = str_replace("``", '', $markdown);
        return $markdown;
    }

    /**
     * 根据字段模板替换
     *
     * @param array $column        字段数组
     * @param string $template     字段模板
     *
     * @return string $string      返回替换后字符串
     */
    private function replaceTemplate ($column, $template)
    {
        $search = [
            '{field}',
            '{type}',
            '{collation}',
            '{null}',
            '{default}',
            '{key}',
            '{extra}',
            '{comment}'
        ];

        $replace = [
            $column['Field'],
            $column['Type'],
            $column['Collation'],
            $this->getNullName($column['Null']),
            $column['Default'],
            $this->getKeyName($column['Key']),
            $column['Extra'],
            $column['Comment']
        ];

        $string = str_replace($search, $replace, $template);
        $string = preg_replace('/\s+/', ' ', $string);
        return $string;
    }

    /**
     * 默认表模板
     *
     * @return string              返回表模板
     */
    private function tableTemplate()
    {
        return "### `{tableName}` {tableComment}\n\r{columns}";
    }

    /**
     * 默认字段模板
     *
     * @return string              返回默认模板
     */
    private function columnTemplate ()
    {
        return "- `{field}` {type} {collation} {null} {default} {key} {extra} {comment}";
    }

    /**
     * 字段参数转换
     *
     * @param string $key          key 参数，PRI, UNI, MUL
     *
     * @return string $keyName     返回对应的参数名称
     */
    private function getKeyName($key)
    {
        switch ($key) {
            case 'PRI':
                $keyName = 'Primary Key';
                break;

            case 'UNI':
                $keyName = 'Unique Key';
                break;

            case 'MUL':
                $keyName = 'Key';
                break;

            default:
                $keyName = $key;
        }

        return $keyName;
    }

    /**
     * 字段参数转换
     *
     * @param string $nul          null 参数，YES or NO
     *
     * @return string              返回空字符串或 NOT NULL
     */
    private function getNullName ($null)
    {
        if ($null == 'YES') {
            return '';
        } else {
            return 'NOT NULL';
        }
    }
}
