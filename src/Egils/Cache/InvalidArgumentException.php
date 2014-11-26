<?php
/*
 * This file is part of the Egils\Cache package.
 *
 * (c) Egidijus Lukauskas <egils.ps@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Egils\Cache;

use Exception;
use Psr\Cache\InvalidArgumentException as InvalidArgumentExceptionInterface;

class InvalidArgumentException extends Exception implements InvalidArgumentExceptionInterface
{
    /**
     * @param mixed $value
     * @param string $expectedType
     * @return InvalidArgumentException
     */
    public static function typeMismatch($value, $expectedType = 'Boolean')
    {
        return new static(ucfirst($expectedType) . ' value expected but ' . gettype($value) . ' given');
    }
}
