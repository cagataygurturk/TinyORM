<?php
require_once realpath(__DIR__ . '/../vendor/autoload.php');


$config=array(
    'dbhost' => "DB",
    'dbuser' => "USER",
    'dbpass' => "PASS",
    'database' => "DBNAME",
    'memcache' => array(
        array('host' => '127.0.0.1')
    )
);

TinyORM\Cache::setConfig($config);

TinyORM\Database::setConfig($config);
?>