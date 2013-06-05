<?php
namespace Tcc;

interface ConvertFileInterface
{
	public function getFilename();
	public function getInputCharset();
	public function getOutputCharset();
	public function getExtension();
}
