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

    abstract public function generateTargetFileName();

    public function setConvertor(AbstractConvertor $convertor)
    {
        $this->convertor = $convertor;
        return $this;
    }

    public function convertTo($contents)
    {
        $targetFile = $this->getTargetFileObject();

        if ($targetFile->fwrite($contents) !== strlen($contents)) {
            throw new RuntimeException('write contents to target file failed');
        }
    }

    public function restoreConvert()
    {
        if (!$this->convertor->convertFinish()) {
            $targetFile = $this->getTargetFileObject();

            $filename = $targetFile->getRealPath();
            unset($targetFile, $this->targetFile);
            if (!unlink($filename)) {
                throw new RuntimeException(sprintf(
                    'Can not delete target file: %s', $filename));
            }
        }
    }
    
    protected function getTargetFileObject()
    {
        $convertor = $this->convertor;

        if ($this->targetFile && !$convertor->convertFinish()) {
            return $this->targetFile;
        }

        $filename = $this->generateTargetFileName();
        $this->targetFile = new SplFileObject($filename, 'a');
        
        return $this->targetFile;
    }
}
