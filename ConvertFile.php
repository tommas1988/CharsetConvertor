<?php
namespace Tcc;

class ConvertFile implements ConvertFileInterface, \IteratorAggregate
{
	protected $inputCharset;
	protected $outputCharset;
	protected $fileInfo;
	
	public function __construct(\SplFileInfo $file, $inputCharset, $outputCharset)
	{
		if (!$file->isFile() || !$file->isReadable()) {
			throw new \Exception();
		}

		$this->fileInfo      = $file;
		$this->inputCharset  = $inputCharset;
		$this->outputCharset = $outputCharset;
	}

	public function getIterator()
	{
		return $this->fileInfo->openFile();
	}
	
	public function getInputCharset()
	{
		return $this->inputCharset;
	}
	
	public function getOutputCharset()
	{
		return $this->outputCharset;
	}
	
	public function getFileInfo()
	{
		return $this->fileInfo;
	}

	public function getFilename($withoutExtension = false)
	{
		$fileInfo = $this->fileInfo;

		if ($withoutExtension) {
			return substr($fileInfo->getFilename(), 0, -strlen($fileInfo->getExtension()));
		}

		return $fileInfo->getFilename();
	}

	public function getPath()
	{
		return $this->fileInfo->getPath();
	}

	public function getPathname()
	{
		return $this->fileInfo->getPathname();
	}

	public function getExtension()
	{
		return $this->fileInfo->getExtension();
	}
}
