<?php

namespace DeJoDev\Fabriek\Iterators;

use FilterIterator;
use Iterator;

class FilterWithParentsIterator extends FilterIterator
{
    public function __construct(Iterator $iterator, private readonly array $parents)
    {
        parent::__construct($iterator);
    }

    /**
     * {@inheritDoc}
     */
    public function accept(): bool
    {
        foreach ($this->parents as $parent) {
            if ($this->current()->isSubclassOf($parent)) {
                return true;
            }
        }

        return false;
    }
}
