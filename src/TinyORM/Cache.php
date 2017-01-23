<?php

/**
 *
 *
 * @author cagatay
 */

namespace TinyORM;

use TinyORM\cache\AbstractCacheProvider;
use TinyORM\cache\LocalProvider;
use TinyORM\cache\MemcacheProvider;
use TinyORM\cache\NotFoundException;

class Cache
{

    /**
     * @var AbstractCacheProvider
     */
    private static $cacheAuthority;
    private static $config;

    const VAL_FALSE = '-F-';
    const VAL_NULL = '-N-';

    public static function setConfig($config, $useLocalMemcache = true)
    {
        self::$config = $config;

        /**
         * @var $caches AbstractCacheProvider[]
         */
        $caches = array();

        $caches[] = new LocalProvider();

        if ($useLocalMemcache) {
            $caches[] = new MemcacheProvider(array(array(
                'host' => '127.0.0.1'
            )));
        }

        $caches[] = new MemcacheProvider($config['memcache']);

        for ($i = 0; $i < count($caches) - 1; $i++) {
            $caches[$i]->addAuthority($caches[$i + 1]);
        }

        self::$cacheAuthority = $caches[0];
    }

    public static function get($key)
    {
        try {
            return self::$cacheAuthority->get($key);
        } catch (NotFoundException $notFoundException) {
            return false;
        }
    }

    public static function set($key, $object, $timeout = 10)
    {
        self::$cacheAuthority->set($key, $object, $timeout);
    }

    public static function delete($key)
    {
        self::$cacheAuthority->delete($key);
    }
}
