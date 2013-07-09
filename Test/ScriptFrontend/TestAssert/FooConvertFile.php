<?php
namespace Tcc\Test\ScriptFrontend\TestAssert;

use Tcc\ConvertFile\ConvertFileInterface;

class FooConvertFile implements ConvertFileInterface
{
	protected $convertError;

	public function setConvertErrorFlag($flag)
	{
		$this->convertError = (bool) $flag;
	}

	public function getConvertErrorFlag()
	{
		return $this->convertError;
	}

	public function getIterator()
	{

	}

	public function getFilename()
	{

	}

	public function getInputCharset()
	{

	}

	public function getOutputCharset()
	{

	}

	public function getPathname()
	{

	}

	public function getPath()
	{

	}

	public function getExtension()
	{

	}
}
