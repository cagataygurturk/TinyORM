<?php

namespace TinyORM\cache;

use Memcached;

class MemcacheProvider extends AbstractCacheProvider
{

    /**
     * @var Memcached
     */
    private $memcache;

    /**
     * MemcacheProvider constructor.
     * @param array $servers
     */
    public function __construct(array $servers)
    {
        $this->memcache = new Memcached(md5(serialize($servers)));
        $this->memcache->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
        if (count($this->memcache->getServerList()) == 0) {
            foreach ($servers as $s) {
                $this->memcache->addServer($s['host'], (isset($s['port']) ? $s['port'] : '11211'));
            }
        }
    }

    private
    function getKey($key)
    {
        return 'to_' . $key;
    }


    function delete($key)
    {
        $key = $this->getKey($key);

        if ($this->hasAuthority()) {
            $this->getAuthorityCache()->delete($key);
        }
        $this->memcache->delete($key);
    }


    function set($key, $object, $timeout = 10)
    {
        $key = $this->getKey($key);

        $cachedObject = CachedObject::create($object, $timeout);

        if ($this->hasAuthority()) {
            $this->getAuthorityCache()->set($key, $cachedObject, $timeout);
        }

        $this->writeToStore($key,
            $cachedObject,
            $timeout);
    }

    private function writeToStore($key, $object, $timeout = 10)
    {
        $this->memcache->set($key,
            $object,
            ($timeout == null ? 0 : $timeout));
    }

    /**
     * @param $key
     * @return mixed
     * @throws NotFoundException
     */
    function get($key)
    {
        $key = $this->getKey($key);
        /**
         * @var $found CachedObject
         */
        $found = $this->memcache->get($key);

        if ($this->memcache->getResultCode() === Memcached::RES_SUCCESS) {

            if (!($found instanceof CachedObject)) {
                throw new NotFoundException();
            }

            return $found->getObject();
        }

        if ($this->hasAuthority()) {
            $found = $this->getAuthorityCache()->get($key);
            if ($found instanceof CachedObject) {
                $this->writeToStore($key,
                    $found,
                    $found->getRemainingTTL());
                return $found->getObject();
            }
        }

        throw new NotFoundException();

    }

}