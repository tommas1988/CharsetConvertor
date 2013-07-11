<?php
namespace Tcc\Test\Convertor\Mock;

use Tcc\Convertor\ConvertToStrategy\AbstractConvertToStrategy;
use Tcc\Convertor\AbstractConvertor;
use SplFileObject;

class MockConvertToStrategy extends AbstractConvertToStrategy
{
    protected $converted;

    public function convertTo($contents)
    {
        $this->converted = $contents;
    }

    public function generateTargetFileName()
    {

    }

    public function getConverted()
    {
        return $this->converted;
    }
}
