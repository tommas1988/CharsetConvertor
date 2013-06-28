<?php
namespace Tcc\Convertor;

use Tcc\ConvertFile\ConvertFileInterface;
use Tcc\Convertor\ConvertToStrategy\AbstractConvertToStrategy;
use Tcc\Convertor\ConvertToStrategy\LongNameConvertToStrategy;
use SplFileObject;
use Exception;

abstract class AbstractConvertor
{
    protected $convertToStrategy;
    protected $convertFile;
    protected $targetLocation;
    protected $convertFinish = false;

    abstract public function getName();
    abstract protected function doConvert();

    public function setTargetLocation($location)
    {
        $this->targetLocation = static::canonicalPath($location);
        
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
        $this->convertFile = $convertFile;
        return $this;
    }

    public function getConvertFile()
    {
        return $this->convertFile;
    }
   
    public function setConvertToStrategy(AbstractConvertToStrategy $strategy)
    {
        $strategy->setConvertor($this);
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

    public static function canonicalPath($path)
    {
        if (!$path = realpath($path)) {
            throw new Exception();
        }

        $path = rtrim(str_replace('\\', '/', $path), '/');
        return $path;
    }
}
