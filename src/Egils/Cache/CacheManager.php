<?php
/*
 * This file is part of the Egils\Cache package.
 *
 * (c) Egidijus Lukauskas <egils.ps@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Egils\Cache;

use Psr\Cache\CacheItemPoolInterface;

class CacheManager
{
    /** @var CacheItemPoolInterface[] */
    private $adapters = [];

    /** @var string */
    private $defaultAdapter = null;

    public function __construct(array $adapters = [], $defaultAdapterName = null)
    {
        foreach ($adapters as $name => $adapter) {
            $this->addAdapter($name, $adapter);
        }

        if (null !== $defaultAdapterName) {
            $this->setDefaultAdapterName($defaultAdapterName);
        }
    }

    /**
     * @param string $name
     * @param CacheItemPoolInterface $adapter
     * @param bool $default Should this adapter be marked as default?
     *
     * @throws CacheException
     */
    public function addAdapter($name, CacheItemPoolInterface $adapter, $default = false)
    {
        if (false === is_string($name)) {
            throw CacheException::adapterNameNotString($name);
        }

        if (true === array_key_exists($name, $this->adapters)) {
            throw CacheException::adapterAlreadyExists($name);
        }

        $this->adapters[$name] = $adapter;

        if (true === $default) {
            $this->setDefaultAdapterName($name);
        }
    }

    /**
     * @param string $name
     * @return CacheItemPoolInterface
     * @throws CacheException
     */
    public function getAdapter($name)
    {
        if (false === $this->hasAdapter($name)) {
            throw CacheException::adapterDoesNotExist($name);
        }

        return $this->adapters[$name];
    }

    /**
     * Is adapter with given name already set?
     *
     * @param string $name
     * @return boolean
     */
    public function hasAdapter($name)
    {
        return isset($this->adapters[$name]);
    }

    /**
     * Is adapter already defined?
     *
     * @param CacheItemPoolInterface $adapter
     * @return boolean
     */
    public function hasAdapterInstance(CacheItemPoolInterface $adapter)
    {
        return in_array($adapter, $this->adapters, true);
    }

    /**
     * @param string $name
     */
    public function removeAdapter($name)
    {
        if (true === $this->hasAdapter($name)) {
            unset($this->adapters[$name]);
        }
    }

    /**
     * @param string $name
     * @throws CacheException
     */
    public function setDefaultAdapterName($name)
    {
        if (false === $this->hasAdapter($name)) {
            throw CacheException::adapterDoesNotExist($name);
        }

        $this->defaultAdapter = $name;
    }

    /**
     * @throws CacheException
     * @return CacheItemPoolInterface
     */
    public function getDefaultAdapter()
    {
        if (null === $this->defaultAdapter) {
            throw CacheException::defaultAdapterNotSet();
        }

        return $this->getAdapter($this->defaultAdapter);
    }
}
