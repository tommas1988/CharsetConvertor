<?php
namespace Tcc\Test\Convertor\TestAssert;

use Tcc\Convertor\ConvertorFactory as TestConvertorFactory;

class ConvertorFactory extends TestConvertorFactory
{
    public static function setConvertors($convertors)
    {
        static::$availableConvertors = $convertors;
    }
}
