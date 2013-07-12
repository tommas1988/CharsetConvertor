<?php
namespace Tcc\Test\ConvertFile;

use Tcc\ConvertFile\ConvertFileAggregate;
use PHPUnit_Framework_TestCase;
use Tcc\Test\ConvertFile\Mock\MockConvertFileContainer;

class ConvertFileAggregateTest extends PHPUnit_Framework_TestCase
{
    protected $container;

    public function setUp()
    {
        $this->container = new MockConvertFileContainer();
        $this->container->setConvertExtensions(array('txt'));
    }

    public function testAddConvertFiles()
    {
        $convertFiles = array(
            'input_charset'  => 'in-charset',
            'output_charset' => 'out-charset',
            'files' => array(1, 2, 3),
            'dirs'  => array(1, 2),
        );

        $aggregate = $this->getMock('Tcc\\ConvertFile\\ConvertFileAggregate',
            array('resolveFileOptions', 'resolveDirOption'), $convertFiles);
        $aggregate->expects($this->exactly(3))
                  ->method('resolveFileOptions');

        $aggregate->expects($this->exactly(2))
                  ->method('resolveDirOptions');

        $container = $this->getMock('Tcc\\ConvertFile\\ConvertFileContainer');

        $aggregate->addConvertFiles($container);
    }

    public function testConvertFileWillNotAddToContainerIfItHasBeenSetted()
    {
        $convertFiles = array(
            'files' => array(),
        );
    }

    public function loadConvertFilesWillRaiseExceptionIfNotAddConvertFileFirst()
    {
        $this->setExpectedException('RuntimeException',
            'You have not add convert files yet');

        $aggregate = new ConvertFileAggregate(array());
        $aggregate->loadConvertFiles();
    }




    public function testAddOnlyFilesWithGlobalCharset()
    {
        $convertFiles = array(
            'input_charset'  => 'foo',
            'output_charset' => 'bar',
            'files'          => array(
                array('name' => './_files/bar.txt',),
            ),
        );
        $expected = array(
            array(
                'name'           => static::canonicalPath('./_files/bar.txt'),
                'input_charset'  => 'foo',
                'output_charset' => 'bar',
            ),
        );

        $aggregate = new ConvertFileAggregate($convertFiles);
        $container = $this->container;

        $aggregate->addConvertFiles($container);
        $result = $container->getConvertFiles();

        $this->assertEquals($expected, $result);
    }

    public function testAddOnlyFilesWithSpecificCharset()
    {
        $convertFiles = array(
            'input_charset'  => 'foo',
            'output_charset' => 'bar',
            'files'          => array(
                array(
                    'name'           => './_files/bar.txt',
                    'input_charset'  => 'spec_foo',
                    'output_charset' => 'spec_bar',
                ),
            ),
        );
        $expected = array(
            array(
                'name'           => static::canonicalPath('./_files/bar.txt'),
                'input_charset'  => 'spec_foo',
                'output_charset' => 'spec_bar',
            ),
        );
        $aggregate = new ConvertFileAggregate($convertFiles);
        $container = $this->container;

        $aggregate->addConvertFiles($container);
        $result = $container->getConvertFiles();

        $this->assertEquals($expected, $result);
    }

    public function testAddOnlyDirsWithGlobalCharset()
    {
        $convertFiles = array(
            'input_charset'  => 'foo',
            'output_charset' => 'bar',
            'dirs' => array(
                array('name' => './_files'),
            ),
        );
        $expected = array(
            array(
                'name'           => static::canonicalPath('./_files/bar.txt'),
                'input_charset'  => 'foo',
                'output_charset' => 'bar',
            ),
            array(
                'name'           => static::canonicalPath('./_files/foo.txt'),
                'input_charset'  => 'foo',
                'output_charset' => 'bar',
            ),
            array(
                'name'           => static::canonicalPath('./_files/foo_dir/sub_foo.txt'),
                'input_charset'  => 'foo',
                'output_charset' => 'bar',
            ),
        );

        $aggregate = new ConvertFileAggregate($convertFiles);
        $container = $this->container;

        $aggregate->addConvertFiles($container);
        $aggregate->getConvertFiles();
        $result = $container->getConvertFiles();

        $this->assertEquals($expected, $result);
    }

    public function testAddOnlyDirsWithSpecificCharset()
    {
        $convertFiles = array(
            'input_charset'  => 'foo',
            'ouptut_charset' => 'bar',
            'dirs' => array(
                 array(
                    'name'           => './_files',
                    'input_charset'  => 'spec_foo',
                    'output_charset' => 'spec_bar',
                ),
            ),
        );
        $expected = array(
            array(
                'name'           => static::canonicalPath('./_files/bar.txt'),
                'input_charset'  => 'spec_foo',
                'output_charset' => 'spec_bar',
            ),
            array(
                'name'           => static::canonicalPath('./_files/foo.txt'),
                'input_charset'  => 'spec_foo',
                'output_charset' => 'spec_bar',
            ),
            array(
                'name'           => static::canonicalPath('./_files/foo_dir/sub_foo.txt'),
                'input_charset'  => 'spec_foo',
                'output_charset' => 'spec_bar',
            ),
        );

        $aggregate = new ConvertFileAggregate($convertFiles);
        $container = $this->container;

        $aggregate->addConvertFiles($container);
        $aggregate->getConvertFiles();
        $result = $container->getConvertFiles();

        $this->assertEquals($expected, $result);
    }

    public function testCanSkipFilesAndDirsThatAlreadyAdded()
    {
        $convertFiles = array(
            'input_charset'  => 'foo',
            'output_charset' => 'bar',
            'files' => array(
                 array(
                     'name' => './_files/bar.txt',
                ),
            ),
            'dirs' => array(
                array(
                    'name'    => './_files',
                    'subdirs' => array(
                         array('name' => 'foo_dir'),
                    ),
                ),
            ),
        );
        $expected = array(
            array(
                'name'           => static::canonicalPath('./_files/bar.txt'),
                'input_charset'  => 'foo',
                'output_charset' => 'bar',
            ),
            array(
                'name'           => static::canonicalPath('./_files/foo_dir/sub_foo.txt'),
                'input_charset'  => 'foo',
                'output_charset' => 'bar',
            ),
            array(
                'name'           => static::canonicalPath('./_files/foo.txt'),
                'input_charset'  => 'foo',
                'output_charset' => 'bar',
            ),
        );

        $aggregate = new ConvertFileAggregate($convertFiles);
        $container = $this->container;

        $aggregate->addConvertFiles($container);
        $aggregate->getConvertFiles();
        $result = $container->getConvertFiles();


        $this->assertEquals($expected, $result);
    }

    protected static function canonicalPath($path)
    {
        if (!$path = realpath($path)) {
            throw new \Exception();
        }

        $path = rtrim(str_replace('\\', '/', $path), '/');
        return $path;
    }
}
