<?php
class ConvertFile implements IteratorAggregate
{
	protected $inputCharset;
	protected $outputCharset;
	protected $fileInfo;
	
	public function __construct($file, $inputCharset, $outputCharset)
	{
		if (!$file instanceof SplFileInfo) {
			throw new Exception();
		}

		if (!$file->isFile() || !$file->isReadable()) {
			throw new Exception();
		}

		$this->fileInfo      = $file;
		$this->inputCharset  = $inputCharset;
		$this->outputCharset = $outputCharset;
	}

	public function getIterator()
	{
		return $this->fileInfo;
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

	public function getFilename()
	{

	}
}
