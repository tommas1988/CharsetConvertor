<?php
namespace Tcc\Iterator;

use Tcc\ConvertFileContainer;
use Tcc\ConvertFile;

class ConvertDirectoryIterator extends \RecursiveFilterIterator
{
	protected $dirname;
	protected $inputCharset;
	protected $outputCharset;
	protected $filters;
	protected $convertFileClass;

	public function __construct(array $convertDirOption, array $filters)
	{
		if (!isset($convertDirOption['input_charset'])
			|| !isset($convertDirOption['output_charset'])
			|| !isset($filters['files'])
			|| !isset($filters['dirs'])
		) {
			throw new \Exception(var_export($convertDirOption, 1) . var_export($filters, 1));
		}

		if (isset($convertDirOption['iterator'])
			&& $convertDirOption['iterator'] instanceof \RecursiveDirectoryIterator
		) {
			$iterator = $convertDirOption['iterator'];
		} elseif (isset($convertDirOption['name']) && is_string($convertDirOption['name'])) {
			$iterator = new \RecursiveDirectoryIterator($convertDirOption['name']);
			$this->dirname       = $convertDirOption['name'];
		} else {
			throw new Exception('Invalid argument');
		}

		$this->inputCharset  = $convertDirOption['input_charset'];
		$this->outputCharset = $convertDirOption['output_charset'];
		$this->filters       = $filters;

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

		return new self($convertDirOption, $this->filters);
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

	public function setConvertFileClass($class)
	{
		if (!is_string($class)) {
			throw new \Exception('Invalid argument');
		}

		if (!is_subclass_of($class, 'Tcc\\ConvertFileInterface')) {
			throw new Exception('The provided class dose not implement Tcc\\ConvertFileInterface or the PHP version is lower than 5.3.7');
		}
		$this->convertFileClass = $class;
	}

	public function getConvertFileClass()
	{
		if (!$this->convertFileClass) {
			$this->setConvertFileClass('Tcc\\ConvertFile');
		}
		return $this->convertFileClass;
	}
}
