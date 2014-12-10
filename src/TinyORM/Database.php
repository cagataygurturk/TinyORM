<?php

namespace TinyORM;

use TinyORM\Query;
use PDO;
use Exception;

class Database {

    private static $masterInstance;
    private static $slaveInstance;
    private static $config;

    const QUERY_UPDATE = 1;
    const QUERY_SELECT = 2;

    private static $queryTypes = array(
        'insert', 'update', 'delete', 'replace', 'master', 'truncate', 'rename', 'alter', 'drop', 'create', 'sql_calc_found_rows', 'found_rows'
    );

    private function __construct() {
        
    }

    private function __clone() {
        
    }

    public static function setConfig($config) {
        self::$config = $config;
    }

    private static function getInstance() {
        return self::getMasterInstance();
    }

    private static function getConnection($host, $database, $user, $pass) {
        $con = new PDO('mysql:host=' . $host . ';dbname=' . $database, $user, $pass, array(
            //PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            //PDO::MYSQL_ATTR_DIRECT_QUERY => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
        );

        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $con;
    }

    private static function getMasterInstance() {
        if (!self::$masterInstance) {
            try {
                if (!self::$config) {
                    self::$config = @include 'Config.php';
                }
                if (!self::$config) {
                    throw new Exception("TinyORM configuration not set");
                }

                self::$masterInstance = self::getConnection(self::$config['dbhost'], self::$config['database'], self::$config['dbuser'], self::$config['dbpass']);
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
        return self::$masterInstance;
    }

    private static function getSlaveInstance() {
        if ((rand(0, 1) == 1)) {
            return self::getMasterInstance();
        }
        if (!self::$slaveInstance) {
            try {
                if (!self::$config) {
                    self::$config = @include 'Config.php';
                }
                if (!self::$config) {
                    throw new Exception("TinyORM configuration not set");
                }

                if (self::$config['slaves']) {
                    $slave = self::$config['slaves'][array_rand(self::$config['slaves'])];
                } else {
                    return self::getMasterInstance();
                }

                self::$slaveInstance = self::getConnection($slave['dbhost'], self::$config['database'], $slave['dbuser'], $slave['dbpass']);
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
        return self::$slaveInstance;
    }

    private static function getQueryType($sql) {
        $sql=strtolower($sql);
        foreach (self::$queryTypes as $type) {
            if (strpos($sql, $type) !== false) {
                return self::QUERY_UPDATE;
            }
        }

        return self::QUERY_SELECT;
    }

    public static function query($sql) {
        switch (self::getQueryType($sql)) {
            case self::QUERY_UPDATE:
                $con = self::getMasterInstance();
                break;
            case self::QUERY_SELECT:
                $con = self::getSlaveInstance();
                break;
        }


        return new Query($sql, $con);
    }

    public static function begin() {
        self::query("set autocommit=0")->execute();
        self::query("begin")->execute();
    }

    public static function commit() {
        self::query("commit")->execute();
        self::query("set autocommit=1")->execute();
    }

    public static function get_insert_id() {
        return intval(self::getInstance()->lastInsertId());
    }

}
