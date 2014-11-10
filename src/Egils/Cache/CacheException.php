<?php
namespace Egils\Cache;

use Exception;
use Psr\Cache\CacheException as CacheExceptionInterface;

class CacheException extends Exception implements CacheExceptionInterface
{
}
