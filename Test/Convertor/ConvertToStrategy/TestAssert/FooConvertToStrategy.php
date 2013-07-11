<?php
namespace Tcc\Test\Convertor\ConvertToStrategy\TestAssert;

use Tcc\Convertor\ConvertToStrategy\AbstractConvertToStrategy;

class FooConvertToStrategy extends AbstractConvertToStrategy
{
    protected function getTargetFileName()
    {
        return 'foo.txt';
    }

    public function setTargetFile(SplFileObject $targetFile)
    {
        $this->targetFile = $targetFile;
    }
}
