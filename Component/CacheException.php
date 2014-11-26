<?php
/*
 * This file is part of the Egils\Component\Cache package.
 *
 * (c) Egidijus Lukauskas <egils.ps@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Egils\Component\Cache;

use Exception;
use Psr\Cache\CacheException as CacheExceptionInterface;

class CacheException extends Exception implements CacheExceptionInterface
{
    /**
     * @param string $name
     * @return CacheException
     */
    public static function adapterAlreadyExists($name)
    {
        return new static('Adapter \'' . $name . '\' already exists');
    }

    /**
     * @param string $name
     * @return CacheException
     */
    public static function adapterNameNotString($name)
    {
        return new static('Adapter name expected to be string, ' . gettype($name) . ' given');
    }

    /**
     * @param string $name
     * @return CacheException
     */
    public static function adapterDoesNotExist($name)
    {
        return new static('Adapter \'' . $name . '\' does not exist');
    }

    /** @return CacheException */
    public static function defaultAdapterNotSet()
    {
        return new static('Default adapter is not set');
    }
}
