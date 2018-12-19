<?php

include_once('MysqlToDoc.php');

$mtd = new MysqlToDoc('127.0.0.1', 'dbname', 'username', 'password');
$doc = $mtd->run();