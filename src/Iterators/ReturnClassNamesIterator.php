<?php

namespace DeJoDev\Fabriek\Iterators;

use Iterator;

class ReturnClassNamesIterator implements Iterator
{
    public function __construct(private readonly Iterator $iterator) {}

    public function current(): string
    {
        return $this->iterator->current()->getName();
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
