<?php

/**
 *
 *
 * @author cagatay
 */

namespace TinyORM;

use TinyORM\Config;
use Memcache;

class Cache {

    private static $instance;

    private static function inst() {
        if (!self::$instance) {
            self::$instance = new Memcache;
            $config=include 'Config.php';
            foreach ($config['memcache'] as $s) {
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
