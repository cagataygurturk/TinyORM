<?php

namespace TinyORM\cache;

class CachedObject
{
    private $object;

    /**
     * @var int
     */
    private $expiresAt;

    /**
     * CachedObject constructor.
     * @param $object
     * @param int $ttl
     */
    private function __construct($object, $ttl)
    {
        $this->object = $object;
        if ($ttl > 0) {
            $this->expiresAt = time() + $ttl;
        }
    }

    /**
     * @param $object
     * @param $timeout
     * @return CachedObject
     */
    public static function create($object, $timeout)
    {
        if ($object instanceof CachedObject) {
            return $object;
        }

        return new CachedObject($object, $timeout);
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return int
     */
    public function getRemainingTTL()
    {
        if ($this->expiresAt == null) {
            return null;
        }

        return $this->expiresAt - time();
    }
}