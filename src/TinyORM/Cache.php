<?php

/**
 *
 *
 * @author cagatay
 */

namespace TinyORM;

use Exception;
use Memcache;

class Cache {

    private static $instance;
    private static $config;

    public static function setConfig($config) {
        self::$config = $config;
    }

    private static function inst() {
        if (!self::$instance) {
            self::$instance = new \Memcached();
            if (!self::$config) {
                self::$config = @include 'Config.php';
            }
            if (!self::$config) {
                throw new Exception("TinyORM configuration not set");
            }
            foreach (self::$config['memcache'] as $s) {
                self::$instance->addServer($s['host'], ($s['port'] ? $s['port'] : '11211'));
            }
        }
        return self::$instance;
    }

    private static function cache_key($key) {
        global $_ENV;
        return 'TinyOrm_' . ($_ENV['HPHP'] ? 'hp_' : '') . $key;
    }

    public static function get($key) {

        $o = self::inst()->get(self::cache_key($key));
        if (is_array($o)) {
            if (isset($o['c'])) {
                return $o['c'];
            } else {
                return false;
            }
        } else {
            return $o;
        }
    }

    public static function set($key, $object, $timeout = 10) {
        if (is_array($object)) {
            $object = array('c' => $object);
        }
        return self::inst()->set(self::cache_key($key), $object, $timeout);
    }

    public static function delete($key) {

        return self::inst()->delete(self::cache_key($key) . $key);
    }

}

?>
