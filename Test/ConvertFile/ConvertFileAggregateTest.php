<?php
namespace Tcc\Test\ConvertFile;

use Tcc\ConvertFile\ConvertFileAggregate;
use Tcc\ConvertFile\ConvertFileContainer;
use PHPUnit_Framework_TestCase;

class ConvertFileAggregateTest extends PHPUnit_Framework_TestCase
{
    public function validConvertFilesOptions()
    {
        $convertFilesOptions = array(
            'input_charset'  => 'g-in-charset',
            'output_charset' => 'g-out-charset',
            'files' => array(
                array(
                    'name'           => __FILE__,
                    'input_charset'  => 'f-in-charset',
                    'output_charset' => 'f-out-charset',
                ),
            ),
            'dirs' => array(
                array(
                    'name'           => './ConvertFile/_files',
                    'input_charset'  => 'd-in-charset',
                    'output_charset' => 'd-out-charset',
                    'files' => array(
                        array(
                            'name'           => 'foo.txt',
                            'input_charset'  => 'f-d-in-charset',
                            'output_charset' => 'f-d-out-charset',
                        ),
                    ),
                    'subdirs' => array(
                        array(
                            'name'           => 'foo',
                            'input_charset'  => 'sd-in-charset',
                            'output_charset' => 'sd-out-charset',
                        ),
                    ),
                ),
            ),
        );

        return array(
            array($convertFilesOptions),
        );
    }

    public function invalidConvertFileOptions()
    {
        $convertDirOptions = array(
            'files' => array(
                array(
                    'input_charset'  => 'f-in-charset',
                    'output_charset' => 'f-out-charset',
                ),
            ), 
        );

        return array(
            array($convertDirOptions),
        );
    }

    public function invalidConvertDirOptions()
    {
        $convertFileOptions = array(
            'dirs' => array(
                array(
                    'input_charset'  => 'd-in-charset',
                    'output_charset' => 'd-out-charset',
                ),
            ),
        );

        return array(
            array($convertFileOptions),
        );
    }

    /**
     * @dataProvider validConvertFilesOptions
     */
    public function testAddConvertFiles($convertFilesOptions)
    {
        $expectConvertFiles = array(
            array(
                'name'           => $this->canonicalPath(__FILE__),
                'input_charset'  => 'f-in-charset',
                'output_charset' => 'f-out-charset',
            ),
            array(
                'name'           => $this->canonicalPath('./ConvertFile/_files/foo.txt'),
                'input_charset'  => 'f-d-in-charset',
                'output_charset' => 'f-d-out-charset',
            ),
        );
        $expectConvertDirs = array(
            array(
                'name'           => $this->canonicalPath('./ConvertFile/_files/foo'),
                'input_charset'  => 'sd-in-charset',
                'output_charset' => 'sd-out-charset',
            ),
            array(
                'name'           => $this->canonicalPath('./ConvertFile/_files'),
                'input_charset'  => 'd-in-charset',
                'output_charset' => 'd-out-charset',
            ),
        );
        $expectFilters = array(
            'files' => array(
                $this->canonicalPath(__FILE__),
                $this->canonicalPath('./ConvertFile/_files/foo.txt'),
            ),
            'dirs' => array(
                $this->canonicalPath('./ConvertFile/_files/foo'),
                $this->canonicalPath('./ConvertFile/_files'),
            ),
        );

        $aggregate = new ConvertFileAggregate($convertFilesOptions);

        $container = $this->getMock('Tcc\\ConvertFile\\ConvertFileContainer');
        $container->expects($this->exactly(4))
                  ->method('addFile')
                  ->with($this->logicalOr($this->isType('string'),
                    $this->isInstanceOf('Tcc\\ConvertFile\\ConvertFile')));

        $aggregate->addConvertFiles($container);

        $this->assertEquals($expectConvertFiles, $aggregate->getConvertFiles());
        $this->assertEquals($expectConvertDirs, $aggregate->getConvertDirs());
        $this->assertEquals($expectFilters, $aggregate->getFilters());
    }

    /**
     * @dataProvider invalidConvertFileOptions
     */
    public function testAddConvertFilesWillRaiseExceptionWithInvalidConvertFileOptions($convertFileOptions)
    {
        $this->setExpectedException('InvalidArgumentException',
            'convert file options must contain a name field');

        $aggregate = new ConvertFileAggregate($convertFileOptions);
        $aggregate->addConvertFiles(new ConvertFileContainer);
    }

    /**
     * @dataProvider invalidConvertDirOptions
     */
    public function testAddConvertFilesWillRaiseExceptionWithInvalidConvertDirOptions($convertDirOptions)
    {
        $this->setExpectedException('InvalidArgumentException',
            'convert directory options must contain a name field');

        $aggregate = new ConvertFileAggregate($convertDirOptions);
        $aggregate->addConvertFiles(new ConvertFileContainer);
    }

    public function testSetDirectoryIteratorClassWillRaiseExceptionIfClassIsNotString()
    {
        $this->setExpectedException('InvalidArgumentException',
            'Invalid iterator class: false');

        $aggregate = new ConvertFileAggregate(array());
        $aggregate->setDirectoryIteratorClass(false);
    }

    public function testSetDirectoryIteratorClassWillRaiseExcetptionIfClassIsNotTraversable()
    {
        $this->setExpectedException('InvalidArgumentException',
                "Invalid iterator class: 'stdClass'");

        $aggregate = new ConvertFileAggregate(array());
        $aggregate->setDirectoryIteratorClass('stdClass');
    }

    public function testSetDirectoryIteratorClass()
    {
        $aggregate = new ConvertFileAggregate(array());
        $result = $aggregate->setDirectoryIteratorClass('DirectoryIterator');

        $this->assertSame($aggregate, $result);
    }

    public function testGetDirectoryIteratorClass()
    {
        $aggregate = new ConvertFileAggregate(array());
        $aggregate->setDirectoryIteratorClass('DirectoryIterator');

        $this->assertEquals('DirectoryIterator',
            $aggregate->getDirectoryIteratorClass());
    }

    public function testGetDiretoryIteratorClassWillReturnADefualtOneIfNotSetBefore()
    {
        $aggregate = new ConvertFileAggregate(array());

        $this->assertEquals('Tcc\\ConvertFile\\Iterator\\ConvertDirectoryIterator',
            $aggregate->getDirectoryIteratorClass());
    }

    protected static function canonicalPath($path)
    {
        if (!$path = realpath($path)) {
            throw new \InvalidArgumentException(sprintf(
                'The path is not valid: %s', $path));
        }

        $path = rtrim(str_replace('\\', '/', $path), '/');
        return $path;
    }
}
