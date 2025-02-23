<?php

namespace DeJoDev\Fabriek\Iterators;

use FilesystemIterator;
use FilterIterator;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionEnum;
use SplFileInfo;

class FilterClassesIterator extends FilterIterator
{
    public function __construct(
        private readonly string $directory,
        private readonly string $namespace,
        private readonly array $exclude,
        private readonly array $matchPatterns,
        private readonly array $noMatchPatterns,
        private readonly bool $withEnums,
    ) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator($this->directory, flags: FilesystemIterator::SKIP_DOTS),
                fn (SplFileInfo $current, $key, $iterator) => $this->acceptDirectory($current->getPathname())
            )
        );
        parent::__construct($iterator);
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
    }

    public function accept(): bool
    {
        $file = parent::current();

        if ($file->isDir() || ! preg_match('/^.+\.php$/i', $file->getFilename())) {
            return false;
        }

        $className = $this->getClassName($file);

        return class_exists($className) &&
            ($this->withEnums || ! enum_exists($className)) &&
            $this->matches($className);
    }

    private function acceptDirectory(string $path): bool
    {
        return ! in_array($path, $this->exclude);
    }

    private function toRegex(string $str): string
    {
        return str_starts_with($str, '/') ? $str : '/'.preg_quote($str, '/').'/i';
    }

    private function matches(string $string): bool
    {
        foreach ($this->noMatchPatterns as $pattern) {
            if (preg_match($this->toRegex($pattern), $string)) {
                return false;
            }
        }

        if ($this->matchPatterns) {
            foreach ($this->matchPatterns as $pattern) {
                if (preg_match($this->toRegex($pattern), $string)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    private function getClassName(SplFileInfo $file): string
    {
        $dir = substr($file->getPathname(), -(strlen($file->getPathName()) - strlen($this->directory)));

        return $this->namespace.str_replace(['/', '.php'], ['\\', ''], $dir);
    }
}
