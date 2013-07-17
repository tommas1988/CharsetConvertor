<?php
namespace Tcc\Test\Convertor\TestAssert;

use Tcc\Convertor\ConvertToStrategy\AbstractConvertToStrategy;

class FooConvertToStrategy extends AbstractConvertToStrategy
{
    protected $converted;

    public function convertTo($contents)
    {
        $this->converted = $contents;
    }

    public function getTargetFileName()
    {

    }

    public function getConverted()
    {
        return $this->converted;
    }
}
