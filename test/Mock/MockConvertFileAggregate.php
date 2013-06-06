<?php
class MockConvertFileAggregate implements Tcc\ConvertFileAggregateInterface
{
	protected $container;

	public function addConvertFiles(Tcc\ConvertFileContainerInterface $container)
	{
		$this->container = $container;
	}

	public function getConvertFiles()
	{
		$convertFileFoo = new MockConvertFile();
		$convertFileBar = new MockConvertFile();
		$convertFileFoo->setExtension('test');
		$convertFileBar->setExtension('test');

		$this->container->addFile($convertFileFoo);
		$this->container->addFile($convertFileBar);
	}
}
