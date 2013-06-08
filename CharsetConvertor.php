<?php
namespace Tcc;

use Tcc\Convertor\Convertor;

class CharsetConvertor
{
	protected $convertor;
	protected $convertFileContainer;
	protected $convertedFiles = array();

	public function __construct(ConvertFileContainerInterface $container = null)
	{
		if (!$this->checkEnvironment) {
			throw new \Exception();
		}

		if ($container) {
			$this->setConvertFileContainer($container);
		}
	}

	public function checkEnvironment()
	{
		return Convertor::checkEnvironment();
	}

	public function setConvertor($strategy)
	{
		if (is_string($strategy)) {
			if (!$strategyClass = Convertor::getConvertStrategyClass($strategy)) {
				throw new \Exception();
			}
			$convertStrategy = new $strategyClass;
		} elseif ($strategy instanceof ConvertStrategyInterface) {
			$convertStrategy = $strategy;
		} else {
			throw new \Exception();
		}

		$this->convertor = new Convertor($convertStrategy);
	}

	public function getConvertor()
	{
		if (!$this->convertor) {
			$this->setConvertor('iconv');
		}

		return $this->convertor;
	}

	public function setConvertFileContainer(ConvertFileContainerInterface $container)
	{
		$this->convertFileContainer = $container;
	}

	public function getConvertFileContainer()
	{
		if (!$this->convertFileContainer) {
			$this->setConvertFileContainer(new ConvertFileContainer);
		}

		return $this->convertFileContainer;
	}

	public function addConvertFile($convertFile, $inputCharset, $outputCharset)
	{
		$container = $this->getConvertFileContainer();
		$container->addFile($convertFile, $inputCharset, $outputCharset);
	}

	public function addConvertFiles(array $convertFiles)
	{
		$container = $this->getConvertFileContainer();
		$container->addFiles($convertFiles);
	}

	public function emptyConvertFiles()
	{
		$container = $this->getConvertFileContainer();
		$container->clearConvertFiles();
	}

	public function convert()
	{
		$convertFiles = $this->container->getConvertFiles();

		foreach ($convertFiles as $convertFile) {
			try {
				$this->convertor->convert($convertFile);
				$this->convertedFiles[] = $convertFile->getPathname();
			} catch (\Exception $e) {
				
			}
		}
	}

	public function getConvertResult()
	{

	}
}
