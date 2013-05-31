<?php
namespace Tcc\Convertor\Strategy;

class IConvConvertStrategy implements ConvertStrategyInterface
{
	public function convert($contents, $inputCharset, $outputCharset)
	{
		
	}

	public function getName()
	{
		return 'iconv';
	}
}
