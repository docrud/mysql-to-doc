<?php
class MysqlToDoc {
    private $dbName;
    private $db;

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

    private function getTables ()
    {
        $query = $this->db->prepare('SELECT table_name, table_comment FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = :dbName');
        $query->bindParam(':dbName', $this->dbName, PDO::PARAM_STR);
        $query->execute();

        $tables = $query->fetchAll(PDO::FETCH_ASSOC);

        return $tables;
    }

    private function getColumns ($tableName)
    {
        $query = $this->db->prepare('SHOW FULL COLUMNS FROM ' . $tableName);
        $query->execute();

        $columns = $query->fetchAll(PDO::FETCH_ASSOC);

        return $columns;
    }

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

    private function tableTemplate()
    {
        return "### `{tableName}` {tableComment}\n\r{columns}";
    }

    private function columnTemplate ()
    {
        return "- `{field}` {type} {collation} {null} {default} {key} {extra} {comment}";
    }

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

    private function getNullName ($null)
    {
        if ($null == 'YES') {
            return '';
        } else {
            return 'NOT NULL';
        }
    }
}
