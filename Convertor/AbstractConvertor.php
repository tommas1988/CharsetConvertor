<?php
namespace Tcc\Convertor;

use Tcc\ConvertFile\ConvertFileInterface;
use Tcc\Convertor\ConvertToStrategyInterface;

class AbstractConvertor
{
    protected $convertToStrategy;
    protected $convertingFile;
    protected $targetLocation;

    abstract public function convert(ConvertFileInterface $convertFile);
    abstract public function getName();

    public function setTargetLocation($location)
    {
        if (!is_dir($location)) {
            throw new Exception('Invalid argument');
        }

        $this->targetLocation = static::canonicalLoaction($location);
    }

    public function getTargetLocation()
    {
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
        return $this->getConvertToStrategy()->getConvertToFile();
    }

    public static function canonicalLoaction($location)
    {

    }
}
