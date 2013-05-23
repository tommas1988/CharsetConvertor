<?php
interface ConvertorInterface
{
	public function convert(ConvertFile $convertFile);
	public function getName();
}