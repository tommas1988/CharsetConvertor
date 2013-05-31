<?php
class ConvertFileContainerTest extends PHPUnit_Framework_TestCase
{
	public function testAddNotExistsFileThrowException()
	{
		$this->setExceptedException('Exception');

		$container = new ConvertFileContainer();
		$container->addFile('not_exists');
	}

	public function testAddFileWithNotAllowedExtension()
	{
		
	}
}
