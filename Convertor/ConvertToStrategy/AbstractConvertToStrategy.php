<?php
namespace Tcc\Convertor\ConvertToStrategy;

use Tcc\Convertor\AbstractConvertor;
use SplFileObject;
use RuntimeException;
use Exception;

abstract class AbstractConvertToStrategy
{
    protected $convertor;
    protected $targetFile;

    abstract public function getTargetFileName();

    public function setConvertor(AbstractConvertor $convertor)
    {
        $this->convertor = $convertor;
        return $this;
    }

    public function convertTo($contents)
    {
        if ($this->targetFile === null
            || !$this->targetFile instanceof SplFileObject
        ) {
            $filename = $this->getTargetFileName();
            $this->targetFile = new SplFileObject($filename, 'a');
        }

        if ($this->targetFile->fwrite($contents) !== strlen($contents)) {
            throw new RuntimeException('write contents to target file failed');
        }
    }

    public function restoreConvert()
    {
        if ($this->targetFile !== null) {
            $filename = $this->targetFile->getRealPath();
            //reset target file to null to be able delete it; 
            $this->targetFile = null;

            if (!unlink($filename)) {
                throw new RuntimeException(sprintf(
                    'Can not delete target file: %s', $filename));
            }
        }
    }

    public function reset()
    {
        $this->targetFile = null;
        return $this;
    }
}
