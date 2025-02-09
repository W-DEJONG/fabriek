# Fabriek
Experimental Package for auto discovering classes and building factories for them.

This package uses the [Symfony Finder](https://symfony.com/doc/current/components/finder.html) package to locate 
classes and provides a fluent interface for selecting and filtering.

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

2025 Wouter de Jong
