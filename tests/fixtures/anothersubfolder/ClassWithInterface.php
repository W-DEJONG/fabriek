<?php

namespace DeJoDev\Fabriek\Fixtures\AnotherSubFolder;

use DeJoDev\Fabriek\Fixtures\MyInterface;

class ClassWithInterface implements MyInterface
{
    private bool $fooCalled = false;

    public function foo(): void
    {
        $this->fooCalled = true;
    }

    public function fooCalled(): bool
    {
        return $this->fooCalled;
    }
}
