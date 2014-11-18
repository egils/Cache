<?php
namespace Egils\Cache;

use Psr\Cache\CacheItemInterface;
use DateTime;

class CacheItem implements CacheItemInterface
{
    /** @var mixed */
    private $value;

    /** @var string */
    private $key;

    /** @var DateTime */
    private $expiration;

    /** @var boolean */
    private $hit;

    /**
     * @param string $key
     * @param DateTime|integer|null $ttl
     * @param boolean $hit
     * @throws InvalidArgumentException
     */
    public function __construct($key, $ttl = null, $hit = false)
    {
        $this->key = $key;
        $this->setExpiration($ttl);
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
        if (true === is_numeric($ttl)) {
            $this->expiration = new DateTime('now +' . $ttl . ' seconds');
        } elseif ($ttl instanceof DateTime) {
            $this->expiration = $ttl;
        } else {
            $this->expiration = new DateTime('now +10 years');
        }

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
