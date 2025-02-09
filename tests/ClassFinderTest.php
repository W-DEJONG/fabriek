<?php

use DeJoDev\Fabriek\ClassFinder;
use DeJoDev\Fabriek\Fixtures\AnotherSubFolder\ClassWithInterface;
use DeJoDev\Fabriek\Fixtures\AnotherSubFolder\ClassWithTrait;
use DeJoDev\Fabriek\Fixtures\MyClass;
use DeJoDev\Fabriek\Fixtures\MyEnum;
use DeJoDev\Fabriek\Fixtures\MyInterface;
use DeJoDev\Fabriek\Fixtures\MyTrait;
use DeJoDev\Fabriek\Fixtures\SubFolder\AnotherClass;
use Symfony\Component\Finder\Finder;

const NAMESPACE_PREFIX = 'DeJoDev\\Fabriek\\Fixtures';

it('Can instantiate a ClassFinder object', function () {
    $finder = ClassFinder::create();

    expect($finder)
        ->toBeInstanceOf(ClassFinder::class)
        ->and($finder->getFinder())
        ->toBeInstanceOf(Finder::class);
});

it('Can scan a model folder for classes', function () {
    $finder = ClassFinder::create()
        ->in(__DIR__.'/fixtures', NAMESPACE_PREFIX);

    expect(iterator_to_array($finder))
        ->toHaveCount(4)
        ->toHaveKey(MyClass::class)
        ->not()->toHaveKey(MyEnum::class);

    $finder = ClassFinder::create()
        ->in(__DIR__.'/fixtures', NAMESPACE_PREFIX)
        ->withEnums();

    expect(iterator_to_array($finder))
        ->toHaveCount(5)
        ->toHaveKey(MyClass::class)
        ->toHaveKey(MyEnum::class);
});

it('Can filter classes with regex', function () {
    $finder = ClassFinder::create()
        ->in(__DIR__.'/fixtures', NAMESPACE_PREFIX)
        ->match('/.*ClassWith.*/');

    expect(iterator_to_array($finder))
        ->toHaveCount(2)
        ->toHaveKey(ClassWithInterface::class)
        ->not()->toHaveKey(MyClass::class);

    $finder = ClassFinder::create()
        ->in(__DIR__.'/fixtures', NAMESPACE_PREFIX)
        ->noMatch('Another');
    $classes = iterator_to_array($finder);
    expect($classes)
        ->toHaveCount(1)
        ->toHaveKey(MyClass::class)
        ->not()->toHaveKey(AnotherClass::class);
});

it('Can check for results', function () {
    $finder = ClassFinder::create()
        ->in(__DIR__.'/fixtures', NAMESPACE_PREFIX);

    expect($finder->hasResults())
        ->toBeTrue();

    $finder = ClassFinder::create()
        ->in(__DIR__.'/fixtures', NAMESPACE_PREFIX)
        ->match('find_nothing');

    expect($finder->hasResults())
        ->toBeFalse();
});

it('Can count results', function () {
    $finder = ClassFinder::create()
        ->in(__DIR__.'/fixtures', NAMESPACE_PREFIX);

    expect($finder->count())
        ->toBe(4);
});

it('Can filter subclasses', function () {
    $finder = ClassFinder::create()
        ->in(__DIR__.'/fixtures', NAMESPACE_PREFIX)
        ->withParents(MyClass::class);

    expect(iterator_to_array($finder))
        ->toHaveCount(1)
        ->toHaveKey(AnotherClass::class);
});

it('Can filter traits', function () {
    $finder = ClassFinder::create()
        ->in(__DIR__.'/fixtures', NAMESPACE_PREFIX)
        ->withTraits(MyTrait::class);

    expect(iterator_to_array($finder))
        ->toHaveCount(1)
        ->toHaveKey(ClassWithTrait::class);
});

it('Can filter interfaces', function () {
    $finder = ClassFinder::create()
        ->in(__DIR__.'/fixtures', NAMESPACE_PREFIX)
        ->withInterfaces(MyInterface::class);

    expect(iterator_to_array($finder))
        ->toHaveCount(1)
        ->toHaveKey(ClassWithInterface::class);
});

it('Returns instances of the class', function () {
    $finder = ClassFinder::create()
        ->in(__DIR__.'/fixtures', NAMESPACE_PREFIX)
        ->match(MyClass::class)
        ->instances();

    expect(iterator_to_array($finder))
        ->toHaveCount(1)
        ->toHaveKey(MyClass::class)
        ->toContainOnlyInstancesOf(MyClass::class);
});

it('Can return an array of classes', function () {
    $finder = ClassFinder::create()
        ->in(__DIR__.'/fixtures', NAMESPACE_PREFIX)
        ->toArray();
    expect($finder)
        ->toBeArray()
        ->toHaveKey(MyClass::class);
});
