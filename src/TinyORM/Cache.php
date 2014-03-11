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
            self::$instance = new Memcache();
            if (!self::$config) {
                self::$config = @include 'Config.php';
            }
            if(!self::$config) {
                throw new Exception("TinyORM configuration not set");
            }
            foreach (self::$config['memcache'] as $s) {
                self::$instance->addServer($s['host'], ($s['port'] ? $s['port'] : '11211'));
            }
        }
        return self::$instance;
    }

    public static function get($key) {

        return self::inst()->get('TinyOrm_' . $key);
    }

    public static function set($key, $object, $timeout = 10) {

        return self::inst()->set('TinyOrm_' . $key, $object, false, $timeout);
    }

    public static function delete($key) {

        return self::inst()->delete('TinyOrm_' . $key);
    }

}

?>
