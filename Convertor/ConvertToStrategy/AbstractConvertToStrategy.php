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
    }

    public function convertTo($contents)
    {
        $targetFile = $this->getTargateFile();

        if (!$targetFile instanceof SplFileObject) {
            throw new RuntimeException('invalid targetFile');
        }

        if (!$targetFile->fwrite($contents)) {
            throw new Exception('write contents to target file failed');
        }
    }
    
    public function getTargetFile()
    {
        $convertor = $this->convertor;

        if ($this->targetFile && !$convertor->convertFinish()) {
            return $this->targetFile;
        }

        $filename = $this->generateTargetFileName();

        return $this->targetFile = new SplFileObject($filename, 'a');
    }

    public function restoreConvert()
    {
        $targetFile = $this->getTargetFile();

        unlink($targetFile->getRealPath());
    }
}
