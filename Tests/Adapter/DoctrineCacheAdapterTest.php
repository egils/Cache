<?php
/*
 * This file is part of the Egils\Cache package.
 *
 * (c) Egidijus Lukauskas <egils.ps@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Egils\Tests\Cache\Adapter;

use Doctrine\Common\Cache\CacheProvider;
use Egils\Component\Cache\Adapter\DoctrineCacheAdapter;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Cache\CacheItemInterface;

class DoctrineCacheAdapterTest extends TestCase
{
    /** @var DoctrineCacheAdapter */
    private $adapter;

    /** @var CacheProvider|MockObject */
    private $cacheProvider;

    /** @var CacheItemInterface|MockObject */
    private $cacheItem;

    public function setUp()
    {
        $this->cacheProvider = $this->getMockForAbstractClass(
            'Doctrine\Common\Cache\CacheProvider',
            [],
            '',
            true,
            true,
            true,
            ['fetch', 'contains', 'flushAll', 'delete', 'save']
        );
        $this->adapter = new DoctrineCacheAdapter($this->cacheProvider);

        $this->cacheItem = $this->getMock('Psr\Cache\CacheItemInterface');
    }

    public function testGetItem()
    {
        $key = 'cache-key';
        $this->cacheProvider
            ->expects($this->once())
            ->method('fetch')
            ->with($key)
            ->willReturn($this->cacheItem);

        $cacheItem = $this->adapter->getItem($key);

        $this->assertSame($this->cacheItem, $cacheItem);
    }

    public function testGetItems_AllItemsFound()
    {
        $keys = ['cache-key-1', 'cache-key-2'];
        $this->cacheProvider
            ->expects($this->exactly(count($keys)))
            ->method('contains')
            ->withConsecutive([$keys[0]], [$keys[1]])
            ->willReturn(true);

        $otherCacheItem = $this->getMock('Psr\Cache\CacheItemInterface');
        $this->cacheProvider
            ->expects($this->exactly(count($keys)))
            ->method('fetch')
            ->willReturnMap([[$keys[0], $this->cacheItem], [$keys[1], $otherCacheItem]]);

        $cacheItems = $this->adapter->getItems($keys);

        $this->assertCount(2, $cacheItems);
        $this->assertSame($this->cacheItem, $cacheItems[$keys[0]]);
        $this->assertSame($otherCacheItem, $cacheItems[$keys[1]]);
    }

    public function testGetItems_OnlySecondItemFound()
    {
        $keys = ['cache-key-1', 'cache-key-2'];
        $this->cacheProvider
            ->expects($this->exactly(count($keys)))
            ->method('contains')
            ->willReturnMap([[$keys[0], false], [$keys[1], true]]);

        $otherCacheItem = $this->getMock('Psr\Cache\CacheItemInterface');
        $this->cacheProvider
            ->expects($this->once())
            ->method('fetch')
            ->with($keys[1])
            ->willReturn($otherCacheItem);

        $cacheItems = $this->adapter->getItems($keys);

        $this->assertCount(2, $cacheItems);
        $this->assertEquals($keys, array_keys($cacheItems));
        $this->assertNull($cacheItems[$keys[0]]);
        $this->assertSame($otherCacheItem, $cacheItems[$keys[1]]);
    }

    public function testGetItems_FirstFetchFailed()
    {
        $keys = ['cache-key-1', 'cache-key-2'];
        $this->cacheProvider
            ->expects($this->exactly(count($keys)))
            ->method('contains')
            ->withConsecutive([$keys[0]], [$keys[1]])
            ->willReturn(true);

        $otherCacheItem = $this->getMock('Psr\Cache\CacheItemInterface');
        $this->cacheProvider
            ->expects($this->exactly(count($keys)))
            ->method('fetch')
            ->willReturnMap([[$keys[0], false], [$keys[1], $otherCacheItem]]);

        $cacheItems = $this->adapter->getItems($keys);

        $this->assertCount(2, $cacheItems);
        $this->assertEquals($keys, array_keys($cacheItems));
        $this->assertNull($cacheItems[$keys[0]]);
        $this->assertSame($otherCacheItem, $cacheItems[$keys[1]]);
    }

    public function testClear()
    {
        $this->cacheProvider
            ->expects($this->once())
            ->method('flushAll')
            ->willReturn(true);

        $this->assertTrue($this->adapter->clear());
    }

    public function testDeleteItems()
    {
        $key = 'cache-key';
        $this->cacheProvider
            ->expects($this->once())
            ->method('contains')
            ->with($key)
            ->willReturn(true);

        $this->cacheProvider
            ->expects($this->once())
            ->method('delete')
            ->with($key)
            ->willReturn(true);

        $cacheItemPool = $this->adapter->deleteItems([$key]);
        $this->assertInstanceOf('Psr\Cache\CacheItemPoolInterface', $cacheItemPool);
    }

    public function testSave_CacheExpired()
    {
        date_default_timezone_set('Europe/Vilnius');

        $this->cacheProvider
            ->expects($this->never())
            ->method('save');

        $this->cacheItem
            ->expects($this->once())
            ->method('getExpiration')
            ->willReturn(new \DateTime('now -30 seconds'));

        $cacheItemPool = $this->adapter->save($this->cacheItem);
        $this->assertInstanceOf('Psr\Cache\CacheItemPoolInterface', $cacheItemPool);
    }

    public function testSave()
    {
        date_default_timezone_set('Europe/Vilnius');

        $cacheKey = 'cache-key';
        $this->cacheItem
            ->expects($this->once())
            ->method('getExpiration')
            ->willReturn(new \DateTime('now +30 seconds'));
        $this->cacheItem
            ->expects($this->once())
            ->method('getKey')
            ->willReturn($cacheKey);

        $this->cacheProvider
            ->expects($this->once())
            ->method('save')
            ->with($cacheKey, $this->cacheItem, 30)
            ->willReturn(true);

        $cacheItemPool = $this->adapter->save($this->cacheItem);
        $this->assertInstanceOf('Psr\Cache\CacheItemPoolInterface', $cacheItemPool);
    }

    public function testSaveDeferredAndCommit()
    {
        $keys = ['cache-key-1', 'cache-key-2'];
        $otherCacheItem = $this->getMock('Psr\Cache\CacheItemInterface');

        $this->cacheItem
            ->expects($this->once())
            ->method('getExpiration')
            ->willReturn(new \DateTime('now +30 seconds'));

        $otherCacheItem
            ->expects($this->once())
            ->method('getExpiration')
            ->willReturn(new \DateTime('now +30 seconds'));

        $cacheItemPool = $this->adapter->saveDeferred($this->cacheItem);
        $this->assertInstanceOf('Psr\Cache\CacheItemPoolInterface', $cacheItemPool);
        $cacheItemPool = $this->adapter->saveDeferred($otherCacheItem);
        $this->assertInstanceOf('Psr\Cache\CacheItemPoolInterface', $cacheItemPool);

        $this->cacheProvider
            ->expects($this->exactly(count($keys)))
            ->method('save')
            ->willReturn(true);

        $this->assertTrue($this->adapter->commit());
    }

    public function testSaveDeferredAndCommit_SeconSaveFails()
    {
        $keys = ['cache-key-1', 'cache-key-2'];
        $otherCacheItem = $this->getMock('Psr\Cache\CacheItemInterface');

        $this->cacheItem
            ->expects($this->once())
            ->method('getExpiration')
            ->willReturn(new \DateTime('now +30 seconds'));

        $otherCacheItem
            ->expects($this->once())
            ->method('getExpiration')
            ->willReturn(new \DateTime('now +30 seconds'));

        $cacheItemPool = $this->adapter->saveDeferred($this->cacheItem);
        $this->assertInstanceOf('Psr\Cache\CacheItemPoolInterface', $cacheItemPool);
        $cacheItemPool = $this->adapter->saveDeferred($otherCacheItem);
        $this->assertInstanceOf('Psr\Cache\CacheItemPoolInterface', $cacheItemPool);

        $this->cacheProvider
            ->expects($this->at(0))
            ->method('save')
            ->willReturn(true);

        $this->cacheProvider
            ->expects($this->at(1))
            ->method('save')
            ->willReturn(false);

        $this->assertFalse($this->adapter->commit());
    }
}
