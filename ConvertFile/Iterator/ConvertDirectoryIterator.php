<?php
namespace Tcc\ConvertFile\Iterator;

use Tcc\ConvertFile\ConvertFileContainer;
use Tcc\ConvertFile\ConvertFile;
use RecursiveFilterIterator;
use RecursiveDirectoryIterator;
use InvalidArgumentException;

class ConvertDirectoryIterator extends RecursiveFilterIterator
{
    protected $inputCharset;
    protected $outputCharset;
    protected $filters;

    public function __construct(array $convertDirOptions)
    {
        if (!isset($convertDirOptions['input_charset'])
            || !isset($convertDirOptions['output_charset'])
        ) {
            throw new InvalidArgumentException(sprintf(
                'convert directoty options must contain charset info, but passed is: %s',
                var_export($convertDirOptions, true)));
        }

        if (isset($convertDirOptions['iterator'])
            && $convertDirOptions['iterator'] instanceof RecursiveDirectoryIterator
        ) {
            $iterator = $convertDirOptions['iterator'];
        } elseif (isset($convertDirOptions['name']) 
            && is_string($convertDirOptions['name'])
        ) {
            $iterator = new RecursiveDirectoryIterator($convertDirOptions['name']);
        } else {
            throw new InvalidArgumentException(sprintf(
                'convert directory options must contain iterator or name field, but passed is: %s',
                var_export($convertDirOptions, true)));
        }

        $this->inputCharset  = $convertDirOptions['input_charset'];
        $this->outputCharset = $convertDirOptions['output_charset'];

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
        $convertDirOptions = array(
            'iterator'       => $this->getInnerIterator()->getChildren(),
            'input_charset'  => $this->inputCharset,
            'output_charset' => $this->outputCharset,
        );

        $iterator = new self($convertDirOptions);
        $iterator->setFilter($this->filters);

        return $iterator;
    }

    public function current()
    {
        $fileInfo = parent::current();
        if ($fileInfo->isFile()) {
            return new ConvertFile($fileInfo,
                $this->inputCharset, $this->outputCharset);
        }
        return null;
    }

    public function setFilter(array $filters)
    {
        if (!isset($filters['files']) || !isset($filters['dirs'])) {
            throw new InvalidArgumentException(sprintf(
                'Invalid filters: %s', var_export($filters, true)));
        }

        $this->filters = $filters;
        return $this;
    }
}
