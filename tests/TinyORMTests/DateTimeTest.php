<?php

use TinyORM\Cache;

class DateTimeTest extends PHPUnit_Framework_TestCase {

    public function testCache() {

        $datetime = new \TinyORM\DateTime();
        $datetime->setInterval("-1 day");
        $this->assertNotNull($datetime->getInterval());
    }

}

?>
