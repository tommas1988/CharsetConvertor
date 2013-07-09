<?php
namespace Tcc\Test\ScriptFrontend\TestAssert;

use Tcc\Convertor\AbstractConvertor;

class FooConvertor extends AbstractConvertor
{
	public function getName()
	{
		return 'foo';
	}

	public function convert(ConvertFileInterface $convertFile)
	{
		if ($convertFile->getConvertErrorFlag()) {
			throw new Exception();
		}
	}

	protected doConvert()
	{

	}
}
