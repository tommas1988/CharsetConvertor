<?php
namespace Tcc;

interface ConvertFileContainerInterface
{
	public function addFile($convertFile, $inputCharset, $outputCharset);
	public function addFiles(ConvertFileAggregateInterface $aggregate);
	public function getConvertFiles();
	public function clearConvertFiles();
	public function setConvertExtensions(array $extensions);
	public function getConvertExtensions();
}
