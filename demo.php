<?php

include_once('MysqlToDoc.php');

// 实例化
$mtd = new MysqlToDoc('127.0.0.1', 'dbname', 'username', 'password');
// 执行
$doc = $mtd->run();
