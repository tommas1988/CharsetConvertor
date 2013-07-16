<?php
namespace Tcc\Test\Convertor\ConvertToStrategy\TestAssert;

use Tcc\Convertor\ConvertToStrategy\AbstractConvertToStrategy;

class FooConvertToStrategy extends AbstractConvertToStrategy
{
    protected $targetFilename;

    public function generateTargetFileName()
    {
        if (!$this->targetFilename) {
        	$this->targetFilename = tempnam(sys_get_temp_dir(), 'tcc');
        }

        return $this->targetFilename;
    }

    public function setTargetFilename($filename)
    {
    	$this->targetFilename = $filename;
    }
}
