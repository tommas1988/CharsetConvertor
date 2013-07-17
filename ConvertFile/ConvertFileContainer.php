<?php
namespace Tcc\ConvertFile;

use InvalidArgumentException;
use SplFileInfo;

class ConvertFileContainer
{
    protected $convertFiles = array();
    protected $convertExtensions;

    public function addFile($convertFile,
        $inputCharset = null, $outputCharset = null
    ) {
        $isConvertFile = ($convertFile instanceof ConvertFile) ? true : false;

        if (!$isConvertFile) {
            $convertFile = new ConvertFile($convertFile,
                $inputCharset, $outputCharset);
        }
        
        $extension  = $convertFile->getExtension();
        $extensions = $this->getConvertExtensions();
        if ($extensions !== null && !in_array($extension, $extensions)) {
            unset($convertFile);
            return false;
        }

        $this->convertFiles[] = $convertFile;
        return true;
    }
    
    public function addFiles(ConvertFileAggregate $aggregate)
    {
        $aggregate->addConvertFiles($this);
        return $this;
    }
    
    public function getFiles()
    {
        return $this->convertFiles;
    }

    public function count()
    {
        return count($this->convertFiles);
    }

    public function clearFiles()
    {
        $this->convertFiles = array();

        return $this;
    }

    public function addConvertExtension($extension)
    {
        if (!is_string($extension)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid file extension, type: %s, value: %s',
                gettype($extension),
                var_export($extension, true)));
        }

        $extension = static::canonicalExtension($extension);

        if ($this->convertExtensions === null) {
            $this->convertExtensions = array($extension);
        } elseif (!in_array($extension, $this->convertExtensions)) {
            $this->convertExtensions[] = $extension;
        }

        return $this;
    }

    public function setConvertExtensions(array $extensions = null)
    {
        $this->convertExtensions = null;

        if ($extensions === null) {
            return $this;
        }

        foreach ($extensions as $extension) {
            $this->addConvertExtension($extension);
        }

        return $this;
    }

    public function getConvertExtensions()
    {
        return $this->convertExtensions;
    }

    public static function canonicalPath($path)
    {
        if (!$path = realpath($path)) {
            throw new Exception();
        }

        $path = rtrim(str_replace('\\', '/', $path), '/');
        return $path;
    }

    public static function canonicalExtension($extension)
    {
        if (false !== strpos($extension, '.')) {
            $extension = substr($extension, strrpos($extension, '.') + 1);
        }

        return strtolower($extension);
    }
}
