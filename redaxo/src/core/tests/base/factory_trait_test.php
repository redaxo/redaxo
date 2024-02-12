<?php

use PHPUnit\Framework\TestCase;

class rex_test_factory
{
    use rex_factory_trait;

    /** @return class-string<self> */
    public static function factory(): string
    {
        // just return the class which was determined using rex_factory_base.
        // this doesn't make sense in real use-cases but eases testing
        return self::getFactoryClass();
    }

    public function doSomething(): string
    {
        return 'base';
    }

    public static function staticCall(): string
    {
        if ($factoryClass = static::getExplicitFactoryClass()) {
            return $factoryClass::staticCall();
        }
        return 'static-base';
    }
}
class rex_alternative_test_factory extends rex_test_factory
{
    public function doSomething(): string
    {
        return 'overridden';
    }

    public static function staticCall(): string
    {
        return 'static-overridden';
    }
}

/**
 * @internal
 */
class rex_factory_trait_test extends TestCase
{
    public function testFactoryCreation(): void
    {
        self::assertFalse(rex_test_factory::hasFactoryClass(), 'initially no factory class is set');
        self::assertEquals(rex_test_factory::class, rex_test_factory::getFactoryClass(), 'original factory class will be returned');
        $clazz = rex_test_factory::factory();
        self::assertEquals(rex_test_factory::class, $clazz, 'factory class defaults to the original impl');
        $obj = new $clazz();
        self::assertEquals('base', $obj->doSomething(), 'call method of the original impl');
        self::assertEquals('static-base', rex_test_factory::staticCall(), 'static method of original impl');

        rex_test_factory::setFactoryClass(rex_alternative_test_factory::class);

        self::assertTrue(rex_test_factory::hasFactoryClass(), 'factory class was set');
        self::assertEquals(rex_alternative_test_factory::class, rex_test_factory::getFactoryClass(), 'factory class will be returned');
        $clazz = rex_test_factory::factory();
        self::assertEquals(rex_alternative_test_factory::class, $clazz, 'alternative factory class will be used');
        $obj = new $clazz();
        self::assertEquals('overridden', $obj->doSomething(), 'call method of the alternative impl');
        self::assertEquals('static-overridden', rex_test_factory::staticCall(), 'static method of alternative impl');
    }
}
