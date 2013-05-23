<?php
class CharsetConvertor
{
	protected $convertor;
	protected $convertorType;
	protected $convertFileContainer;

	public function __construct(ConvertFileContainer $container = null)
	{
		if (!$this->checkEnvironment) {
			throw new Exception();
		}

		if (!$container) {
			$container = new ConvertFileContainer;
		}
		$this->setConvertFileContainer($container);
	}

	public function checkEnvironment()
	{
		return ConvertorFactory::checkEnvironment();
	}

	public function setConvertor($type = null)
	{
		$availableConvertors = ConvertorFactory::getAvailableConvertors();
		$convertorNames      = array_keys($availableConvertors);

		if (is_null($type) && !empty($availableConvertors)) {
			$type = $convertorNames[0];
		}

		$type = strtolower($type);
		if (!in_array($type, $convertorNames)) {
			throw new Exception();
		}

		$convertor = ConvertorFactory::factory($type);
		$this->convertorType = $type;
		$this->convertor     = $convertor;
	}

	public function getConvertor()
	{
		if (!$this->convertor) {
			$this->setConvertor();
		}

		return $this->convertor;
	}

	public function setConvertFileContainer(ConvertFileContainer $container)
	{
		$this->convertFileContainer = $container;
	}

	public function setConvertFile(ConvertFileContainer $container = null, $convertFile = null)
	{
		if ($container === null) {
			$container = new ConvertFileContainer;
		}

		if ($convertFile !== null) {
			$container->addFile($convertFile);
		}

		$this->setConvertFileContainer($container);
	}

	public function setConvertFiles(ConvertFileContainer $container = null, array $convertFiles = null)
	{
		if ($container === null) {
			$container = new ConvertFileContainer;
		}

		if ($convertFiles === null) {
			$container->addFiles($convertFiles);
		}

		$this->setConvertFileContainer($container);
	}

	public function addConvertFile($convertFile, $inputCharset, $outputCharset)
	{
		$this->container->addFile()
	}

	public function addConvertFiles(array $convertFiles)
	{
		$this->container->addFiles($convertFiles);
	}

	public function convert()
	{
		$convertFiles = $this->container->getConvertFiles();

		foreach ($convertFiles as $convertFile) {
			$this->convert($convertFile);
		}
	}

	public function getConvertResult()
	{

	}
}
