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
