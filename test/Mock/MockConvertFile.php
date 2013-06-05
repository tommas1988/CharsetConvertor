<?php
class MockConvertFile implements \Tcc\ConvertFileInterface
{
	protected $filename;
	protected $inputCharset;
	protected $outputCharset;
	protected $extension;

	public function setFilename($filename)
	{
		$this->filename = $filename;
	}

	public function getFilename()
	{
		return $this->filename;
	}

	public function setInputCharset($inputCharset)
	{
		$this->inputCharset = $inputCharset;
	}

	public function getInputCharset()
	{
		return $this->inputChaset;
	}

	public function setOutputCharset($outputCharset)
	{
		$this->outputCharset = $outputCharset;
	}

	public function getOutputCharset()
	{
		return $this->outputCharset;
	}

	public function setExtension($extension)
	{
		$this->extension = $extension;
	}

	public function getExtension()
	{
		return $this->extension;
	}
}
