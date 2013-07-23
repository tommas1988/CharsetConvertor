<?php
/**
 * CharsetConvertor
 * 
 * @author Tommas Yuan
 * @link   http://github.com/tommas1988/CharsetConvertor the source code repository
 */

namespace Tcc\Convertor;

use Tcc\ConvertFile\ConvertFile;
use Tcc\Convertor\ConvertToStrategy\AbstractConvertToStrategy;
use Tcc\Convertor\ConvertToStrategy\LongNameConvertToStrategy;
use RuntimeException;
use InvalidArgumentException;

/**
 * Abstract convertor class
 */
abstract class AbstractConvertor
{
    /**
     * convert to strategy.
     *
     * @var ConvertToStrategy\AbstractConvertToStrategy
     */
    protected $convertToStrategy;

    /**
     * @var Tcc\ConvertFile\ConvertFile
     */
    protected $convertFile;

    /**
     * The convert to target location
     *
     * @var string
     */
    protected $targetLocation;

    /**
     * Get convertor name.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Real convert method
     */
    abstract protected function doConvert();

    /**
     * @param  string $location
     * @return self
     * @throws InvalidArgumentException If $location is not string
     * @throws RuntimeException If $location is not exists and can not create
     */
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

    /**
     * @return string
     * @throws RuntimeException If target location has not been setted yet
     */
    public function getTargetLocation()
    {
        if (!$this->targetLocation) {
            throw new RuntimeException('targetLocation has not been setted');
        }

        return $this->targetLocation;
    }
   
    /**
     * @param  AbstractConvertToStrategy $strategy
     * @return self
     */
    public function setConvertToStrategy(AbstractConvertToStrategy $strategy)
    {
        $strategy->setConvertor($this);
        $this->convertToStrategy = $strategy;
        
        return $this;
    }

    /**
     * Get a Convert to strategy.
     *
     * Return ConvertToStrategy\LongNameConvertToStrategy if not set before
     *
     * @return AbstractConvertToStrategy
     */
    public function getConvertToStrategy()
    {
        if (!$this->convertToStrategy) {
            $this->setConvertToStrategy(new LongNameConvertToStrategy);
        }

        return $this->convertToStrategy;
    }

    /**
     * Convert method
     *
     * @param  Tcc\ConvertFile\ConvertFile $convertFile
     */
    public function convert(ConvertFile $convertFile)
    {
        $this->convertFile = $convertFile;
        $this->doConvert();

        $this->getConvertToStrategy()->reset();
    }

    /**
     * @return Tcc\ConvertFile\ConvertFile
     */
    public function getConvertFile()
    {
        return $this->convertFile;
    }

    /**
     * Convert Error
     *
     * @throws RuntimeException
     */
    protected function convertError()
    {
        $this->getConvertToStrategy()->restoreConvert();

        $errorMessage = 'Unable to convert file: ' . $this->convertFile->getFilename()
                      . ' with input charset: ' . $this->convertFile->getInputCharset()
                      . ' and output charset: ' . $this->convertFile->getOutputCharset();

        $this->convertFile = null;

        throw new RuntimeException($errorMessage);
    }

    /**
     * Canonical pathname
     *
     * @param  string $path
     * @return string
     * @throws InvalidArgumentException If $path is not exists
     */
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
