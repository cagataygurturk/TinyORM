<?php

/**
 *
 *
 * @author cagatay
 */

namespace TinyORM;

use Exception;
use Memcached;

class Cache
{

    private static $instance;
    private static $config;
    private static $localCache = array();

    const VAL_FALSE = '-F-';
    const VAL_NULL = '-N-';

    public static function setConfig($config)
    {
        self::$config = $config;
    }

    private static function localGet($key)
    {
        $key = self::cache_key($key);
        if (isset(self::$localCache[$key])) {
            return self::$localCache[$key];
        }

        throw new \Exception();
    }

    private static function localSet($key, $value)
    {
        self::$localCache[self::cache_key($key)] = $value;
        return true;
    }

    private static function inst()
    {
        if (!self::$instance) {
            self::$instance = new Memcached(md5(serialize(self::$config['memcache'])));
            self::$instance->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);

            if (!count(self::$instance->getServerList())) {
                if (!self::$config) {
                    self::$config = @include 'Config.php';
                }
                if (!self::$config) {
                    throw new Exception("TinyORM configuration not set");
                }
                $servers = array();
                foreach (self::$config['memcache'] as $s) {
                    $servers[] = array($s['host'], (isset($s['port']) ? $s['port'] : '11211'));
                }
                self::$instance->addServers($servers);
            }
        }
        return self::$instance;
    }

    private static function cache_key($key)
    {
        global $_ENV;
        return 'i' . (isset($_ENV['HPHP']) ? 'h' : '') . $key;
    }

    public static function get($key)
    {
        try {
            $val = self::localGet($key);
        } catch (\Exception $e) {
            $val = self::inst()->get(self::cache_key($key));
        }


        self::localSet($key, $val);

        if ($val === static::VAL_NULL) {
            return null;
        }

        if ($val === static::VAL_FALSE) {
            return false;
        }

        return $val;
    }

    public static function set($key, $object, $timeout = 10)
    {
        if (null === $object) {
            $object = static::VAL_NULL;
        }

        if (false === $object) {
            $object = static::VAL_FALSE;
        }

        self::localSet($key, $object);
        return self::inst()->set(self::cache_key($key), $object, $timeout);
    }

    public static function delete($key)
    {
        return self::inst()->delete(self::cache_key($key));
    }

}
