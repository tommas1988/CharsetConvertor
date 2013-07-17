<?php
namespace Tcc\Test\Convertor\ConvertToStrategy\TestAssert;

use Tcc\Convertor\ConvertToStrategy\AbstractConvertToStrategy;

class FooConvertToStrategy extends AbstractConvertToStrategy
{
    protected $targetFilename;

    public function getTargetFileName()
    {
        if (!$this->targetFilename) {
        	$this->targetFilename = tempnam(sys_get_temp_dir(), 'tcc');
        }

        return $this->targetFilename;
    }

    public function getTargetFile()
    {
    	return $this->targetFile;
    }
}
