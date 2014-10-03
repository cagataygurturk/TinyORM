<?php

/**
 *
 *
 * @author cagatay
 */

namespace TinyORM;

use Exception;
use Memcached;

class Cache {

    private static $instance;
    private static $config;

    public static function setConfig($config) {
        self::$config = $config;
    }

    private static function inst() {
        if (!self::$instance) {
            self::$instance = new Memcached('tinyorm');
            if (!self::$config) {
                self::$instance = @include 'Config.php';
            }
            $ss = self::$instance->getServerList();
            if (empty($ss)) {
                self::$instance->setOption(Memcached::OPT_RECV_TIMEOUT, 1000);
                self::$instance->setOption(Memcached::OPT_SEND_TIMEOUT, 1000);
                self::$instance->setOption(Memcached::OPT_TCP_NODELAY, true);
                self::$instance->setOption(Memcached::OPT_SERVER_FAILURE_LIMIT, 50);
                self::$instance->setOption(Memcached::OPT_CONNECT_TIMEOUT, 500);
                self::$instance->setOption(Memcached::OPT_RETRY_TIMEOUT, 300);
                self::$instance->setOption(Memcached::OPT_DISTRIBUTION, Memcached::DISTRIBUTION_CONSISTENT);
                self::$instance->setOption(Memcached::OPT_REMOVE_FAILED_SERVERS, true);
                self::$instance->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
                if (!self::$config) {
                    throw new Exception("TinyORM configuration not set");
                }
                foreach (self::$config['memcache'] as $s) {
                    self::$instance->addServer($s['host'], ($s['port'] ? $s['port'] : '11211'));
                }
            }
        }
        return self::$instance;
    }

    private static function cache_key($key) {
        global $_ENV;
        return 'TinyOrm_' . ($_ENV['HPHP'] ? 'hp_' : '') . $key;
    }

    public static function get($key) {

        return self::inst()->get(self::cache_key($key));
    }

    public static function set($key, $object, $timeout = 10) {

        return self::inst()->set(self::cache_key($key), $object, $timeout);
    }

    public static function delete($key) {

        return self::inst()->delete(self::cache_key($key) . $key);
    }

}

?>
