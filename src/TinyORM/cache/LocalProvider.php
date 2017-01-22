<?php

namespace TinyORM\cache;


class LocalProvider extends AbstractCacheProvider
{
    private $dataStore = array();

    function delete($key)
    {
        if ($this->hasAuthority()) {
            $this->getAuthorityCache()->delete($key);
        }
    }

    function set($key, $object, $timeout = 10)
    {
        if ($this->hasAuthority()) {
            $this->getAuthorityCache()->set($key, $object, $timeout);
        }

        $this->writeToStore($key, $object, $timeout);
    }

    function writeToStore($key, $object, $timeout = 10)
    {
        $this->dataStore[$key] = $object;
    }

    function get($key)
    {
        if (isset($this->dataStore[$key])) {
            return $this->dataStore[$key];
        }

        if ($this->hasAuthority()) {
            $found = $this->getAuthorityCache()->get($key);
            $this->writeToStore($key, $found);
            return $found;
        }

        throw new NotFoundException();
    }

}