<?php
namespace Tcc;

use Tcc\Convertor\Convertor;

class CharsetConvertor
{
	protected $convertor;
	protected $convertFileContainer;
	protected $convertedFiles = array();

	public function __construct(ConvertFileContainer $container = null)
	{
		if (!$this->checkEnvironment) {
			throw new \Exception();
		}

		if (!$container) {
			$container = new ConvertFileContainer;
		}
		$this->setConvertFileContainer($container);
	}

	public function checkEnvironment()
	{
		return Convertor::checkEnvironment();
	}

	public function setConvertor($strategy = null)
	{
		$strategy = $strategy ?: 'iconv';

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
			try {
				$this->convertor->convert($convertFile);
				$this->convertedFiles[] = $convertFile->getPathname();
			} catch (\Exception $e) {
				
				break;
			}
		}
	}

	public function getConvertResult()
	{

	}
}
