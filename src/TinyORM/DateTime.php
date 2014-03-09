<?php

namespace TinyORM;

class DateTime extends \DateTime {

    private $interval;

    public function setNow() {
        $this->interval = "now()";
    }

    public function setYesterday() {
        $this->interval = "-1 day";
    }

    public function setInterval($interval) {
        $this->interval = $interval;
    }

    public function getInterval() {
        return $this->interval;
    }
    
    public function __toString() {
        return $this->format('Y-m-d H:i:s');
    }

}

?>
