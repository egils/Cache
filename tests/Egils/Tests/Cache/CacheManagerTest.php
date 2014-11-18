<?php
namespace Egils\Tests\Cache;

use Egils\Cache\CacheManager;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Cache\CacheItemPoolInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CacheManagerTest extends TestCase
{
    /** @var CacheManager */
    private $manager;

    /** @var CacheItemPoolInterface|MockObject */
    private $adapter;

    public function setUp()
    {
        $this->adapter = $this->getMock('Psr\Cache\CacheItemPoolInterface');
        $this->manager = new CacheManager(['name' => $this->adapter]);
    }

    public function testInitialisation_Success()
    {
        $this->assertNotNull($this->manager);
        $this->assertTrue($this->manager->hasAdapter('name'));
        $this->assertTrue($this->manager->hasAdapterInstance($this->adapter));
        $this->assertSame($this->adapter, $this->manager->getAdapter('name'));
    }

    public function testInitialisationWithNoKey_CacheExceptionRaised()
    {
        $this->setExpectedException('Psr\Cache\CacheException', 'Adapter name expected to be string, integer given');

        $this->manager = new CacheManager([$this->adapter]);

        $this->assertNull($this->manager);
    }

    public function testInitialisationWithInvalidPrimaryAdapterName_CacheExceptionRaised()
    {
        $this->setExpectedException('Psr\Cache\CacheException', 'Adapter \'non-existing-adapter-name\' does not exist');

        $this->manager = new CacheManager(['name' => $this->adapter], 'non-existing-adapter-name');

        $this->assertNull($this->manager);
    }

    public function testInitialisationWithPrimaryAdapterName()
    {
        $this->manager = new CacheManager(['name' => $this->adapter], 'name');

        $this->assertNotNull($this->manager);

        $adapter = $this->manager->getPrimaryAdapter();

        $this->assertInstanceOf('Psr\Cache\CacheItemPoolInterface', $adapter);
        $this->assertSame($this->adapter, $adapter);
    }

    public function testAddExistingAdapter_CacheExceptionRaised()
    {
        $this->setExpectedException('Psr\Cache\CacheException', 'Adapter \'name\' already exists');

        $this->manager->addAdapter('name', $this->adapter);
    }

    public function testRequestNonExistingAdapter_GetNullValue()
    {
        $this->assertNull($this->manager->getAdapter('notExist'));
    }

    public function testRemoveAdapter_OtherAdapterStays()
    {
        $newAdapter = $this->getMock('Psr\Cache\CacheItemPoolInterface');
        $this->manager->addAdapter('newAdapter', $newAdapter);

        $this->manager->removeAdapter('name');

        $this->assertTrue($this->manager->hasAdapter('newAdapter'));
        $this->assertTrue($this->manager->hasAdapterInstance($newAdapter));
        $this->assertSame($newAdapter, $this->manager->getAdapter('newAdapter'));

        $this->assertFalse($this->manager->hasAdapter('name'));
        $this->assertFalse($this->manager->hasAdapterInstance($this->adapter));
        $this->assertNull($this->manager->getAdapter('name'));
    }

    public function testSetNonExistingPrimaryAdapterName_CacheExceptionRaised()
    {
        $this->setExpectedException('Psr\Cache\CacheException', 'Adapter \'non-existing-adapter-name\' does not exist');

        $this->manager->setPrimaryAdapterName('non-existing-adapter-name');
    }

    public function testGetNotSetPrimaryAdapter_CacheExceptionRaised()
    {
        $this->setExpectedException('Psr\Cache\CacheException', 'Primary adapter is not set');

        $this->manager->getPrimaryAdapter();
    }

    public function testGetPrimaryAdapter()
    {
        $this->manager->setPrimaryAdapterName('name');

        $adapter = $this->manager->getPrimaryAdapter();

        $this->assertInstanceOf('Psr\Cache\CacheItemPoolInterface', $adapter);
        $this->assertSame($this->adapter, $adapter);
    }
}
