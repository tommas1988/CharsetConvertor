<?php
require '../ConvertFileAggregateInterface.php';
require '../ConvertFileAggregate.php';
require '../ConvertFileContainerInterface.php';
require '../ConvertFileContainer.php';
require '../Iterator/ConvertDirectoryIterator.php';
require '../ConvertFileInterface.php';
require '../ConvertFile.php';
require './Mock/MockConvertFileContainer.php';

use Tcc\ConvertFileAggregate;

class ConvertFileAggregateTest extends PHPUnit_Framework_TestCase
{
	public function testAddOnlyFilesWithGlobalCharset()
	{
		$convertFiles = array(
				'input_charset'  => 'foo',
				'output_charset' => 'bar',
				'files'          => array(
						array('name' => './_files_&_dirs/bar.txt',)
					),
			);
		$expected = array(
				array(
						'name'           => static::canonicalPath('./_files_&_dirs/bar.txt'),
						'input_charset'  => 'foo',
						'output_charset' => 'bar',
					),
			);

		$aggregate = new ConvertFileAggregate($convertFiles);
		$container = new MockConvertFileContainer();

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
								'name'           => './_files_&_dirs/bar.txt',
								'input_charset'  => 'spec_foo',
								'output_charset' => 'spec_bar',
							),
					),
			);
		$expected = array(
				array(
						'name'           => static::canonicalPath('./_files_&_dirs/bar.txt'),
						'input_charset'  => 'spec_foo',
						'output_charset' => 'spec_bar',
					),
			);
		$aggregate = new ConvertFileAggregate($convertFiles);
		$container = new MockConvertFileContainer();

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
						array('name' => './_files_&_dirs'),
					),
			);
		$expected = array(
				array(
						'name'           => static::canonicalPath('./_files_&_dirs'),
						'input_charset'  => 'foo',
						'output_charset' => 'bar',
					),
			);

		$aggregate = new ConvertFileAggregate($convertFiles);
		$container = new MockConvertFileContainer();

		$container->setConvertExtensions(array('txt'));
		$aggregate->addConvertFiles($container);
		$result = $aggregate->getConvertDirs();

		$this->assertEquals($expected, $result);
	}

	public function testAddOnlyDirsWithSpecificCharset()
	{
		$convertFiles = array(
				'input_charset'  => 'foo',
				'ouptut_charset' => 'bar',
				'dirs' => array(
						array(
								'name'           => './_files_&_dirs',
								'input_charset'  => 'spec_foo',
								'output_charset' => 'spec_bar',
							),
					),
			);
		$expected = array(
				array(
						'name'           => static::canonicalPath('./_files_&_dirs'),
						'input_charset'  => 'spec_foo',
						'output_charset' => 'spec_bar',
					),
			);

		$aggregate = new ConvertFileAggregate($convertFiles);
		$container = new MockConvertFileContainer();

		$container->setConvertExtensions(array('txt'));
		$aggregate->addConvertFiles($container);
		$result = $aggregate->getConvertDirs();

		$this->assertEquals($expected, $result);
	}

	public function testCanSkipFilesAndDirsThatAlreadyAdded()
	{
		$convertFiles = array(
				'input_charset'  => 'foo',
				'output_charset' => 'bar',
				'files' => array(
						array(
								'name' => './_files_&_dirs/bar.txt',
							),
					),
				'dirs' => array(
						array(
								'name' => './_files_&_dirs',
							),
					),
			);
		$expected = array(
				array(
						'name'           => static::canonicalPath('./_files_&_dirs/bar.txt'),
						'input_charset'  => 'foo',
						'output_charset' => 'bar',
					),
				array(
						'name'           => static::canonicalPath('./_files_&_dirs/foo.txt'),
						'input_charset'  => 'foo',
						'output_charset' => 'bar',
					),
				array(
						'name'           => static::canonicalPath('./_files_&_dirs/foo_dir/sub_foo.txt'),
						'input_charset'  => 'foo',
						'output_charset' => 'bar',
					),
			);

		$aggregate = new ConvertFileAggregate($convertFiles);
		$container = new MockConvertFileContainer();

		$container->setConvertExtensions(array('txt'));
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
