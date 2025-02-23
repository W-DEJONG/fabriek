<?php

namespace DeJoDev\Fabriek\Iterators;

use FilterIterator;
use Iterator;

class FilterWithInterfacesIterator extends FilterIterator
{
    public function __construct(Iterator $iterator, private readonly array $interfaces)
    {
        parent::__construct($iterator);
    }

    /**
     * {@inheritDoc}
     */
    public function accept(): bool
    {
        foreach ($this->interfaces as $interface) {
            if ($this->current()->implementsInterface($interface)) {
                return true;
            }
        }

        return false;
    }
}
