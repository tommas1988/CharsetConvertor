<?php
namespace Tcc\ScriptFrontend\Printer;

use Tcc\ScriptFrontend\Runner;

interface PrinterInterface
{
    public function setAppRunner(Runner $runer);
    public function getAppRunner();
    public function print($state);
}