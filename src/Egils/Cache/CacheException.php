<?php
namespace Egils\Cache;

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
        return new static("Adapter '" . $name . "' already exists");
    }

    /**
     * @param string $name
     * @return CacheException
     */
    public static function adapterNameNotString($name)
    {
        return new static ("Adapter name expected to be string, " . gettype($name) . " given");
    }
}
