<?php
namespace Tcc\ConvertFile;

use InvalidArgumentException;
use RuntimeException;
use RecursiveIteratorIterator;

class ConvertFileAggregate implements ConvertFileAggregateInterface
{
    protected $loadedConvertFiles = array();
    protected $convertFiles       = array();
    protected $convertDirs        = array();
    protected $filenames          = array();
    protected $container;

    protected $loadFinished       = false;
    
    public function __construct(array $convertFiles)
    {
        $this->convertFiles = $convertFiles;
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
    
    public function addConvertFiles(ConvertFileContainerInterface  $container)
    {
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
    }

    public function loadConvertFiles()
    {
        if ($this->loadFinished) {
            return ;
        }

        if (!$this->container) {
            throw new RuntimeException(
                'You have not add convert files yet');
        }

        $filters = array(
            'files' => $this->filenames,
            'dirs'  => array(),
        );

        foreach ($this->convertDirs as $dir) {
            foreach ($this->getConvertFileFromDirectory() as $convertFile) {
                if ($convertFile) {
                    $this->container->addFile($convertFile);
                }
            }
            $filters['dirs'][] = $dir['name'];
        }

        foreach ($this->convertFiles as $convertFile) {
            $this->container->addFile($convertFile['name'],
                $convertFile['input_charset'],
                $convertFile['output_charset']);
        }

        $this->loadFinished = true;
    }

    public function setDirectoryIteratorClass($class)
    {
        //use Reflection to test wether the class is traversable

        if (!is_string($class) || !class_exists($class)) {
            throw new InvalidArgumentException('Invalid itertor class');
        }

        $this->itertorClass = $class;
    }

    public function getDirectoryIteratorClass()
    {
        if (!$this->iteratorClass) {
            $this->setConvertDirectoryIteratorClass(
                'Tcc\\ConvertFile\\Iterator\\ConvertDirectoryIterator');
        }

        return $this->iteratorClass;
    }

    protected function getConvertFileFromDirectory($dir, $filters)
    {
        $iterator = $this->getDirectoryIteratorClass();

        return new RecursiveIteratorIterator(
            new $iterator($dir, $filters));
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
                'convert dir option must contain a name field');
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
}
