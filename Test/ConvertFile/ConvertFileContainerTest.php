<?php
namespace Tcc\Test\ConvertFile;

use Tcc\ConvertFile\ConvertFileContainer;
use PHPUnit_Framework_TestCase;

class ConvertFileContainerTest extends PHPUnit_Framework_TestCase
{
    protected $container;

    public function setUp()
    {
        $this->container = new ConvertFileContainer();
    }

    public function testAddFile()
    {
        $container = $this->container;

        $result = $container->addFile(__FILE__);

        $this->assertTrue($result);
        $this->assertSame(1, $container->count());
    }

    public function testCanNotAddToContainerIfConvertFileExtensionIsNotInTheContainer()
    {
        $container = $this->container;

        $container->setConvertExtensions(array('txt'));
        $result = $container->addFile(__FILE__);

        $this->assertFalse($result);
        $this->assertSame(0, $container->count());
    }

    public function testCanAddFiles()
    {
        $container = $this->container;

        $aggregate = $this->getMock('Tcc\\ConvertFile\\ConvertFileAggregate',
            array('addConvertFiles'), array(array()));
        $aggregate->expects($this->once())
                  ->method('addConvertFiles')
                  ->with($this->identicalTo($container));

        $result = $container->addFiles($aggregate);

        $this->assertSame($container, $result);
    }

    public function testGetFiles()
    {
        $container = $this->container;
        
        $container->addFile('./ConvertFile/_files/foo.txt');
        $container->addFile('./ConvertFile/_files/bar.txt');

        $this->assertContainsOnlyInstancesOf('Tcc\\ConvertFile\\ConvertFile',
            $container->getFiles());
    }

    public function testCount()
    {
        $container = $this->container;
        
        $container->addFile('./ConvertFile/_files/foo.txt');
        $container->addFile('./ConvertFile/_files/bar.txt');

        $this->assertSame(2, $container->count());
    }

    public function testClearFiles()
    {
        $container = $this->container;
        
        $container->addFile('./ConvertFile/_files/foo.txt');
        $container->addFile('./ConvertFile/_files/bar.txt');

        $this->assertSame(2, $container->count());
        $result = $container->clearFiles();
        $this->assertSame($container, $result);
        $this->assertSame(0, $container->count());
    }

    public function testAddConvertExtension()
    {
        $container = $this->container;
        $expects   = array('php', 'txt');

        $result = $container->addConvertExtension('foo.php')
                            ->addConvertExtension('Txt')
                            ->addConvertExtension('php');

        $this->assertEquals($expects, $container->getConvertExtensions());
    }

    public function testAddFileExtensionWillRaiseExceptionIfExtensionIsNotString()
    {
        $this->setExpectedException('InvalidArgumentException',
            'Invalid file extension, type: boolean, value: false');

        $container = $this->container;
        $container->addConvertExtension(false);
    }

    public function testSetConvertFilesWithNull()
    {
        $container = $this->container;

        $container->addConvertExtension('php')
                  ->addConvertExtension('txt');

        $this->assertEquals(array('php', 'txt'),
            $container->getConvertExtensions());

        $result = $container->setConvertExtensions();
        $this->assertSame($container, $result);
        $this->assertSame(null, $container->getConvertExtensions());
    }

    public function testSetConvertFilesWillArray()
    {
        $container = $this->container;

        $container->addConvertExtension('php');
        $this->assertEquals(array('php'),
            $container->getConvertExtensions());

        $result = $container->setConvertExtensions(array('txt'));
        $this->assertSame($container, $result);
        $this->assertEquals(array('txt'),
            $container->getConvertExtensions());
    }
}
