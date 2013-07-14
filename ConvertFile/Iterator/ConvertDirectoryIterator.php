<?php
namespace Tcc\ConvertFile\Iterator;

use Tcc\ConvertFile\ConvertFileContainer;
use Tcc\ConvertFile\ConvertFile;
use RecursiveFilterIterator;
use RecursiveDirectoryIterator;
use Exception;

class ConvertDirectoryIterator extends RecursiveFilterIterator
{
    protected $dirname;
    protected $inputCharset;
    protected $outputCharset;
    protected $filters;
    protected $convertFileClass;

    public function __construct(array $convertDirOption)
    {
        if (!isset($convertDirOption['input_charset'])
            || !isset($convertDirOption['output_charset'])
        ) {
            throw new Exception(var_export($convertDirOption, 1));
        }

        if (isset($convertDirOption['iterator'])
            && $convertDirOption['iterator'] instanceof RecursiveDirectoryIterator
        ) {
            $iterator = $convertDirOption['iterator'];
        } elseif (isset($convertDirOption['name']) 
            && is_string($convertDirOption['name'])
        ) {
            $iterator      = new RecursiveDirectoryIterator($convertDirOption['name']);
            $this->dirname = $convertDirOption['name'];
        } else {
            throw new Exception('Invalid argument');
        }

        $this->inputCharset  = $convertDirOption['input_charset'];
        $this->outputCharset = $convertDirOption['output_charset'];

        parent::__construct($iterator);
    }

    public function accept()
    {
        $fileInfo = parent::current();

        if ($fileInfo->isFile()) {
            $filename = ConvertFileContainer::canonicalPath($fileInfo->getPathname());

            if (in_array($filename, $this->filters['files'])) {
                return false;
            }
            return true;
        }

        if ($fileInfo->isDir()) {
            $dirname = ConvertFileContainer::canonicalPath($fileInfo->getPathname());

            if (in_array($dirname, $this->filters['dirs'])) {
                return false;
            }
            return true ;
        }

        return false ;

    }

    public function getChildren()
    {
        $convertDirOption = array(
            'iterator'       => $this->getInnerIterator()->getChildren(),
            'input_charset'  => $this->inputCharset,
            'output_charset' => $this->outputCharset,
        );

        $iterator = new static($convertDirOption);
        $iterator->setFilter($this->filters);

        return $iterator;
    }

    public function current()
    {
        $fileInfo = parent::current();
        if ($fileInfo->isFile()) {
            $convertFileClass = $this->getConvertFileClass();
            return new $convertFileClass($fileInfo, $this->inputCharset, $this->outputCharset);
        }
        return null;
    }

    public function setFilter(array $filter)
    {
        if (!isset($filters['files']) || !isset($filters['dirs'])) {
            throw new InvalidArgumentException(sprintf(
                'Invalid filter: %s', var_export($filter, true)));
        }

        $this->filter = $filter;
    }

    public function setConvertFileClass($class)
    {
        if (!is_string($class)) {
            throw new Exception('Invalid argument');
        }

        if (!is_subclass_of($class, 'Tcc\\ConvertFile\\ConvertFileInterface')) {
            throw new Exception('The provided class dose not implement '
                              . 'Tcc\\ConvertFileInterface or the PHP '
                              . 'version is lower than 5.3.7');
        }
        $this->convertFileClass = $class;
    }

    public function getConvertFileClass()
    {
        if (!$this->convertFileClass) {
            $this->setConvertFileClass('Tcc\\ConvertFile\\ConvertFile');
        }
        return $this->convertFileClass;
    }
}
