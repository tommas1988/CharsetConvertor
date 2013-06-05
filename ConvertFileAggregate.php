<?php
namespace Tcc;

use Tcc\Iterator\ConvertDirectoryIterator;

class ConvertFileAggregate implements ConvertFileAggregateInterface
{
	protected $loadedConvertFiles = array();
	protected $convertFiles       = array();
	protected $convertDirs        = array();
	protected $filenames          = array();
	protected $container;

	protected $loadFinished       = false;
	
	public function __construct(array $convertFiles)
	{
		$this->convertFiles = $convertFiles;
	}

	public function getRawConvertFiles()
	{
		return $this->convertFiles;
	}

	public function getConvertDirs()
	{
		return $this->convertDirs;
	}
	
	public function addConvertFiles(ConvertFileContainterInterface $container)
	{
		$this->container = $container;
		$convertFiles    = $this->convertFiles;

		$inputCharset  = (isset($convertFiles['input_charset'])) ? $convertFiles['input_charset'] : null;
		$outputCharset = (isset($convertFiles['output_charset'])) ? $convertFiles['output_charset'] : null;

		if (isset($convertFiles['files'])) {
			foreach ($convertFiles['files'] as $convertFileOption) {
				$this->resolveFileOption($convertFileOption, $inputCharset, $outputCharset);
			}
		}

		if (isset($convertFiles['dirs'])) {
			foreach ($convertFiles['dirs'] as $dirOption) {
				$this->resolveDirOptions($dirOption, $inputCharset, $outputCharset);
			}
		}

	}

	public function getConvertFiles()
	{
		if ($this->loadFinished) {
			return $this->loadedConvertFiles;
		}

		$filters = array(
			'files'     => $this->filenames,
			'dirs'      => array(),
			'extensions' => $this->container->getConvertExtensions(),
		);
		foreach ($this->convertDirs as $dir) {
			$iterator = new ConvretDirectoryIterator($dir, $filters);
			foreach (new \RecursiveIteratorIterator($iterator) as $convertFile) {
				$this->loadedConvertFiles[] = $convertFile;
			}
			$filters['dirs'][] = $dir['name'];
		}

		$this->loadFinished = true;
		return $this->loadedConvertFiles;
	}

	protected function resolveFileOption(array $option, $inputCharset, $outputCharset)
	{
		if (!isset($option['name'])) {
			throw new \Exception();
		}

		$convertFile = ConvertFileContainer::conanicalPath($option['name']);
		if (in_array($convertFile ,$this->filenames) {
			return ;
		}

		$inputCharset  = (isset($option['input_charset']) ? $option['input_charset'] : $inputCharset;
		$outputCharset = (isset($option['output_charset'])) ? $option['output_charset'] : $outputCharset;

		$this->filenames[] = $convertFile;
		$this->container->addFile($convertFile, $inputCharset, $outputCharset);
	}

	protected function resolveDirOptions(array $dirOption, $inputCharset = null, $outputCharset = null, $parentDir = null)
	{
		if (!isset($dirOption['name'])) {
			throw new \Exception();
		}

		$concatWithParentDir = function($parentDir, $name) {
			$pathname = $parentDir . '/' . rtrim(ltrim(basename($name), '/'), '/');
			return ConvertFileContainer::canonicalPath($pathname);
		};

		if (!isset($dirOption['input_charset'])) {
			$dirOption['input_charset'] = $inputCharset;
		}
		if (!isset($dirOption['output_charset'])) {
			$dirOption['output_charset'] = $outputCharset;
		}

		$inputCharset  = $dirOption['input_charset'];
		$outputCharset = $dirOption['output_charset'];

		if (is_null($parentDir)) {
			$dirname = ConvertFileContainer::canonicalPath($dirOption['name']);
		} else {
			$dirname = $concatWithParentDir($parentDir, $dirOption['name']);
		}
		$dirOption['name'] = $dirname;

		if (isset($dirOption['subdirs'])) {
			foreach ($dirOption['subdirs'] as $subdirOption) {
				$this->resolveDirOptions($subdirOption, $inputCharset, $outputCharset, $dirname);
			}
			unset($dirOption['subdirs']);
		}

		if (isset($dirOption['files'])) {
			foreach ($dirOption['files'] as $convertFileOption) {
				if (!isset($convertFileOption['name'])) {
					throw new \Exception();
				}

				$convertFileOption['name'] = $concatWithParentDir($dirname, $convertFileOption['name']);
				$this->resolveFileOption($convertFileOption, $inputCharset, $outputCharset);
			}
			unset($dirOption['files']);
		}

		$this->convertDirs[] = $dirOption;
	}
}
