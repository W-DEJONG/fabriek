<?php

namespace DeJoDev\Fabriek\Iterators;

use ReflectionClass;
use ReflectionEnum;
use Symfony\Component\Finder\Iterator\MultiplePcreFilterIterator;
use Symfony\Component\Finder\SplFileInfo;

class FilterClassesIterator extends MultiplePcreFilterIterator
{
    public function __construct(
        \Iterator $iterator,
        private readonly array $directories,
        array $matchPatterns,
        array $noMatchPatterns,
        private readonly bool $withEnums,
    ) {
        parent::__construct($iterator, $matchPatterns, $noMatchPatterns);
    }

    /**
     * @throws \ReflectionException
     */
    public function current(): ReflectionClass|ReflectionEnum
    {
        $className = $this->getClassName(parent::current());

        return enum_exists($className) ? new ReflectionEnum($className) : new ReflectionClass($className);
    }

    public function key(): string
    {
        return $this->current()->getName();
        //        return $this->getClassName(parent::current());
    }

    public function accept(): bool
    {
        $className = $this->getClassName(parent::current());

        return class_exists($className) &&
            ($this->withEnums || ! enum_exists($className)) &&
            $this->isAccepted($className);
    }

    protected function toRegex(string $str): string
    {
        return $this->isRegex($str) ? $str : '/'.preg_quote($str, '/').'/';
    }

    private function getClassName(SplFileInfo $file): string
    {
        $dir = substr($file->getPathName(), 0, strlen($file->getPathName()) - strlen($file->getRelativePathname()));

        return $this->directories[$dir].str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
    }
}
