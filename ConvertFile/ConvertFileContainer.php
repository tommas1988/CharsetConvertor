<?php
/**
 * CharsetConvertor
 * 
 * @author Tommas Yuan
 * @link   http://github.com/tommas1988/CharsetConvertor the source code repository
 */

namespace Tcc\ConvertFile;

use InvalidArgumentException;

/**
 * Convert file container
 */
class ConvertFileContainer
{
    /**
     * Convert files
     * @var ConvertFile[]
     */
    protected $convertFiles = array();

    /**
     * Being converted file`s extensions
     * @var string[]
     */
    protected $convertExtensions = null;

    /**
     * Add a convert file to container.
     *
     * @param  string|SplFileInfo|ConvertFile $convertFile
     * @param  null|string $inputCharset
     * @param  null|string $outputCharset
     * @return bool
     */
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
    
    /**
     * Add convert files to container.
     *
     * @param  ConvertFileAggregate $aggregate
     * @return self
     */
    public function addFiles(ConvertFileAggregate $aggregate)
    {
        $aggregate->addConvertFiles($this);
        return $this;
    }
    
    /**
     * Get all convert files.
     *
     * @return ConvertFile[]
     */
    public function getFiles()
    {
        return $this->convertFiles;
    }

    /**
     * Count convert files
     *
     * @return int
     */
    public function count()
    {
        return count($this->convertFiles);
    }

    /**
     * Clean up the container
     *
     * @return self
     */
    public function clearFiles()
    {
        $this->convertFiles = array();
        return $this;
    }

    /**
     * Add to be convert file's extensions.
     *
     * @param  string $extension
     * @return self
     * @throws InvalidArgumentException If extension is not string
     */
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

    /**
     * Reset convert extensions with new ones.
     *
     * @param  null|string[]
     * @return self
     */
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

    /**
     * Get all convert extensions
     *
     * @return null|string[]
     */
    public function getConvertExtensions()
    {
        return $this->convertExtensions;
    }

    /**
     * Canonical pathname.
     *
     * @param  string $path
     * @return string canonical path
     * @throws InvalidArgumentException If path is not valid or exists
     */
    public static function canonicalPath($path)
    {
        if (!$path = realpath($path)) {
            throw new InvalidArgumentException(sprintf('Invalid path: %s', $path));
        }

        $path = rtrim(str_replace('\\', '/', $path), '/');
        return $path;
    }

    /**
     * Canonical extension name.
     *
     * @param  string $extension
     * @return string canonical extension name
     * @throws InvalidArgumentException If extension is not string
     */
    public static function canonicalExtension($extension)
    {
        if (!is_string($extension)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid extension type: %s', gettype($extension)));
        }

        if (false !== strpos($extension, '.')) {
            $extension = substr($extension, strrpos($extension, '.') + 1);
        }

        return strtolower($extension);
    }
}
