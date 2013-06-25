<?php
namespace Tcc\Test\Convertor\TestAssert;

use Tcc\Convertor\AbstractConvertor;
use SplFileObject;

class FooConvertor extends AbstractConvertor
{
    protected $triggerError;

    public function getName()
    {
        return 'foo';
    }

    protected function doConvert(ConvertFileInterface $convertFile,
        SplFileObject $convertToFile
    ){
        if ($this->triggerError) {
            $this->convertError();
        }
    }

    public function setTriggerConvertError($flag)
    {
        $this->triggerError = (bool) $flag;
    }

    public function setConvertFinishFlag($flag)
    {
        $this->convertFinish = (bool) $flag;
    }
}
