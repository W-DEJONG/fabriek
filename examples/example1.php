<?php

use DeJoDev\Fabriek\ClassFinder;

require __DIR__.'/../vendor/autoload.php';

$finder = ClassFinder::create()
    ->in(__DIR__.'/jobs', '\\App\\Jobs')
    ->match('/^.*Job$/i')
    ->withInterfaces('\\App\\Contracts\\JobInterface')
    ->instances();
foreach ($finder as $job) {
    $job->handle();
}
