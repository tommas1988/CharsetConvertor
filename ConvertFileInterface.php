<?php
namespace Tcc;

use IteratorAggregate;

interface ConvertFileInterface extends IteratorAggregate
{
	public function getFilename();
	public function getInputCharset();
	public function getOutputCharset();
	public function getExtension();
}
