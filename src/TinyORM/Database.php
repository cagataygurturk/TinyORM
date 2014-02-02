<?php

namespace TinyORM;

use TinyORM\Query;
use TinyORM\Config;
use TinyORM\Fetcher;
use PDO;

class Database {

    private static $objInstance;
    private $sql;
    private $connection;

    private function __construct() {
        
    }

    private function __clone() {
        
    }

    private static function getInstance() {
        global $_SERVER;
        if (!self::$objInstance) {
            try {
                $config = include 'Config.php';
                self::$objInstance = new PDO('mysql:host=' . $config['dbhost'] . ';dbname=' . $config['database'], $config['dbuser'], $config['dbpass'], array(
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

    public function useResultCache($timeout = 30) {
        $cachekey = '_qc_' . md5($this->sql);
        $cached = Cache::get($cachekey);
        if ($cached === false) {
            $fetcher = new Fetcher($this->connection->query($this->sql));
            $cached = $fetcher->fetchAll();
            cache::set($cachekey, $cached, $timeout);
        }
        return $cached;
    }

}

?>