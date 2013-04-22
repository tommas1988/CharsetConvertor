<?php
class CharsetConvertor
{
	protected $charsets = array();
	protected $extensionNames = array();
	protected $convertResult = array();
	protected $pathname;
	protected $destination;
	protected $targetCharset;
	protected $convertFiles;
	protected $convertor;

	public function __construct($from, $destination, $targetCharset, array $convertExtensionNames)
	{
		$this->setConvertFiles($from);
	}

	public function checkEnviroment()
	{
		return ConvertorFactory::checkEnverioment();
	}
	
	public function setCharsetMapList($charsets)
	{
		
	}
	
	public function addCharsetMap($charset)
	{
		
	}

	public function getCharset($fileInfo)
	{
		if (is_string($fileInfo)) {
			$fileInfo = new SplFileInfo($fileInfo);
		}
		
		if (!$fileInfo instanceof SplFileInfo) {
			throw new InvalidArgumentException('invalid argument');
		}
		
		$charsets = $this->charsets;
		if (isset($charsets['file'][$fileInfo->getPathname()])) {
			return $charsets['file'][$fileInfo->getPathname()];
		} 
		
		if (isset($charsets['dir'])) {
			$charset = $this->getCharsetFromDir($charsets['dir'], $fileInfo);
			
			if ($charset !== 'unknown') {
				return $charset;
			}
		}
		
		if (isset($charsets['*'])) {
			return $charsets['*'];
		}
		
		return 'unknown';
	}

	public function getAllCharsets()
	{
		return $this->charsets;
	}
	
	public function getConvertResult()
	{
		return $this->convertResult;
	}
	
	public function setConvertor(ConvertorInterface $convertor)
	{
		$this->convertor = $convertor;
	}
	
	public function getConvertor()
	{
		if ($this->convertor === null) {
			$this->setConvertor(ConvertorFactory::factory());
		}
		
		return $this->convertor;
	}

	public function setConvertExtensionNames(array $extensionNames)
	{
		array_walk(
			$extensionNames, 
			function(&$value, $index){
				$value = strtolower($value);
			}
		);
		
		$this->extensionNames = $extensionNames;
	}

	public function convert()
	{
		$convertor = $this->getConvertor();
		
		foreach ($this->convertFiles as $fileInfo) {
			$fileContents = '';
			$file = $fileInfo->openFile('r');
			foreach ($file as $line) {
				$fileContents .= $convertor->convert($line, $this->targetCharset);
			}
		}
	}
	
	public function setConvertFiles($convertFiles = null)
	{
		$this->convertFiles = new SplStack();
	
		if ($convertFiles === null) {
			$this->addConvertFiles($convertFiles);
		}
	
		return $this;
	}
	
	public function getConvertFiles()
	{
		if ($this->convertFiles === null) {
			$this->setConvertFiles();
		}
	
		return $this->convertFiles;
	}
	
	public function addConvertFiles($convertFiles)
	{
		if ($this->convertFiles === null) {
			$this->setConvertFiles();
		}
		
		if (is_string($convertFiles) || $convertFiles instanceof SplFileInfo) {
			$this->addConvertFilesFromPathname($convertFiles);
		} elseif (is_array($convertFiles)) {
			$this->addConvertFilesFromArray($files);
		} else {
			throw new InvalidArgumentException('invalid convert files argument');
		}
	}

	protected function addConvertFilesFromPathname($pathnameOrFileInfo)
	{
		if (is_string($pathnameOrFileInfo)) {
			$fileInfo = new SplFileInfo($pathname);
		} elseif ($pathnameOrFileInfo instanceof SplFileInfo) {
			$fileInfo = $pathnameOrFileInfo;
		} else {
			throw new InvalidArgumentException('not string or SplFileInfo instance');
		}

		if (!$fileInfo->isReadable()) {
			throw new RuntimeException('pathname could not be read');
		}

		if (
			$fileInfo->isFile()
			&& $this->checkExtensionName($fileInfo->getExtension())
		) {
			$this->convertFiles->push($fileInfo);
			return ;
		}
		
		if ($fileInfo->isDir()) {
			$fsIterator = new FilesystemIterator($fileInfo->getPathname());
			
			foreach ($fsIterator as $subFileInfo) {
				$this->setConvertFilesFromPathname($subFileInfo);
			}
		}
	}
	
	protected function addConvertFilesFromArray(array $files)
	{
		foreach ($files as $file) {
			$fileInfo = new SplFileInfo($file);
			$this->setConvertFilesFromPathname($fileInfo);
		}
	}

	protected function checkExtensionName($extensionName)
	{
		if (empty($this->extensionNames)) {
			return false;
		}

		$extensionName = strtolower($extensionName);

		return in_array($extensionName, $this->extensionNames);
	}
	
	protected function getCharsetFromDir(array $charsets, SplFileInfo $fileInfo)
	{
		foreach ($charsets as $dirname=>$info) {
			if (strpos($dirname, $fileInfo->getPathname()) === 0) {
				$charset = $this->getCharset($fileInfo);
				if ($charset !== 'unknown') {
					return $charset;
				}
			}
		}
		
		return 'unknown';
	}
}