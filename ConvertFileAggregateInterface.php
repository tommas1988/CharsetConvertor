<?php
namespace Tcc;

interface ConvertFileAggregateInterface
{
	public function addConvertFiles(ConvertFileContainer $container);
	public function getConvertFiles();
}
