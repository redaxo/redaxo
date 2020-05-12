<?php

use PHPUnit\Framework\TestCase;

class rex_test_factory
{
    use rex_factory_trait;

    public static function factory()
    {
        // just return the class which was determined using rex_factory_base.
        // this doesn't make sense in real use-cases but eases testing
        return self::getFactoryClass();
    }

    public function doSomething()
    {
        return 'base';
    }

    public static function staticCall()
    {
        if (static::hasFactoryClass()) {
            return static::callFactoryClass(__FUNCTION__, func_get_args());
        }
        return 'static-base';
    }
}
class rex_alternative_test_factory extends rex_test_factory
{
    public function doSomething()
    {
        return 'overridden';
    }

    public static function staticCall()
    {
        return 'static-overridden';
    }
}

/**
 * @internal
 */
class rex_factory_trait_test extends TestCase
{
    public function testFactoryCreation()
    {
        static::assertFalse(rex_test_factory::hasFactoryClass(), 'initially no factory class is set');
        static::assertEquals('rex_test_factory', rex_test_factory::getFactoryClass(), 'original factory class will be returned');
        $clazz = rex_test_factory::factory();
        static::assertEquals('rex_test_factory', $clazz, 'factory class defaults to the original impl');
        $obj = new $clazz();
        static::assertEquals('base', $obj->doSomething(), 'call method of the original impl');
        static::assertEquals('static-base', rex_test_factory::staticCall(), 'static method of original impl');

        rex_test_factory::setFactoryClass('rex_alternative_test_factory');
        static::assertTrue(rex_test_factory::hasFactoryClass(), 'factory class was set');
        static::assertEquals('rex_alternative_test_factory', rex_test_factory::getFactoryClass(), 'factory class will be returned');
        $clazz = rex_test_factory::factory();
        static::assertEquals('rex_alternative_test_factory', $clazz, 'alternative factory class will be used');
        $obj = new $clazz();
        static::assertEquals('overridden', $obj->doSomething(), 'call method of the alternative impl');
        static::assertEquals('static-overridden', rex_test_factory::staticCall(), 'static method of alternative impl');
    }
}
