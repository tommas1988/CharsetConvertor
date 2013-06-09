<?php
namespace Tcc\ConvertFile;

interface ConvertFileAggregateInterface
{
	public function addConvertFiles(ConvertFileContainerInterface $container);
	public function getConvertFiles();
}
