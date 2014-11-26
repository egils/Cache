<?php
/*
 * This file is part of the Egils\Cache package.
 *
 * (c) Egidijus Lukauskas <egils.ps@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Egils\Tests\Cache;

use Egils\Component\Cache\CacheItem;
use PHPUnit_Framework_TestCase as TestCase;

class CacheItemTest extends TestCase
{
    /** @var CacheItem */
    private $cacheItem;

    public function setUp()
    {
        date_default_timezone_set('Europe/Vilnius');
        $this->cacheItem = new CacheItem('key');
    }

    public function testCacheItemSetHit_IsHitMatchesSetValue()
    {
        $this->cacheItem->setHit(true);
        $this->assertTrue($this->cacheItem->isHit());
        $this->assertTrue($this->cacheItem->exists());

        $this->cacheItem->setHit(false);
        $this->assertFalse($this->cacheItem->isHit());
        $this->assertFalse($this->cacheItem->exists());
    }

    public function testCacheItemSetInteger_InvalidArgumentExceptionRaised()
    {
        $this->setExpectedException('Psr\Cache\InvalidArgumentException', 'Boolean value expected but integer given');

        $this->cacheItem->setHit(1);
    }

    public function testSetExpirationInteger_ExpiresInSeconds()
    {
        $now = new \DateTime('now');
        $this->cacheItem->setExpiration(15);

        $expiration = $this->cacheItem->getExpiration();
        $diff =  $expiration->format('U') - $now->format('U');

        $this->assertLessThanOrEqual(15, $diff);
        $this->assertGreaterThanOrEqual(14, $diff);
    }

    public function testSetExpirationNull_ExpiresIn10Years()
    {
        $now = new \DateTime('now +10 years');
        $this->cacheItem->setExpiration(null);

        $expiration = $this->cacheItem->getExpiration();
        $diff =  $expiration->format('U') - $now->format('U');

        $this->assertLessThanOrEqual(1, $diff);
        $this->assertGreaterThanOrEqual(0, $diff);
    }

    public function testSetExpirationDateTime_Same()
    {
        $now = new \DateTime('now');
        $this->cacheItem->setExpiration($now);

        $expiration = $this->cacheItem->getExpiration();

        $this->assertEquals($now, $expiration);
    }

    public function testKeyValueRemainsUnchanged()
    {
        $value = [
            'value' => ['another', 'array']
        ];

        $this->cacheItem->set($value);

        $this->assertSame($value, $this->cacheItem->get());
        $this->assertEquals('key', $this->cacheItem->getKey());
    }
}
