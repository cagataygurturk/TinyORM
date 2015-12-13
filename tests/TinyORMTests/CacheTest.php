<?php

use TinyORM\Cache;

class CacheTest extends PHPUnit_Framework_TestCase
{

    public function testCache()
    {
        $var = "testcache";
        $key = "cachetest" . time();
        Cache::set($key, $var, 10);
        $this->assertEquals($var, Cache::get($key));
    }

    public function testArrayCache()
    {
        $key = "cachetestarray" . time();
        $var = array('test' => 'test');
        Cache::set($key, $var, 10);
        $this->assertArrayHasKey('test', Cache::get($key));
    }

    public function testNullCache()
    {
        $key = 'nullcache' . time();
        Cache::set($key, null, 10);
        $this->assertNull(Cache::get($key));
    }

    public function testFalseCache()
    {
        $key = 'falsecache' . time();
        Cache::set($key, false, 10);
        $this->assertFalse(Cache::get($key));
    }

    public function testNotFoundCache()
    {
        $this->assertFalse(Cache::get('testfalse'));
    }


}
