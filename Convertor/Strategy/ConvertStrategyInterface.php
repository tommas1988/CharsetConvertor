<?php
namespace Tcc\Convertor\Strategy;

interface ConvertInterface
{
	public function convert($contents, $inputCharset, $outputCharset);
	public function getName();
}