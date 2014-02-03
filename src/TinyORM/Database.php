<?php

namespace TinyORM;

use TinyORM\Query;
use PDO;
use Exception;

class Database {

    private static $objInstance;
    private static $config;

    private function __construct() {
        
    }

    private function __clone() {
        
    }

    public static function setConfig($config) {
        self::$config = $config;
    }

    private static function getInstance() {
        global $_SERVER;
        if (!self::$objInstance) {
            try {
                if (!self::$config) {
                    self::$config = @include 'Config.php';
                }
                if (!self::$config) {
                    throw new Exception("TinyORM configuration not set");
                }
                self::$objInstance = new PDO('mysql:host=' . self::$config['dbhost'] . ';dbname=' . self::$config['database'], self::$config['dbuser'], self::$config['dbpass'], array(
                    PDO::ATTR_PERSISTENT => true,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                    PDO::MYSQL_ATTR_DIRECT_QUERY => true)
                );
                self::$objInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
        return self::$objInstance;
    }

    public static function query($sql) {
        return new Query($sql, self::getInstance());
    }

    public static function begin() {
        self::query("set autocommit=0")->execute();
        self::query("begin")->execute();
    }

    public static function commit() {
        return;
        self::query("commit")->execute();
    }

    public static function get_insert_id() {
        return self::getInstance()->lastInsertId();
    }

}

?>