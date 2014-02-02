<?php

use TinyORM\Cache;


class CacheTest extends PHPUnit_Framework_TestCase {


    public function testCache() {

        $var = "testcache";
        $key = "cachetest";

        Cache::set($key, $var, 10);


        $this->assertEquals($var, Cache::get($key));
    }

}

?>
