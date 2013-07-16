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
    protected $iteratorClass;
    protected $container;
    protected $added        = false;
    
    public function __construct(array $convertFilesOptions)
    {
        $this->convertFilesOptions = $convertFilesOptions;
    }
    
    public function addConvertFiles(ConvertFileContainer  $container)
    {
        if ($this->added) {
            return ;
        }

        $this->container = $container;
        $options         = $this->convertFilesOptions;
        $inputCharset    = null;
        $outputCharset   = null;

        if (isset($options['input_charset'])) {
            $inputCharset = $options['input_charset'];
        }
        if (isset($options['output_charset'])) {
            $outputCharset = $options['output_charset'];
        }

        if (isset($options['files'])) {
            foreach ($options['files'] as $convertFileOptions) {
                $this->resolveFileOptions($convertFileOptions,
                    $inputCharset,
                    $outputCharset);
            }
        }

        if (isset($options['dirs'])) {
            foreach ($options['dirs'] as $convertDirOptions) {
                $this->resolveDirOptions($convertDirOptions,
                    $inputCharset,
                    $outputCharset);
            }
        }

        $this->addConvertFilesToContainer();
        $this->added = true;
    }

    public function setDirectoryIteratorClass($class)
    {
        if (!is_string($class) || !self::isSubclassOf($class, 'Traversable')) {
            throw new InvalidArgumentException(sprintf(
                'Invalid iterator class: %s',
                var_export($class, true)));
        }

        $this->iteratorClass = $class;
        return $this;
    }

    public function getDirectoryIteratorClass()
    {
        if (!$this->iteratorClass) {
            $this->setDirectoryIteratorClass(
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

    protected function getConvertDirectoryIterator($dir, $filters)
    {
        $class    = $this->getDirectoryIteratorClass();
        $iterator = new $class($dir);
        $iterator->setFilter($filters);

        return new RecursiveIteratorIterator($iterator);
    }

    protected function resolveFileOptions(array $convertFileOptions,
        $inputCharset, $outputCharset
    ) {
        if (!isset($convertFileOptions['name'])) {
            throw new InvalidArgumentException(
                'convert file options must contain a name field');
        }

        $convertFile = ConvertFileContainer::canonicalPath($convertFileOptions['name']);
        
        if (in_array($convertFile ,$this->filenames)) {
            return ;
        }

        if (isset($convertFileOptions['input_charset'])) {
            $inputCharset = $convertFileOptions['input_charset'];
        }
        if (isset($convertFileOptions['output_charset'])) {
            $outputCharset = $convertFileOptions['output_charset'];
        }

        //mark this file is added to container
        $this->filenames[]  = $convertFile;
        $this->convertFiles[] = array(
            'name'           => $convertFile,
            'input_charset'  => $inputCharset,
            'output_charset' => $outputCharset,
        );
    }

    protected function resolveDirOptions(array $convertDirOptions,
        $inputCharset = null, $outputCharset = null, $parentDir = null
    ) {
        if (!isset($convertDirOptions['name'])) {
            throw new InvalidArgumentException(
                'convert directory options must contain a name field');
        }
        $convertDir = $convertDirOptions['name'];

        //concat directory name with parent directory name
        $concatWithParentDir = function($parentDir, $name) {
            $pathname = $parentDir . '/' . rtrim(ltrim(basename($name), '/'), '/');
            return ConvertFileContainer::canonicalPath($pathname);
        };

        if (isset($convertDirOptions['input_charset'])) {
            $inputCharset  = $convertDirOptions['input_charset'];
        }
        if (isset($convertDirOptions['output_charset'])) {
            $outputCharset = $convertDirOptions['output_charset'];
        }

        if (is_null($parentDir)) {
            $dirname = ConvertFileContainer::canonicalPath($convertDir);
        } else {
            $dirname = $concatWithParentDir($parentDir, $convertDir);
        }
        $convertDir = $dirname;

        if (isset($convertDirOptions['subdirs'])) {
            foreach ($convertDirOptions['subdirs'] as $subConvertDirOption) {
                $this->resolveDirOptions($subConvertDirOption,
                    $inputCharset,
                    $outputCharset,
                    $convertDir);
            }
        }

        if (isset($convertDirOptions['files'])) {
            foreach ($convertDirOptions['files'] as $convertFileOptions) {
                $convertFileOptions['name'] = $concatWithParentDir(
                    $dirname,
                    $convertFileOptions['name']);

                $this->resolveFileOptions($convertFileOptions,
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
