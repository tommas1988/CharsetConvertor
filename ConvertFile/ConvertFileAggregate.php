<?php
namespace Tcc\ConvertFile;

use InvalidArgumentException;
use RuntimeException;
use RecursiveIteratorIterator;
use Traversable;

class ConvertFileAggregate
{
    protected $convertFiles = array();
    protected $convertDirs  = array();
    protected $filenames    = array();
    protected $filters      = array();
    protected $container;

    protected $added        = false;
    
    public function __construct(array $convertFiles)
    {
        $this->convertFiles = $convertFiles;
    }
    
    public function addConvertFiles(ConvertFileContainer  $container)
    {
        if ($this->added) {
            return ;
        }

        $this->container = $container;
        $convertFiles    = $this->convertFiles;
        $inputCharset    = null;
        $outputCharset   = null;

        if (isset($convertFiles['input_charset'])) {
            $inputCharset = $convertFiles['input_charset'];
        }
        if (isset($convertFiles['output_charset'])) {
            $outputCharset = $convertFiles['output_charset'];
        }

        if (isset($convertFiles['files'])) {
            foreach ($convertFiles['files'] as $convertFileOption) {
                $this->resolveFileOptions($convertFileOption,
                    $inputCharset,
                    $outputCharset);
            }
        }

        if (isset($convertFiles['dirs'])) {
            foreach ($convertFiles['dirs'] as $convertDirOption) {
                $this->resolveDirOptions($convertDirOption,
                    $inputCharset,
                    $outputCharset);
            }
        }

        $this->addConvertFilesToContainer();
        $this->added = true;
    }

    public function setDirectoryIteratorClass($class)
    {
        if (!is_string($class) || !self::isSubclassOf($class, Traversable)) {
            throw new InvalidArgumentException('Invalid itertor class');
        }

        $this->itertorClass = $class;
        return $this;
    }

    public function getDirectoryIteratorClass()
    {
        if (!$this->iteratorClass) {
            $this->setConvertDirectoryIteratorClass(
                'Tcc\\ConvertFile\\Iterator\\ConvertDirectoryIterator');
        }

        return $this->iteratorClass;
    }

    /**
     * Handy for debug
     */
    public function getConvertFiles()
    {
        return $this->convertFiles;
    }

    /**
     * Handy for debug
     */
    public function getConvertDirs()
    {
        return $this->convertDirs;
    }

    /**
     * Handy for debug
     */
    public function getFilters()
    {
        return $this->filters;
    }

    protected function addConvertFilesToContainer()
    {
        if (!$this->container) {
            throw new RuntimeException(
                'You have not setted a container');
        }

        $filters = array(
            'files' => $this->filenames,
            'dirs'  => array(),
        );

        foreach ($this->convertDirs as $dir) {
            foreach ($this->getConvertDirectoryIterator($dir, $filters) as $convertFile) {
                if ($convertFile) {
                    $this->container->addFile($convertFile);
                }
            }
            $filters['dirs'][] = $dir['name'];
        }
        $this->filters = $filters;

        foreach ($this->convertFiles as $convertFile) {
            $this->container->addFile($convertFile['name'],
                $convertFile['input_charset'],
                $convertFile['output_charset']);
        }
    }

    protected function getConvertDirectoryIterator($dir, $filter)
    {
        $class    = $this->getDirectoryIteratorClass();
        $iterator = new $class($dir);
        $iterator->setFilter($filters);

        return new RecursiveIteratorIterator($iterator);
    }

    protected function resolveFileOptions(array $convertFileOption,
        $inputCharset, $outputCharset
    ) {
        if (!isset($convertFileOption['name'])) {
            throw new InvalidArgumentException(
                'convert file options must contain a name field');
        }

        $convertFile = ConvertFileContainer::canonicalPath($convertFileOption['name']);
        
        if (in_array($convertFile ,$this->filenames)) {
            return ;
        }

        if (isset($convertFileOption['input_charset'])) {
            $inputCharset = $convertFileOption['input_charset'];
        }
        if (isset($convertFileOption['output_charset'])) {
            $outputCharset = $convertFileOption['output_charset'];
        }

        //mark this file is added to container
        $this->filenames[]  = $convertFile;
        $this->convertFiles = array(
            'name'           => $convertFile,
            'input_charset'  => $inputCharset,
            'output_charset' => $outputCharset,
        );
    }

    protected function resolveDirOptions(array $convertDirOption,
        $inputCharset = null, $outputCharset = null, $parentDir = null
    ) {
        if (!isset($convertDirOption['name'])) {
            throw new InvalidArgumentException(
                'convert directory options must contain a name field');
        }
        $convertDir = $convertDirOption['name'];

        //concat directory name and parent directory name
        $concatWithParentDir = function($parentDir, $name) {
            $pathname = $parentDir . '/' . rtrim(ltrim(basename($name), '/'), '/');
            return ConvertFileContainer::canonicalPath($pathname);
        };

        if (isset($convertDirOption['input_charset'])) {
            $inputCharset  = $convertDirOption['input_charset'];
        }
        if (isset($convertDirOption['output_charset'])) {
            $outputCharset = $convertDirOption['output_charset'];
        }

        if (is_null($parentDir)) {
            $dirname = ConvertFileContainer::canonicalPath($convertDir);
        } else {
            $dirname = $concatWithParentDir($parentDir, $convertDir);
        }
        $convertDir = $dirname;

        if (isset($convertDirOption['subdirs'])) {
            foreach ($convertDirOption['subdirs'] as $subConvertDirOption) {
                $this->resolveDirOptions($subConvertDirOption,
                    $inputCharset,
                    $outputCharset,
                    $convertDir);
            }
        }

        if (isset($convertDirOption['files'])) {
            foreach ($convertDirOption['files'] as $convertFileOption) {
                $convertFileOption['name'] = $concatWithParentDir($dirname,
                    $convertFileOption['name']);

                $this->resolveFileOptions($convertFileOption,
                    $inputCharset,
                    $outputCharset);
            }
        }

        $this->convertDirs[] = array(
            'name'           => $convertDir,
            'input_charset'  => $inputCharset,
            'output_charset' => $outputCharset,
        );
    }

    protected static function isSubclassOf($className, $type)
    {
        if (is_subclass_of($className, $type)) {
            return true;
        }
        return false;
    }
}
