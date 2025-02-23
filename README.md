# Fabriek
Experimental Package for auto discovering classes and building factories for them.
It provides a fluent interface for searching, filtering and instantiating classes.

This package was inspired by the [Symfony Finder](https://symfony.com/doc/current/components/finder.html) package 
but the dependency was too heavy for my use case and removed.

Usage
```php
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
```

(c) 2025 Wouter de Jong
