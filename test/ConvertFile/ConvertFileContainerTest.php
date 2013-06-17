<?php
namespace Tcc\Test\ConvertFile;

use Tcc\ConvertFile\ConvertFileContainer;
use PHPUnit_Framework_TestCase;
use Tcc\Test\ConvertFile\Mock\MockConvertFile;
use Tcc\Test\ConvertFile\Mock\MockConvertFileAggregate;
use SplFileInfo;


class ConvertFileContainerTest extends PHPUnit_Framework_TestCase
{
    protected $container;

    public function setUp()
    {
        $this->container = new ConvertFileContainer();
    }

    public function testAddNotExistsFileThrowException()
    {
        $this->setExpectedException('Exception');
        $container = $this->container;

        $container->addFile('not_exists_file');
    }

    public function testAddFileWithNotAllowedExtension()
    {
        $container = $this->container;

        $container->setConvertExtensions(array('txt'));
        $result = $container->addFile(__FILE__);

        $this->assertFalse($result);
    }

    public function testCanAddFileName()
    {
        $container = $this->container;

        $container->setConvertExtensions(array('php'));
        $result = $container->addFile(__FILE__);

        $this->assertTrue($result);
    }

    public function testCanAddConvertFileObject()
    {
        
        $container   = $this->container;
        $convertFile = new MockConvertFile();

        $convertFile->setExtension('foo');
        $container->setConvertExtensions(array('foo'));

        $result = $container->addFile($convertFile);
        $this->assertTrue($result);
    }

    public function testCanAddSplFileInfo()
    {
        $container = $this->container;
        $fileInfo  = new SplFileInfo(__FILE__);

        $container->setConvertExtensions(array('php'));
        $result = $container->addFile($fileInfo);
        $this->assertTrue($result);
    }

    public function testCanAddFiles()
    {
        $container = $this->container;
        $aggregate = new MockConvertFileAggregate();

        $container->setConvertExtensions(array('test'));
        $container->addFiles($aggregate);
        $convertFiles = $container->getConvertFiles();
        $this->assertEquals(2, count($convertFiles));
    }

    public function testLoadedConvertFilesAreAllConvertFileObject()
    {
        $container      = $this->container;
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
        $container = $this->container;
        $extensions = array('PHP', 'Txt', 'Php');
        $expected   = array('php', 'txt');

        $container->setConvertExtensions($extensions);
        $result = $container->getConvertExtensions();
        $this->assertEquals($expected, $result, var_export($result, 1));
    }
}
