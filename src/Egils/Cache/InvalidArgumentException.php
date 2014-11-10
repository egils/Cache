<?php
namespace Egils\Cache;

use Exception;
use Psr\Cache\InvalidArgumentException as InvalidArgumentExceptionInterface;

class InvalidArgumentException extends Exception implements InvalidArgumentExceptionInterface
{
    /**
     * @param mixed $value
     * @param string $expectedType
     * @return static
     */
    public static function typeMismatch($value, $expectedType = "Boolean")
    {
        return new static(ucfirst($expectedType) . " value expected but " . gettype($value) . " given");
    }

}
