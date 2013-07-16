<?php
namespace Tcc\Test\Convertor\ConvertToStrategy\TestAssert;

use Tcc\ConvertFile\ConvertFile;
use Tcc\Convertor\AbstractConvertor;

class FooConvertor extends AbstractConvertor
{
    public function getName()
    {
    	return 'foo';
    }

    public function convertFinish()
    {
    	return false;
    }

    public function setConvertFile(ConvertFile $convertFile)
    {
        $this->convertFile = $convertFile;
    }

    protected function doConvert()
    {

    }
}
