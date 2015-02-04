<?php

/**
 * Description of Fetcher
 *
 * @author cagatay
 */

namespace TinyORM;

use PDO;

class Fetcher {

    public function __construct($query) {
        $this->query = $query;
        $this->query->setFetchMode(PDO::FETCH_ASSOC);
    }

    public function fetch() {
        return $this->query->fetch();
    }

    public function fetchNumeric() {
        return $this->query->fetch(PDO::FETCH_NUM);
    }

    public function fetchObject() {
        return $this->query->fetch(PDO::FETCH_OBJ);
    }

    public function fetchAll() {
        return $this->query->fetchAll();
    }

    public function fetchOne() {
        $results = $this->query->fetchAll();
        return (isset($results[0]) ? $results[0] : null);
    }

    public function fetchScalar() {
        $this->query->setFetchMode(PDO::FETCH_NUM);
        $x = $this->query->fetch();
        return $x[0];
    }

    public function affectedRows() {
        return $this->query->rowCount();
    }
}

?>
