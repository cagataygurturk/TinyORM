<?php

use TinyORM\Cache;

class CacheTest extends PHPUnit_Framework_TestCase {

    public function testCache() {
        $var = "testcache";
        $key = "cachetest" . time();
        Cache::set($key, $var, 10);
        $this->assertEquals($var, Cache::get($key));
    }

    public function testArrayCache() {
        $key = "cachetestarray" . time();
        $var = array('test' => 'test');
        Cache::set($key, $var, 10);
        $this->assertArrayHasKey('test', Cache::get($key));
    }

    public function testArrayNullValue() {
        $key = "cachetestnull" . time();
        $var = null;
        Cache::set($key, $var, 10);
        $this->assertNull(Cache::get($key));
    }

}
