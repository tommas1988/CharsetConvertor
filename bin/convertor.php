<?php
use ScriptFrontend\Runner;

require '../autoload.php';

$args = (isset($argv)) ? $argv : $_SERVER['argv'];
$args = array_shift($args);

Runner::init($args)->run();
