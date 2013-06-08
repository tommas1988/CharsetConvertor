<?php
namespace Tcc;

class ConvertFileContainer implements ConvertFileContainerInterface
{
	protected $loadedConvertFiles = array();
	protected $convertFiles       = array();
	protected $convertAggregates  = array();
	protected $convertExtensions  = array();
	protected $convertFileClass;

	protected $loadFinshed        = false;
	
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

	public function addFile($convertFile, $inputCharset = null, $outputCharset = null)
	{
		$isConvertFile = false;

		if (is_string($convertFile)) {
			$file = new \SplFileInfo($convertFile);
		} elseif ($convertFile instanceof \SplFileInfo) {
			$file = $convertFile;
		} elseif ($convertFile instanceof ConvertFileInterface) {
			$isConvertFile = true;
			$file          = $convertFile;
		} else {
			throw new \Exception();
		}

		if (!$isConvertFile && !$file->isReadable()) {
			throw new \Exception();
		}

		$extension = static::canonicalExtension($file->getExtension());
		if (!in_array($extension, $this->getConvertExtensions())) {
			unset($file, $convertFile);
			return false;
		}

		if ($isConvertFile) {
			$this->loadedConvertFiles[] = $convertFile;
			return true;
		}

		$this->convertFiles[] = array(
			'name'           => $file,
			'input_charset'  => $inputCharset,
			'output_charset' => $outputCharset
		);
		return true;
	}
	
	public function addFiles(ConvertFileAggregateInterface $aggregate)
	{
		$aggregate->addConvertFiles($this);
		$this->convertAggregates[] = $aggregate;
	}
	
	public function getConvertFiles()
	{
		if ($this->loadFinshed) {
			return $this->loadedConvertFiles;
		}

		$convertFileClass = $this->getConvertFileClass();

		foreach ($this->convertFiles as $convertFile) {
			$this->loadedConvertFiles[] = new $convertFileClass(
					$convertFile['name'], 
					$convertFile['input_charset'], 
					$convertFile['output_charset']
				);
		}

		foreach ($this->convertAggregates as $aggregate) {
			$aggregate->getConvertFiles();
		}

		$this->loadFinshed = true;
		return $this->loadedConvertFiles;
	}

	public function clearConvertFiles()
	{
		$this->loadedConvertFiles = array();
		$this->convertFiles       = array();
		$this->convertAggregates  = array();
		$this->loadFinshed        = false;
	}

	public function setConvertExtensions(array $convertExtensions)
	{
		foreach ($convertExtensions as $extension) {
			$extension = static::canonicalExtension($extension);
			if (!in_array($extension, $this->convertExtensions)) {
				$this->convertExtensions[] = $extension;
			}
		}
	}

	public function getConvertExtensions()
	{
		return $this->convertExtensions;
	}

	public static function canonicalPath($path)
	{
		if (!$path = realpath($path)) {
			throw new \Exception();
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
