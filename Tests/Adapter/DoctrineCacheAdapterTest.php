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
use Egils\Component\Cache\CacheItem;

class DoctrineCacheAdapterTest extends TestCase
{
    /** @var DoctrineCacheAdapter */
    private $adapter;

    /** @var CacheProvider|MockObject */
    private $cacheProvider;

    /** @var CacheItem|MockObject */
    private $cacheItem;

    /** @var string */
    private $cacheKey;

    public function setUp()
    {
        date_default_timezone_set('Europe/Vilnius');

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

        $this->cacheKey = 'cache-key';
        $this->cacheItem = $this->getMock('Egils\Component\Cache\CacheItem', [], [$this->cacheKey]);
    }

    public function testGetItem()
    {
        $this->cacheProvider
            ->expects($this->once())
            ->method('fetch')
            ->with($this->cacheKey)
            ->willReturn($this->cacheItem);

        $this->cacheProvider
            ->expects($this->once())
            ->method('contains')
            ->with($this->cacheKey)
            ->willReturn(true);

        $this->cacheItem
            ->expects($this->once())
            ->method('setHit')
            ->with(true)
            ->willReturn($this->cacheItem);

        $cacheItem = $this->adapter->getItem($this->cacheKey);

        $this->assertSame($this->cacheItem, $cacheItem);
    }

    public function testGetItem_fetchDeferred()
    {
        $this->cacheItem
            ->expects($this->any())
            ->method('getKey')
            ->willReturn($this->cacheKey);

        $cacheItemPool = $this->adapter->saveDeferred($this->cacheItem);
        $this->assertSame($this->adapter, $cacheItemPool);

        $this->cacheProvider
            ->expects($this->never())
            ->method('contains');
        $this->cacheProvider
            ->expects($this->never())
            ->method('fetch');

        $cacheItem = $this->adapter->getItem($this->cacheKey);

        $this->assertSame($this->cacheItem, $cacheItem);
    }

    public function testGetItem_ItemNotFound()
    {
        $this->cacheProvider
            ->expects($this->once())
            ->method('contains')
            ->with($this->cacheKey)
            ->willReturn(false);

        $cacheItem = $this->adapter->getItem($this->cacheKey);

        $this->assertInstanceOf('Psr\Cache\CacheItemInterface', $cacheItem);
        $this->assertEquals($this->cacheKey, $cacheItem->getKey());
        $this->assertFalse($cacheItem->isHit());
    }

    public function testGetItems_EmptyKeysSetGiven()
    {
        $cacheItems = $this->adapter->getItems([]);

        $this->assertEmpty($cacheItems);
    }

    public function testGetItems_AllItemsFound()
    {
        $keys = [$this->cacheKey, 'cache-key-2'];
        $this->cacheProvider
            ->expects($this->exactly(count($keys)))
            ->method('contains')
            ->withConsecutive([$keys[0]], [$keys[1]])
            ->willReturn(true);

        $otherCacheItem = $this->getMock('Egils\Component\Cache\CacheItem', [], [$keys[1]]);
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
        $keys = [$this->cacheKey, 'cache-key-2'];
        $this->cacheProvider
            ->expects($this->exactly(count($keys)))
            ->method('contains')
            ->willReturnMap([[$keys[0], false], [$keys[1], true]]);

        $otherCacheItem = $this->getMock('Egils\Component\Cache\CacheItem', [], [$keys[1]]);
        $otherCacheItem
            ->expects($this->once())
            ->method('setHit')
            ->with(true)
            ->willReturn($otherCacheItem);

        $this->cacheProvider
            ->expects($this->once())
            ->method('fetch')
            ->with($keys[1])
            ->willReturn($otherCacheItem);

        $cacheItems = $this->adapter->getItems($keys);

        $this->assertCount(2, $cacheItems);
        $this->assertEquals($keys, array_keys($cacheItems));

        $firstCacheItem = $cacheItems[$keys[0]];
        $this->assertInstanceOf('Egils\Component\Cache\CacheItem', $firstCacheItem);
        $this->assertfalse($firstCacheItem->isHit());

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
        $this->cacheProvider
            ->expects($this->once())
            ->method('contains')
            ->with($this->cacheKey)
            ->willReturn(true);

        $this->cacheProvider
            ->expects($this->once())
            ->method('delete')
            ->with($this->cacheKey)
            ->willReturn(true);

        $cacheItemPool = $this->adapter->deleteItems([$this->cacheKey]);
        $this->assertInstanceOf('Psr\Cache\CacheItemPoolInterface', $cacheItemPool);
    }

    public function testSave_CacheExpired()
    {
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
        $this->cacheItem
            ->expects($this->once())
            ->method('getExpiration')
            ->willReturn(new \DateTime('now +30 seconds'));
        $this->cacheItem
            ->expects($this->once())
            ->method('getKey')
            ->willReturn($this->cacheKey);

        $this->cacheProvider
            ->expects($this->once())
            ->method('save')
            ->with($this->cacheKey, $this->cacheItem, 30)
            ->willReturn(true);

        $cacheItemPool = $this->adapter->save($this->cacheItem);
        $this->assertInstanceOf('Psr\Cache\CacheItemPoolInterface', $cacheItemPool);
    }

    public function testSaveDeferredAndCommit()
    {
        $keys = [$this->cacheKey, 'cache-key-2'];
        $otherCacheItem = $this->getMock('Egils\Component\Cache\CacheItem', [], [$keys[1]]);

        $this->cacheItem
            ->expects($this->once())
            ->method('getExpiration')
            ->willReturn(new \DateTime('now +30 seconds'));
        $this->cacheItem
            ->expects($this->any())
            ->method('getKey')
            ->willReturn($this->cacheKey);

        $otherCacheKey = 'cache-key-2';
        $otherCacheItem
            ->expects($this->once())
            ->method('getExpiration')
            ->willReturn(new \DateTime('now +30 seconds'));
        $otherCacheItem
            ->expects($this->any())
            ->method('getKey')
            ->willReturn($otherCacheKey);

        $cacheItemPool = $this->adapter->saveDeferred($this->cacheItem);
        $this->assertSame($this->adapter, $cacheItemPool);
        $cacheItemPool = $this->adapter->saveDeferred($otherCacheItem);
        $this->assertSame($this->adapter, $cacheItemPool);

        $this->cacheProvider
            ->expects($this->exactly(count($keys)))
            ->method('save')
            ->willReturn(true);

        $this->assertTrue($this->adapter->commit());
    }

    public function testSaveDeferredAndCommit_SecondSaveFails()
    {
        $otherCacheItem = $this->getMock('Psr\Cache\CacheItemInterface');

        $this->cacheItem
            ->expects($this->once())
            ->method('getExpiration')
            ->willReturn(new \DateTime('now +30 seconds'));
        $this->cacheItem
            ->expects($this->any())
            ->method('getKey')
            ->willReturn($this->cacheKey);

        $otherCacheKey = 'cache-key-2';
        $otherCacheItem
            ->expects($this->once())
            ->method('getExpiration')
            ->willReturn(new \DateTime('now +30 seconds'));
        $otherCacheItem
            ->expects($this->any())
            ->method('getKey')
            ->willReturn($otherCacheKey);

        $cacheItemPool = $this->adapter->saveDeferred($this->cacheItem);
        $this->assertSame($this->adapter, $cacheItemPool);
        $cacheItemPool = $this->adapter->saveDeferred($otherCacheItem);
        $this->assertSame($this->adapter, $cacheItemPool);

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
