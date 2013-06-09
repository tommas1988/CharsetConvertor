<?php
namespace Test\ConvertFile;

require '../../ConvertFile/ConvertFileContainerInterface.php';
require '../../ConvertFile/ConvertFileContainer.php';
require '../../ConvertFile/ConvertFileInterface.php';
require '../../ConvertFile/ConvertFile.php';
require '../../ConvertFile/ConvertFileAggregateInterface.php';
require './Mock/MockConvertFile.php';
require './Mock/MockConvertFileAggregate.php';

use Tcc\ConvertFile\ConvertFileContainer;
use PHPUnit_Framework_TestCase;
use Test\ConvertFile\Mock\MockConvertFile;
use Test\ConvertFile\Mock\MockConvertFileAggregate;
use SplFileInfo;


class ConvertFileContainerTest extends PHPUnit_Framework_TestCase
{
	public function testAddNotExistsFileThrowException()
	{
		$this->setExpectedException('Exception');

		$container = new ConvertFileContainer();

		$container->addFile('not_exists_file');
	}

	public function testAddFileWithNotAllowedExtension()
	{
		$container = new ConvertFileContainer();

		$container->setConvertExtensions(array('txt'));
		$result = $container->addFile(__FILE__);

		$this->assertFalse($result);
	}

	public function testCanAddFileName()
	{
		$container = new ConvertFileContainer();

		$container->setConvertExtensions(array('php'));
		$result = $container->addFile(__FILE__);

		$this->assertTrue($result);
	}

	public function testCanAddConvertFileObject()
	{
		$convertFile = new MockConvertFile();
		$container   = new ConvertFileContainer();

		$convertFile->setExtension('foo');
		$container->setConvertExtensions(array('foo'));

		$result = $container->addFile($convertFile);
		$this->assertTrue($result);
	}

	public function testCanAddSplFileInfo()
	{
		$fileInfo  = new SplFileInfo(__FILE__);
		$container = new ConvertFileContainer();

		$container->setConvertExtensions(array('php'));
		$result = $container->addFile($fileInfo);
		$this->assertTrue($result);
	}

	public function testCanAddFiles()
	{
		$aggregate = new MockConvertFileAggregate();
		$container = new ConvertFileContainer();

		$container->setConvertExtensions(array('test'));
		$container->addFiles($aggregate);
		$convertFiles = $container->getConvertFiles();
		$this->assertEquals(2, count($convertFiles));
	}

	public function testLoadedConvertFilesAreAllConvertFileObject()
	{
		$container      = new ConvertFileContainer();
		$fileInfo       = new SplFileInfo(__FILE__);
		$aggregate      = new MockConvertFileAggregate();
		$convertFileObj = new MockConvertFile();

		$container->setConvertExtensions(array('php', 'foo', 'test'));
		$convertFileObj->setExtension('foo');

		$container->addFile(__FILE__);
		$container->addFile($convertFileObj);
		$container->addFile($fileInfo);
		$container->addFiles($aggregate);

		$convertFiles = $container->getConvertFiles();
		$this->assertEquals(5, count($convertFiles));
		foreach ($convertFiles as $convertFile) {
			$this->assertInstanceOf('Tcc\\ConvertFile\\ConvertFileInterface', $convertFile);
		}
	}

	public function testSetCanonicalConvertExtensions()
	{
		$extensions = array('PHP', 'Txt', 'Php');
		$expected   = array('php', 'txt');
		$container  = new ConvertFileContainer();

		$container->setConvertExtensions($extensions);
		$result = $container->getConvertExtensions();
		$this->assertEquals($expected, $result, var_export($result, 1));
	}
}
