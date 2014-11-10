<?php
namespace Egils\Tests\Cache;

use Egils\Cache\CacheItem;
use PHPUnit_Framework_TestCase as TestCase;

class CacheItemTest extends TestCase
{
    public function testCacheItemSetHit_IsHitMatchesSetValue()
    {
        $cacheItem = new CacheItem('key');

        $cacheItem->setHit(true);
        $this->assertTrue($cacheItem->isHit());

        $cacheItem->setHit(false);
        $this->assertFalse($cacheItem->isHit());
    }

    public function testCacheItemSetInteger_InvalidArgumentExceptionRaised()
    {
        $cacheItem = new CacheItem('key');

        $this->setExpectedException('Psr\Cache\InvalidArgumentException', "Boolean value expected but integer given");

        $cacheItem->setHit(1);
    }
}
