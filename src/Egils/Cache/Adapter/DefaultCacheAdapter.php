<?php
/*
 * This file is part of the Egils\Cache package.
 *
 * (c) Egidijus Lukauskas <egils.ps@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Egils\Cache\Adapter;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class DefaultCacheAdapter implements CacheItemPoolInterface
{
    /** @var CacheItemPoolInterface */
    private $defaultAdapter;

    public function __construct(CacheItemPoolInterface $cacheItemPool)
    {
        $this->defaultAdapter = $cacheItemPool;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        return $this->defaultAdapter->getItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        return $this->defaultAdapter->getItems($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->defaultAdapter->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        return $this->defaultAdapter->deleteItems($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        return $this->defaultAdapter->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        return $this->defaultAdapter->saveDeferred($item);
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        return $this->defaultAdapter->commit();
    }
}
