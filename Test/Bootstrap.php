<?php
require 'Zend/Loader/StandardAutoloader.php';

$loader = new Zend\Loader\StandardAutoloader(
    array(
        Zend\Loader\StandardAutoloader::LOAD_NS => array(
            'Tcc' => __DIR__ . '/../',
        ),
    ));

$loader->register();
