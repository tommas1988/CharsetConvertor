<?php
namespace Tcc\Test\ScriptFrontend\Printer\TestAssert;

use Tcc\ConvertFile\ConvertFile;

class FooConvertFile extends ConvertFile
{
	protected $convertError;
	protected $name;
	protected $path;

	public function __construct($name = null,
		$inputCharset = null, $outputCharset = null
	) {
		$this->name          = $name;
		$this->inputCharset  = $inputCharset;
		$this->outputCharset = $outputCharset;
	}

	public function getFilename($withoutExtension = false)
	{
		return $this->name;
	}

	public function setPath($path)
	{
		$this->path = $path;
	}

	public function getPath()
	{
		return $this->path;
	}
}
