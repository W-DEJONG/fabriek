<?php

namespace DeJoDev\Fabriek\Iterators;

use Iterator;

readonly class ReturnInstancesIterator implements Iterator
{
    public function __construct(private Iterator $iterator) {}

    public function current(): object
    {
        $classname = $this->iterator->key();

        return new $classname;
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
