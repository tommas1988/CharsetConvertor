<?php
namespace Tcc\Convertor\ConvertToStrategy;

use Tcc\Convertor\AbstractConvertor;

class AbstractConvertToStrategy
{
    protected $convertor;
    protected $targetFile;

    abstract protected function generateTargetFileName();

    public function __construct(AbstractConvertor $convertor)
    {
        $this->convertor = $convertor;
    }

    public function convertTo($contents)
    {
        $targetFile = $this->getTargateFile();

        if (!$targetFile->fwrite($contents)) {
            throw new Exception('write contents to target file failed');
        }
    }

    public function restoreConvert()
    {
        $targetFile = $this->getTargetFile();

        unlink($targetFile->getRealPath());
    }

    protected function getTargetFile()
    {
        $convertor = $this->convertor;

        if ($this->targetFile && !$convertor->convertFinish()) {
            return $this->targetFile;
        }

        $filename = $this->getTargetFileName();

        return $this->targetFile = new SplFileObject($filename, 'a');
    }
}
