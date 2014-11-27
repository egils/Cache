<?php
/*
 * This file is part of the Egils\Cache package.
 *
 * (c) Egidijus Lukauskas <egils.ps@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Egils\Component\Cache;

use Psr\Cache\CacheItemPoolInterface;

interface CacheManagerInterface
{
    /**
     * @param string $name
     * @return CacheItemPoolInterface
     * @throws CacheException
     */
    public function getAdapter($name);

    /**
     * Is adapter with given name already set?
     *
     * @param string $name
     * @return boolean
     */
    public function hasAdapter($name);

    /**
     * Is adapter already defined?
     *
     * @param CacheItemPoolInterface $adapter
     * @return boolean
     */
    public function hasAdapterInstance(CacheItemPoolInterface $adapter);

    /**
     * @throws CacheException
     * @return CacheItemPoolInterface
     */
    public function getDefaultAdapter();
}
