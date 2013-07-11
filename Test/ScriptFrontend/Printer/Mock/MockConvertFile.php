<?php
namespace Tcc\Test\ScriptFrontend\Printer\Mock;

use Tcc\ConvertFile\ConvertFileInterface;

class MockConvertFile implements ConvertFileInterface
{
	protected $filename;
	protected $inputCharset;
	protected $outputCharset;
	protected $pathname;
	protected $path;

	public function getIterator()
	{

	}

	public function setFilename($filename)
	{
		$this->filename = $filename;
		return $this;
	}

	public function getFilename()
	{
		return $this->filename;
	}

	public function setInputCharset($inputCharset)
	{
		$this->inputCharset = $inputCharset;
		return $this;
	}

	public function getInputCharset()
	{
		return $this->inputCharset;
	}

	public function setOutputCharset($outputCharset)
	{
		$this->outputCharset = $outputCharset;
		return $this;
	}

	public function getOutputCharset()
	{
		return $this->outputCharset;
	}

	public function setPathname($pathname)
	{
		$this->pathname = $pathname;
		return $this;
	}

	public function getPathname()
	{
		return $this->pathname;
	}

	public function setPath($path)
	{
		$this->path = $path;
		return $this;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function getExtension()
	{

	}
}
