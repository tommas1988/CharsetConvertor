<?php
class ConvertFlieContainer
{
	protected $loadedConvertFiles = array();
	protected $convertFiles       = array();
	protected $convertAggregates  = array();
	protected $convertExtensions  = array();

	protected $loadFinshed        = false;
	
	public function addFile($convertFile, $inputCharset = null, $outputCharset = null)
	{
		$isConvertFile = false;

		if (is_string($convertFile)) {
			$file = new SplFileInfo($convertFile);
		} elseif ($convertFile instanceof SplFileInfo) {
			$file = $convertFile;
		} elseif ($convertFile instanceof ConvertFile) {
			$isConvertFile = true;
			$file          = $convertFile->getFileInfo();
		}

		$extension = static::canonicalExtension($file->getExtension());
		if (!in_array($extension, $this->getConvertExtensions())) {
			unset($file, $convertFile);
			return ;
		}

		if ($isConvertFile) {
			$this->loadedConvertFiles[] = $convertFile;
			return ;
		}

		$this->convertFiles[] = array(
			'name'           => $file,
			'input_charset'  => $inputCharset,
			'output_charset' => $outputCharset
		);
	}
	
	public function addFiles(array $convertFiles)
	{
		$this->convertAggregates[] = new ConvertAggregate($convertFiles, $this);
	}
	
	public function getConvertFiles()
	{
		if ($this->loadFinshed) {
			return $this->loadedConvertFiles;
		}

		foreach ($this->convertFiles as $convertFile) {
			$this->loadedConvertFiles[] = new ConvertFile($convertFile['name'], 
														  $convertFile['input_charset'], 
														  $convertFile['output_charset'], 
													);
		}

		foreach ($this->convertAggregates as $aggregate) {
			$this->loadedConvertFiles = array_merge($this->loadedConvertFiles, $aggregate->getConvertFiles());
		}

		$this->loadFinshed = true;
		return $this->loadedConvertFiles;
	}

	public function setConvertExtensions(array $convertExtensions)
	{
		foreach ($convertExtensions as $extension) {
			$this->convertExtensions[] = staitc::canonicalExtension($extension);
		}
	}

	public function getConvertExtensions()
	{
		return $this->convertExtensions;
	}

	public static function conanicalPath($path)
	{
		if (!$path = realpath($path)) {
			throw new Exception();
		}

		$path = rtrim(str_replace('\\', '/', $path), '/');
		return $path;
	}

	public static function canonicalExtension($extension)
	{
		if (false !== strpos($extension, '.')) {
			$extension = substr($extension, strrpos($extension, '.') + 1);
		}

		return strtolower($extension);
	}
}
