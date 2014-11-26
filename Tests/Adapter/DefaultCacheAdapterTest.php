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

use Egils\Component\Cache\Adapter\DefaultCacheAdapter;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class DefaultCacheAdapterTest extends TestCase
{
    /** @var DefaultCacheAdapter */
    private $adapter;

    /** @var CacheItemPoolInterface|MockObject */
    private $cacheItemPool;

    /** @var CacheItemInterface|MockObject */
    private $cacheItem;

    public function setUp()
    {
        $this->cacheItemPool = $this->getMock('Psr\Cache\CacheItemPoolInterface');
        $this->adapter = new DefaultCacheAdapter($this->cacheItemPool);

        $this->cacheItem = $this->getMock('Psr\Cache\CacheItemInterface');
    }

    public function testGetItem()
    {
        $key = 'cache-key';
        $this->cacheItemPool
            ->expects($this->once())
            ->method('getItem')
            ->with($key)
            ->willReturn($this->cacheItem);

        $cacheItem = $this->adapter->getItem($key);

        $this->assertSame($this->cacheItem, $cacheItem);
    }

    public function testGetItems()
    {
        $keys = ['cache-key'];
        $this->cacheItemPool
            ->expects($this->once())
            ->method('getItems')
            ->with($keys)
            ->willReturn([$this->cacheItem]);

        $cacheItems = $this->adapter->getItems($keys);

        $this->assertCount(1, $cacheItems);
        $this->assertSame($this->cacheItem, $cacheItems[0]);
    }

    public function testClear()
    {
        $this->cacheItemPool
            ->expects($this->once())
            ->method('clear')
            ->willReturn(true);

        $this->assertTrue($this->adapter->clear());
    }

    public function testDeleteItems()
    {
        $keys = ['cache-key'];
        $this->cacheItemPool
            ->expects($this->once())
            ->method('deleteItems')
            ->with($keys)
            ->willReturn($this->cacheItemPool);

        $cacheItemPool = $this->adapter->deleteItems($keys);

        $this->assertSame($this->cacheItemPool, $cacheItemPool);
    }

    public function testSave()
    {
        $this->cacheItemPool
            ->expects($this->once())
            ->method('save')
            ->with($this->cacheItem)
            ->willReturn($this->cacheItemPool);

        $cacheItemPool = $this->adapter->save($this->cacheItem);

        $this->assertSame($this->cacheItemPool, $cacheItemPool);
    }

    public function testSaveDeferred()
    {
        $this->cacheItemPool
            ->expects($this->once())
            ->method('saveDeferred')
            ->with($this->cacheItem)
            ->willReturn($this->cacheItemPool);

        $cacheItemPool = $this->adapter->saveDeferred($this->cacheItem);

        $this->assertSame($this->cacheItemPool, $cacheItemPool);
    }

    public function testCommit()
    {
        $this->cacheItemPool
            ->expects($this->once())
            ->method('commit')
            ->willReturn(true);

        $this->assertTrue($this->adapter->commit());
    }
}
