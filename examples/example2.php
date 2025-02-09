<?php

use DeJoDev\Fabriek\ClassFinder;

$autoloader = require __DIR__.'/../vendor/autoload.php';

$namespace = 'DeJoDev\\Fabriek\\Fixtures\\';
$psr4Mappings = $autoloader->getPrefixesPsr4();
$dirs = $psr4Mappings[$namespace];

$finder = ClassFinder::create();
foreach ($dirs as $dir) {
    $finder->in($dir, $namespace);
}

echo $finder->count()." classes found\n";
foreach ($finder->instances() as $class => $reflection) {
    echo $class."\n";
}
