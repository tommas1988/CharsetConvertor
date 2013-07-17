<?php
namespace Tcc\Convertor;

use Tcc\ConvertFile\ConvertFile;
use Tcc\Convertor\ConvertToStrategy\AbstractConvertToStrategy;
use Tcc\Convertor\ConvertToStrategy\LongNameConvertToStrategy;
use RuntimeException;
use InvalidArgumentException;

abstract class AbstractConvertor
{
    protected $convertToStrategy;
    protected $convertFile;
    protected $targetLocation;

    abstract public function getName();
    abstract protected function doConvert();

    public function setTargetLocation($location)
    {
        if (!is_string($location)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid location type: %s', gettype($location)));
        }

        if (!file_exists($location) && !mkdir($location, 0777, true)) {
            throw new RuntimeException(sprintf(
                'Can not create a directory: %s', $location));
        }

        $this->targetLocation = static::canonicalPath($location);
        return $this;
    }

    public function getTargetLocation()
    {
        if (!$this->targetLocation) {
            throw new RuntimeException('targetLocation has not been setted');
        }

        return $this->targetLocation;
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
            $this->setConvertToStrategy(new LongNameConvertToStrategy);
        }

        return $this->convertToStrategy;
    }

    public function convert(ConvertFile $convertFile)
    {
        $this->convertFile = $convertFile;
        $this->doConvert();

        $this->getConvertToStrategy()->reset();
    }

    public function getConvertFile()
    {
        return $this->convertFile;
    }

    protected function convertError()
    {
        $this->getConvertToStrategy()->restoreConvert();

        $errorMessage = 'Unable to convert file: ' . $this->convertFile->getFilename()
                      . ' with input charset: ' . $this->convertFile->getInputCharset()
                      . ' and output charset: ' . $this->convertFile->getOutputCharset();

        $this->convertFile = null;

        throw new RuntimeException($errorMessage);
    }

    public static function canonicalPath($path)
    {
        if (!$path = realpath($path)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid path: %s', $path));
        }

        $path = rtrim(str_replace('\\', '/', $path), '/');
        return $path;
    }
}
