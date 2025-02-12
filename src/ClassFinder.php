<?php

namespace DeJoDev\Fabriek;

use CallbackFilterIterator;
use Closure;
use Countable;
use DeJoDev\Fabriek\Iterators\FilterClassesIterator;
use DeJoDev\Fabriek\Iterators\FilterWithInterfacesIterator;
use DeJoDev\Fabriek\Iterators\FilterWithParentsIterator;
use DeJoDev\Fabriek\Iterators\FilterWithTraitsIterator;
use DeJoDev\Fabriek\Iterators\ReturnClassNamesIterator;
use DeJoDev\Fabriek\Iterators\ReturnInstancesIterator;
use Iterator;
use IteratorAggregate;
use Symfony\Component\Finder\Finder;

final class ClassFinder implements Countable, IteratorAggregate
{
    private Finder $finder;

    private array $directories = [];

    private array $matchPatterns = [];

    private array $noMatchPatterns = [];

    private bool $withEnums = false;

    private array $withParents = [];

    private array $withTraits = [];

    private array $withInterfaces = [];

    private ?Closure $filterCallback = null;

    private ?Closure $instances = null;

    private bool $reflect = false;

    public function __construct()
    {
        $this->finder = (new Finder)->files()->name('*.php');
    }

    /**
     * Create a new ClassFinder instance
     */
    public static function create(): ClassFinder
    {
        return new ClassFinder;
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
    public function in(string $directory, string $namespace): ClassFinder
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
    public function match(array|string $patterns): ClassFinder
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
    public function noMatch(array|string $patterns): ClassFinder
    {
        $this->noMatchPatterns = array_merge($this->noMatchPatterns, (array) $patterns);

        return $this;
    }

    /**
     * Configures whether enums should be included in the search results or not.
     *
     * @param  bool  $accept  Boolean value indicating whether enums are accepted. Defaults to true.
     */
    public function withEnums(bool $accept = true): ClassFinder
    {
        $this->withEnums = $accept;

        return $this;
    }

    /**
     * Only accept classes that extend of one of given classes.
     *
     * @param  array|string  $classes  One or more classes to include.
     */
    public function withParents(array|string $classes): ClassFinder
    {
        $this->withParents = array_merge($this->withParents, (array) $classes);

        return $this;
    }

    /**
     * Only accept classes that contain of one of given traits.
     */
    public function withTraits(array|string $traits): ClassFinder
    {
        $this->withTraits = array_merge($this->withTraits, (array) $traits);

        return $this;
    }

    /**
     * Only accept classes that implement of one of given interfaces.
     *
     * @return $this
     */
    public function withInterfaces(array|string $interfaces): ClassFinder
    {
        $this->withInterfaces = array_merge($this->withInterfaces, (array) $interfaces);

        return $this;
    }

    /**
     * Filter the search results using a callback function.
     *
     * @param  callable  $callback  A callable with two parameters:
     *                              - $reflectionClass -> A ReflectionClass object for the class
     *                              - $className -> string with the fully qualified class name
     *                              function(ReflectionClass $reflectionClass, string $className)
     */
    public function filter(callable $callback): ClassFinder
    {
        $this->filterCallback = $callback(...);

        return $this;
    }

    /**
     * Return instances instead of classnames as values
     */
    public function instances(?callable $factoryMethod = null): ClassFinder
    {
        $this->instances = is_null($factoryMethod) ? fn ($className) => new $className : $factoryMethod(...);

        return $this;
    }

    /**
     * Return ReflectionClass objects instead of classnames as values
     */
    public function reflect(): ClassFinder
    {
        $this->reflect = true;

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

        if ($this->filterCallback) {
            $iterator = new CallbackFilterIterator($iterator, $this->filterCallback);
        }

        if ($this->instances) {
            $iterator = new ReturnInstancesIterator($iterator, $this->instances);
        } elseif (! $this->reflect) {
            $iterator = new ReturnClassNamesIterator($iterator);
        }

        return $iterator;
    }

    /**
     * Check if any results were found.
     */
    public function hasResults(): bool
    {
        foreach ($this->getIterator() as $ignored) {
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
     *
     * @param  bool  $preserve_keys  [optional] Whether to use the iterator element keys as index.
     *                               </p>
     */
    public function toArray(bool $preserve_keys = false): array
    {
        return iterator_to_array($this->getIterator(), $preserve_keys);
    }

    /**
     * Executes a given action on each object
     * retrieved from the iterator.
     *
     * @param  callable  $action  A callable with two parameters:
     *                            - $object -> An instance of the class or a ReflectionClass
     *                            - $className -> string with the fully qualified class name
     *                            function(object $object, string $className)
     */
    public function do(callable $action): void
    {
        foreach ($this->getIterator() as $className => $object) {
            $action($object, $className);
        }
    }
}
