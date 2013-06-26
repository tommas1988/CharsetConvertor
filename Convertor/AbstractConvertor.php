<?php
namespace Tcc\Convertor;

use Tcc\ConvertFile\ConvertFileInterface;
use Tcc\Convertor\ConvertToStrategy\ConvertToStrategyInterface;
use Tcc\Convertor\ConvertToStrategy\LongNameConvertToStrategy;
use SplFileObject;
use Exception;

class AbstractConvertor
{
    protected $convertToStrategy;
    protected $convertFile;
    protected $targetLocation;
    protected $convertFinish = false;

    abstract public function getName();
    abstract protected function doConvert();

    public function setTargetLocation($location)
    {
        $location = str_replace('\\', '/', $location);

        if (!is_dir($location)) {
            throw new Exception('Invalid argument');
        }

        $this->targetLocation = $location;
        return $this;
    }

    public function getTargetLocation()
    {
        if (!$this->targetLocation) {
            throw new Exception('targetLocation has not set');
        }

        return $this->targetLocation;
    }

    public function setConvertFile(ConvertFileInterface $convertFile)
    {
        $this->convertingFile = $convertFile;
        return $this;
    }

    public function getConvertFile()
    {
        return $this->convertingFile;
    }
   
    public function setConvertToStrategy(ConvertToStrategyInterface $strategy)
    {
        $this->convertToStrategy = $strategy;
        return $this;
    }

    public function getConvertToStrategy()
    {
        if (!$this->convertToStrategy) {
            $this->setConvertToStrategy(new LongNameConvertToStrategy($this));
        }

        return $this->convertToStrategy;
    }

    public function convert(ConvertFileInterface $convertFile)
    {
        $this->setConvertFile($convertFile);

        $this->convertFinish = false;
        $this->doConvert();
        $this->convertFinish = true;
    }

    public function convertError()
    {
        $this->getConvertToStrategy()->restoreConvert();

        $errorMessage = 'Unable to convert file: ' . $this->convertFile->getFilename()
                      . ' with input charset: ' . $this->convertFile->getInputCharset()
                      . ' and output charset: ' . $this->convertFile->getOutputCharset();

        $this->convertFinish  = false;
        $this->convertFile = null;

        throw new Exception($errorMessage);
    }

    public function convertFinish()
    {
        return (bool) $this->convertFinish;
    }
}
