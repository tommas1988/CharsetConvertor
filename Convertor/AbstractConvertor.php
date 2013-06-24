<?php
namespace Tcc\Convertor;

use Tcc\ConvertFile\ConvertFileInterface;
use Tcc\Convertor\ConvertToStrategyInterface;

class AbstractConvertor
{
    protected $convertToStrategy;
    protected $convertingFile;
    protected $targetLocation;
    protected $convertToFile;
    protected $convertFinish = false;

    abstract public function convert(ConvertFileInterface $convertFile);
    abstract public function getName();

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
            $this->setConvertToStrategy(new LongNameConvertToStrategy($this));
        }

        return $this->convertToStrategy;
    }

    public function getConvertToFile()
    {
        if ($this->convertToFile && !$this->convertFinish) {
            return $this->convertToFile;
        }

        $this->convertToFile = $this->getConvertToStrategy()->getConvertToFile();
        return $this->convertToFile;
    }

    public function cleanConvertor()
    {
        $this->convertFinish  = false;
        $this->convertingFile = null;
        $this->convertToFile  = null;
    }
}
