<?php

require_once __DIR__.'/../vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';

$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'      => __DIR__.'/../vendor',
    'rg' => __DIR__
));
$loader->register();