<?php
namespace Tcc\Test\ScriptFrontend\TestAssert;

use Tcc\ConvertFile\ConvertFile;
use Tcc\Convertor\AbstractConvertor;
use RuntimeException;

class FooConvertor extends AbstractConvertor
{
	public function getName()
	{
		return 'foo';
	}

	public function convert(ConvertFile $convertFile)
	{
		if ($convertFile->getConvertErrorFlag()) {
			throw new RuntimeException();
		}
	}

	protected function doConvert()
	{

	}
}
