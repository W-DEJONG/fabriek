<?php

namespace DeJoDev\Fabriek\Iterators;

use FilterIterator;

class FilterWithTraitsIterator extends FilterIterator
{
    public function __construct(\Iterator $iterator, private readonly array $traits)
    {
        parent::__construct($iterator);
    }

    /**
     * {@inheritDoc}
     */
    public function accept(): bool
    {
        foreach ($this->traits as $trait) {
            if (in_array($trait, $this->current()->getTraitNames(), true)) {
                return true;
            }
        }

        return false;
    }
}
