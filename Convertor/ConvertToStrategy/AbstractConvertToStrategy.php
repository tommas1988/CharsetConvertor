<?php
/**
 * CharsetConvertor
 * 
 * @author Tommas Yuan
 * @link   http://github.com/tommas1988/CharsetConvertor the source code repository
 */

namespace Tcc\Convertor\ConvertToStrategy;

use Tcc\Convertor\AbstractConvertor;
use SplFileObject;
use RuntimeException;
use Exception;

/**
 * Abstract ConvertToStrategy class
 */
abstract class AbstractConvertToStrategy
{
    /**
     * @var Tcc\Covertor\AbstractConvertor
     */
    protected $convertor;

    /**
     * Target convert file
     *
     * @var \SplFileObject
     */
    protected $targetFile;

    /**
     * Get target file name
     *
     * @return string
     */
    abstract public function getTargetFileName();

    /**
     * Set convertor
     *
     * @param  Tcc\Convertor\AbstractConvertor $convertor
     * @return self
     */
    public function setConvertor(AbstractConvertor $convertor)
    {
        $this->convertor = $convertor;
        return $this;
    }

    /**
     * Put converted contents to the target file
     *
     * @param  string $contents
     * @throws RuntimeException If failed to write to the target file
     */
    public function convertTo($contents)
    {
        if ($this->targetFile === null
            || !$this->targetFile instanceof SplFileObject
        ) {
            $filename = $this->getTargetFileName();
            $this->targetFile = new SplFileObject($filename, 'a');
        }

        if ($this->targetFile->fwrite($contents) !== strlen($contents)) {
            throw new RuntimeException('write contents to target file failed');
        }
    }

    /**
     * Restore convert process
     *
     * @throws RuntimeException If can not delete the generated target file
     */
    public function restoreConvert()
    {
        if ($this->targetFile !== null) {
            $filename = $this->targetFile->getRealPath();
            //reset target file to null to be able delete it; 
            $this->targetFile = null;

            if (!unlink($filename)) {
                throw new RuntimeException(sprintf(
                    'Can not delete target file: %s', $filename));
            }
        }
    }

    /**
     * Reset state when finish converting a file
     *
     * @return self
     */
    public function reset()
    {
        $this->targetFile = null;
        return $this;
    }
}
