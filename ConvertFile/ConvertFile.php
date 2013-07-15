<?php
namespace Tcc\ConvertFile;

use SplFileInfo;
use InvalidArgumentException;

class ConvertFile
{
    protected $inputCharset;
    protected $outputCharset;
    protected $fileInfo;
    
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

    public function getIterator()
    {
        return $this->fileInfo->openFile();
    }
    
    public function getInputCharset()
    {
        return $this->inputCharset;
    }
    
    public function getOutputCharset()
    {
        return $this->outputCharset;
    }

    public function getFilename($withoutExtension = false)
    {
        $fileInfo = $this->fileInfo;

        if ($withoutExtension) {
            return substr($fileInfo->getFilename(), 0,
                -(strlen($fileInfo->getExtension()) + 1));
        }

        return $fileInfo->getFilename();
    }

    public function getPath()
    {
        return ConvertFileContainer::canonicalPath($this->fileInfo->getPath());
    }

    public function getPathname()
    {
        return ConvertFileContainer::canonicalPath($this->fileInfo->getPathname());
    }

    public function getExtension()
    {
        return ConvertFileContainer::canonicalExtension($this->fileInfo->getExtension());
    }
}
