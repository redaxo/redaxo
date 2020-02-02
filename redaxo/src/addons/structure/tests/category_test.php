<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_category_test extends TestCase
{
    protected function setUp()
    {
        // generate classVars and add test column
        rex_category::getClassVars();
        $class = new ReflectionClass(rex_category::class);
        $classVarsProperty = $class->getProperty('classVars');
        $classVarsProperty->setAccessible(true);
        $classVarsProperty->setValue(
            array_merge(
                $classVarsProperty->getValue(),
                ['cat_foo']
            )
        );
    }

    protected function tearDown()
    {
        // reset static properties
        $class = new ReflectionClass(rex_article::class);
        $classVarsProperty = $class->getProperty('classVars');
        $classVarsProperty->setAccessible(true);
        $classVarsProperty->setValue(null);

        rex_category::clearInstancePool();
    }

    public function testHasValue()
    {
        $class = new ReflectionClass(rex_category::class);
        /** @var rex_category $instance */
        $instance = $class->newInstanceWithoutConstructor();
        $instance->cat_foo = 'teststring';

        static::assertTrue($instance->hasValue('foo'));
        static::assertTrue($instance->hasValue('cat_foo'));

        static::assertFalse($instance->hasValue('bar'));
        static::assertFalse($instance->hasValue('cat_bar'));
    }

    public function testGetValue()
    {
        $class = new ReflectionClass(rex_category::class);
        /** @var rex_category $instance */
        $instance = $class->newInstanceWithoutConstructor();
        $instance->cat_foo = 'teststring';

        static::assertEquals('teststring', $instance->getValue('foo'));
        static::assertEquals('teststring', $instance->getValue('cat_foo'));

        static::assertNull($instance->getValue('bar'));
        static::assertNull($instance->getValue('cat_bar'));
    }
}
