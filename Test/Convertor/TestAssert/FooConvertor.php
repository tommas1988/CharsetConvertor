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

    protected function doConvert()
    {
        if ($this->triggerError) {
            $this->convertError();
        }
    }

    public function setTriggerConvertErrorFlag($flag)
    {
        $this->triggerError = (bool) $flag;
    }
}
