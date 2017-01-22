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
    public function getAuthorityCache()
    {
        return $this->authorityCache;
    }

    protected function hasAuthority()
    {
        return $this->authorityCache !== null;
    }

    protected function populateObjectWithMetadata($object, $timeout)
    {
        return array(
            'e' => time() + $timeout,
            'o' => $object
        );
    }

    abstract function delete($key);

    abstract function set($key, $object, $timeout = 10);

    /**
     * @param $key
     * @return mixed
     */
    abstract function get($key);
}