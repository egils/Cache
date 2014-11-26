<?php
/*
 * This file is part of the Egils\Component\Cache package.
 *
 * (c) Egidijus Lukauskas <egils.ps@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Egils\Component\Cache\Adapter;

use Doctrine\Common\Cache\CacheProvider;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use DateTime;

class DoctrineCacheAdapter implements CacheItemPoolInterface
{
    /** @var CacheProvider */
    private $provider;

    /** @var array|CacheItemInterface[] */
    private $deferred = [];

    public function __construct(CacheProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        return $this->provider->fetch($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        if (empty($keys)) {
            return [];
        }

        $items = [];
        foreach ($keys as $key) {
            if ($this->provider->contains($key) && $item = $this->provider->fetch($key)) {
                $items[$key] = $item;
            } else {
                $items[$key] = null;
            }
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->provider->flushAll();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            if ($this->provider->contains($key)) {
                $this->provider->delete($key);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        $this->doSave($item);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferred[] = $item;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $result = true;
        foreach ($this->deferred as $deferred) {
            $result = $result && $this->doSave($deferred);
        }

        $this->deferred = [];

        return $result;
    }

    private function doSave(CacheItemInterface $item)
    {
        $now = new DateTime();
        $ttl = $item->getExpiration()->format('U') - $now->format('U');

        if ($ttl < 0) {
            return false;
        }

        return $this->provider->save($item->getKey(), $item, $ttl);
    }
}
