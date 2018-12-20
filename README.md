## Mysql to doc，根据数据库结构生成文档（字符串）

## 输出模板说明
模板分为表模板和字段模板

### 表模板
主要参数有：
- `{tableName}` 数据表名称
- `{tableComment}` 数据表注释
- `{columns}` 所有列，字段模板生成字符串后填充进来

### 字段模板
主要参数有：
- `{field}` 字段名称
- `{type}` 字段类型
- `{collation}` 字符集
- `{null}` 是否为可空：`YES` or `NO`
- `{key}` 索引类型：`PRI = 主键`，`UNI = 唯一索引`，`MUL = 普通索引`
- `{default}` 默认值
- `{extra}` 扩展信息，自增：`AUTO_INCREMENT`
- `{privileges}` 权限
- `{comment}` 注释
- `{nullName}` null 转换后名称，`YES` 输出空字符串，`NO` 输出 `NOT NULL`
- `{keyName}` key 转换后名称，`PRI = Primary Key = 主键`，`UNI = Unique Key = 唯一索引`，`MUL = Key = 普通索引`

### 模板参数，二维数组
```php
<?php
$templates = [
    'table' => '{tableName} {tableComment} {columns}',
    'column' => '{field} {type} {collation} {null} {key} {default} {extra} {comment} {nullName} {keyName}',
];

```

## Demo
```php
<?php
// 引入文件
include_once('MysqlToDoc.php');

// 实例化
$mtd = new MysqlToDoc('127.0.0.1', 'dbname', 'username', 'password');

// 模板
$templates = [
    'table' => "{tableName} {tableComment} \r\n{columns}",
    'column' => "{field} {type} {collation} {nullName} {keyName} {default} {extra} {comment} ",
];

// 执行
$doc = $mtd->run($templates);

?>

<textarea style="width: 700px; height: 350px;"><?= $doc ?></textarea>

```

## 联系
[email](chao@docrud.com)