<?php
namespace Tcc\Iterator;

use Tcc\ConvertFileContainer;
use Tcc\ConvertFile;

class ConvertDirectoryIterator extends \RecursiveFilterIterator
{
	protected $dirname;
	protected $inputCharset;
	protected $outputCharset;
	protected $fiters;

	public function __construct(array $convertDirOption, array $filters)
	{
		if (!isset($convertDirOption['name'])
			|| !isset($convertDirOption['input_charset'])
			|| !isset($convertDirOption['output_charset'])
			|| !isset($filters['files'])
			|| !isset($filters['dirs'])
			|| !isset($filters['extensions'])
		) {
			throw new \Exception();
		}

		$this->dirname       = $convertDirOption['name'];
		$this->inputCharset  = $convertDirOption['input_charset'];
		$this->outputCharset = $convertDirOption['output_charset'];
		$this->fiters        = $filters;

		$iterator = new \RecursiveDirectoryIterator($this->dirname);

		parent::__construct($iterator);
	}

	public function accept()
	{
		$fileInfo = parent::current();

		if ($fileInfo->isFile()) {
			$filename = ConvertFileContainer::canonicalPath($fileInfo->getPathname());
			if (in_array($filename, $this->filters['files'])) {
				return false;
			} else {
				$extension = ConvertFileContainer::canonicalExtension($fileInfo->getExtension());
				return in_array($extension, $this->filters['extensions']);
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

	public function current()
	{
		$fileInfo = parent::current();
		return new ConvertFile($fileInfo, $this->inputCharset, $this->$outputCharset);
	}
}
