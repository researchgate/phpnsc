<?php
/*
 * This file is part of phpnsc.
 *
 * (c) Bastian Hofmann <bastian.hofmann@researchgate.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
require_once __DIR__.'/../vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';

$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'      => __DIR__.'/../vendor',
    'rg' => __DIR__
));
$loader->register();