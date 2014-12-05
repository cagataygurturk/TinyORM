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

    public function execute($params = null) {
        try {
            $query = $this->connection->prepare($this->sql);
            $query->execute($params);
            return new Fetcher($query);
        } catch (Exception $e) {
            
        }
    }

    public function useResultCache($timeout = 30, $params = null) {
        $cachekey = 'TinyORM-' . md5($this->sql);
        if ($params) {
            $cachekey.=md5(serialize($params));
        }

        $cached = cache::get($cachekey);
        if ($cached === false) {
            $query = $this->connection->prepare($this->sql);
            $query->execute($params);
            $cached = $query->fetchAll();
            cache::set($cachekey, $cached, $timeout);
        }
        return $cached;
    }

}

?>
