<?php
/**
 * CharsetConvertor
 * 
 * @author Tommas Yuan
 * @link   http://github.com/tommas1988/CharsetConvertor the source code repository
 */

namespace Tcc\ConvertFile;

use SplFileInfo;
use IteratorAggregate;
use InvalidArgumentException;

/**
 * ConvertFile class
 */
class ConvertFile implements IteratorAggregate
{
    /**
     * Input charset encoding
     * @var string
     */
    protected $inputCharset;

    /**
     * Output charset encoding
     * @var string
     */
    protected $outputCharset;

    /**
     * @var SplFileInfo
     */
    protected $fileInfo;
    
    /**
     * Constructor
     *
     * @param  string|SplFileInfo $file
     * @param  string $inputCharset
     * @param  string $outputCharset
     * @throws InvalidArgumentException If convert file is not string or SplFileInfo
     * @throws InvalidArgumentException If convert file is not file or readable
     */
    public function __construct($file, $inputCharset, $outputCharset)
    {
        if (is_string($file)) {
            $file = new SplFileInfo($file);
        } elseif (!$file instanceof SplFileInfo) {
            throw new InvalidArgumentException('Invalid convert file');
        }

        if (!$file->isFile() || !$file->isReadable()) {
            throw new InvalidArgumentException(
                'Convert file is not file or readable');
        }

        $this->fileInfo      = $file;
        $this->inputCharset  = $inputCharset;
        $this->outputCharset = $outputCharset;
    }

    /**
     * Implements from AggregateIterator
     *
     * @return SplFileObject
     */
    public function getIterator()
    {
        return $this->fileInfo->openFile();
    }
    
    /**
     * @return string
     */
    public function getInputCharset()
    {
        return $this->inputCharset;
    }
    
    /**
     * @return string
     */
    public function getOutputCharset()
    {
        return $this->outputCharset;
    }

    /**
     * @param  bool $withoutExtension Return filename with suffix or not
     * @return string
     */
    public function getFilename($withoutExtension = false)
    {
        $fileInfo = $this->fileInfo;

        if ($withoutExtension) {
            return substr($fileInfo->getFilename(), 0,
                -(strlen($fileInfo->getExtension()) + 1));
        }

        return $fileInfo->getFilename();
    }

    /**
     * @return string The file path
     */
    public function getPath()
    {
        return ConvertFileContainer::canonicalPath($this->fileInfo->getPath());
    }

    /**
     * @return string The file path and file name
     */
    public function getPathname()
    {
        return ConvertFileContainer::canonicalPath($this->fileInfo->getPathname());
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return ConvertFileContainer::canonicalExtension($this->fileInfo->getExtension());
    }
}
