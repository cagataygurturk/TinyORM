<?php

namespace TinyORM\cache;

abstract class AbstractCacheProvider
{
    /**
     * @var AbstractCacheProvider
     */
    private $authorityCache;

    public function addAuthority(AbstractCacheProvider $authorityCache)
    {
        $this->authorityCache = $authorityCache;
    }

    /**
     * @return AbstractCacheProvider
     */
    protected function getAuthorityCache()
    {
        return $this->authorityCache;
    }

    protected function hasAuthority()
    {
        return $this->authorityCache !== null;
    }


    abstract function delete($key);

    abstract function set($key, $object, $timeout = 10);

    /**
     * @param $key
     * @return mixed
     */
    abstract function get($key);
}