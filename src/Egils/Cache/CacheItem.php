<?php
namespace Egils\Cache;

use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface
{
    /** @var mixed */
    private $value;

    /** @var string */
    private $key;

    /** @var integer */
    private $expiration;

    /** @var boolean */
    private $hit;

    /**
     * @param string $key
     * @param integer $ttl
     * @param boolean $hit
     */
    public function __construct($key, $ttl = 300, $hit = false)
    {
        $this->key = $key;
        $this->expiration = $ttl;
        $this->setHit($hit);
    }

    /**
     * @inheritdoc
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function set($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isHit()
    {
        return $this->hit;
    }

    /**
     * @inheritdoc
     */
    public function exists()
    {
        return $this->isHit();
    }

    /**
     * @inheritdoc
     */
    public function setExpiration($ttl = null)
    {
        $this->expiration = $ttl;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @param boolean $hit
     *
     * @return CacheItem
     * @throws InvalidArgumentException not a boolean value given
     */
    public function setHit($hit)
    {
        if (false === is_bool($hit)) {
            throw InvalidArgumentException::typeMismatch($hit, "Boolean");
        }

        $this->hit = $hit;

        return $this;
    }
}
