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
    protected $convertingFile;
    protected $targetLocation;
    protected $convertToFile;
    protected $convertFinish = false;

    abstract public function getName();
    abstract protected function doConvert(ConvertFileInterface $convertFile,
        SplFileObject $convertToFile);

    public function setTargetLocation($location)
    {
        $location = str_replace('\\', '/', $location);

        if (!is_dir($location)) {
            throw new Exception('Invalid argument');
        }

        $this->targetLocation = $location;
    }

    public function getTargetLocation()
    {
        if (!$this->targetLocation) {
            throw new Exception('targetLocation has not set');
        }

        return $this->targetLocation;
    }

    public function getConvertingFile()
    {
        return $this->convertingFile;
    }
   
    public function setConvertToStrategy(ConvertToStrategyInterface $strategy)
    {
        $this->convertToStrategy = $strategy;
    }

    public function getConvertToStrategy()
    {
        if (!$this->convertToStrategy) {
            $this->setConvertToStrategy(new LongNameConvertToStrategy);
        }

        return $this->convertToStrategy;
    }

    public function getConvertToFile()
    {
        if ($this->convertToFile && !$this->convertFinish) {
            return $this->convertToFile;
        }

        $this->convertToFile = $this->getConvertToStrategy()->getConvertToFile($this);
        return $this->convertToFile;
    }

    public function convert(ConvertFileInterface $convertFile)
    {
        $this->convertingFile = $convertFile;
        $convertToFile = $this->getConvertToFile();

        $this->convertFinish = false;
        $this->doConvert($convertFile, $convertToFile);
        $this->convertFinish = true;
    }

    public function convertError()
    {
        if (file_exists($this->convertToFile->getPathname())) {
            unlink($this->convertToFile->getPathname());
        }

        $errorMessage = 'Unable to convert file: ' . $this->convertingFile->getFilename()
                      . ' with input charset: ' . $this->convertingFile->getInputCharset()
                      . ' and output charset: ' . $this->convertingFile->getOutputCharset();

        $this->convertFinish  = false;
        $this->convertingFile = null;
        $this->convertToFile  = null;

        throw new Exception($errorMessage);
    }
}
