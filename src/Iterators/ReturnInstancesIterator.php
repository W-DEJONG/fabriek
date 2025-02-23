<?php

namespace DeJoDev\Fabriek\Iterators;

use Closure;
use Iterator;

class ReturnInstancesIterator implements Iterator
{
    public function __construct(private readonly Iterator $iterator, private readonly Closure $factoryMethod) {}

    public function current(): object
    {
        $classname = $this->iterator->current()->getName();
        $factory = $this->factoryMethod;

        return $factory($classname);
    }

    public function next(): void
    {
        $this->iterator->next();
    }

    public function key(): mixed
    {
        return $this->iterator->key();
    }

    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    public function rewind(): void
    {
        $this->iterator->rewind();
    }
}
