<?php

/**
 * Description of Query
 *
 * @author cagatay
 */


namespace TinyORM;

class Query {

    private $connection;
    public $sql;

    public function __construct($sql, $connection) {
        $this->connection = $connection;
        $this->sql = $sql;
    }

    public function execute() {
        try {
            return new fetcher($this->connection->query($this->sql));
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function useResultCache($timeout = 30) {
        $cachekey = 'TinyORM-' . md5($this->sql);
        $cached = cache::get($cachekey);
        if ($cached === false) {
            $fetcher = new fetcher($this->connection->query($this->sql));
            $cached = $fetcher->fetchAll();
            cache::set($cachekey, $cached, $timeout);
        }
        return $cached;
    }

}

?>
