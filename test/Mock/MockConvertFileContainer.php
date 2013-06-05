<?php
class MockConvertFileContainer implements Tcc\ConvertFileContainerInterface
{
	protected $convertFiles = array();

	public function addFile($convertFile, $inputCharset, $outputCharset)
	{
		$this->convertFiles[] = array(
				'name'          => $convertFile,
				'inputCharset'  => $inputCharset,
				'outputCharset' => $outputCharset,
			);
	}

	public function addFiles(ConvertFileAggregateInterface $aggregate)
	{

	}

	public function getConvertFiles()
	{
		return $this->convertFiles;
	}

	public function setConvertExtensions(array $extensions)
	{

	}

	public function getConvertExtensions()
	{

	}
}
