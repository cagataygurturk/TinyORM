<?php
require_once realpath(__DIR__ . '/../vendor/autoload.php');


$config=array(
    'dbhost' => "127.0.0.1",
    'dbuser' => "ubuntu",
    'dbpass' => "",
    'database' => "circle_test",
    'memcache' => array(
        array('host' => '127.0.0.1')
    )
);

TinyORM\Cache::setConfig($config);

TinyORM\Database::setConfig($config);
?>