<?php

namespace DeJoDev\Fabriek;

use Countable;
use DeJoDev\Fabriek\Iterators\FilterClassesIterator;
use DeJoDev\Fabriek\Iterators\FilterWithInterfacesIterator;
use DeJoDev\Fabriek\Iterators\FilterWithParentsIterator;
use DeJoDev\Fabriek\Iterators\FilterWithTraitsIterator;
use DeJoDev\Fabriek\Iterators\ReturnInstancesIterator;
use Iterator;
use IteratorAggregate;
use Symfony\Component\Finder\Finder;

class ClassFinder implements Countable, IteratorAggregate
{
    private Finder $finder;

    private array $directories = [];

    private array $matchPatterns = [];

    private array $noMatchPatterns = [];

    private bool $withEnums = false;

    private array $withParents = [];

    private array $withTraits = [];

    private array $withInterfaces = [];

    private bool $insances = false;

    public function __construct()
    {
        $this->finder = (new Finder)->files()->name('*.php');
    }

    /**
     * Create a new ClassFinder instance
     */
    public static function create(): static
    {
        return new static;
    }

    /**
     * This method returns the underlying Symfony Finder object.
     *
     * Warning! Using the finder directly can cause unforeseen results so use with caution.
     *
     * @see https://symfony.com/doc/current/components/finder.html
     */
    public function getFinder(): Finder
    {
        return $this->finder;
    }

    /**
     * Specify the directory and corresponding namespace to search within.
     * Can be called multiple times to search multiple directories.
     *
     * @param  string  $directory  The directory path.
     * @param  string  $namespace  The namespace associated with the directory.
     */
    public function in(string $directory, string $namespace): static
    {
        $directory = str_ends_with($directory, '/') ? $directory : $directory.'/';
        $namespace = str_ends_with($namespace, '\\') ? $namespace : $namespace.'\\';
        $this->directories[$directory] = $namespace;
        $this->finder->in($directory);

        return $this;
    }

    /**
     * Adds one or more search patterns for filtering based on classname and namespace.
     * At least one pattern must match for a class to be accepted.
     * Given pattern may be a regular expressions otherwise partial string matching is used.
     *
     * @param  array|string  $patterns  One or more patterns to be matched.
     */
    public function match(array|string $patterns): static
    {
        $this->matchPatterns = array_merge($this->matchPatterns, (array) $patterns);

        return $this;
    }

    /**
     * Adds one or more search patterns for filtering based on classname and namespace.
     * No given patterns may match for a class to be accepted.
     * Given pattern may be a regular expressions otherwise partial string matching is used.
     *
     * @return $this
     */
    public function noMatch(array|string $patterns): static
    {
        $this->noMatchPatterns = array_merge($this->noMatchPatterns, (array) $patterns);

        return $this;
    }

    /**
     * Configures whether enums should be included in the search results or not.
     *
     * @param  bool  $accept  Boolean value indicating whether enums are accepted. Defaults to true.
     */
    public function withEnums(bool $accept = true): static
    {
        $this->withEnums = $accept;

        return $this;
    }

    /**
     * Only accept classes that extend of one of given classes.
     *
     * @param  array|string  $classes  One or more classes to include.
     */
    public function withParents(array|string $classes): static
    {
        $this->withParents = array_merge($this->withParents, (array) $classes);

        return $this;
    }

    /**
     * Only accept classes that contain of one of given traits.
     *
     * @return $this
     */
    public function withTraits(array|string $traits): static
    {
        $this->withTraits = array_merge($this->withTraits, (array) $traits);

        return $this;
    }

    /**
     * Only accept classes that implement of one of given interfaces.
     *
     * @return $this
     */
    public function withInterfaces(array|string $interfaces): static
    {
        $this->withInterfaces = array_merge($this->withInterfaces, (array) $interfaces);

        return $this;
    }

    public function instances(bool $instantiate = true): static
    {
        $this->insances = $instantiate;

        return $this;
    }

    /**
     * Returns an iterator for the current ClassFinder configuration
     */
    public function getIterator(): Iterator
    {
        $iterator = new FilterClassesIterator(
            $this->finder->getIterator(),
            $this->directories,
            $this->matchPatterns,
            $this->noMatchPatterns,
            $this->withEnums,
        );

        if ($this->withParents) {
            $iterator = new FilterWithParentsIterator($iterator, $this->withParents);
        }

        if ($this->withTraits) {
            $iterator = new FilterWithTraitsIterator($iterator, $this->withTraits);
        }

        if ($this->withInterfaces) {
            $iterator = new FilterWithInterfacesIterator($iterator, $this->withInterfaces);
        }

        if ($this->insances) {
            $iterator = new ReturnInstancesIterator($iterator);
        }

        return $iterator;
    }

    /**
     * Check if any results were found.
     */
    public function hasResults(): bool
    {
        foreach ($this->getIterator() as $_) {
            return true;
        }

        return false;
    }

    /**
     * Counts all the results collected by the iterators.
     */
    public function count(): int
    {
        return iterator_count($this->getIterator());
    }

    /**
     * Converts the object to an array
     */
    public function toArray(): array
    {
        return iterator_to_array($this->getIterator());
    }
}
