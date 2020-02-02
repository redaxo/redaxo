<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_article_test extends TestCase
{
    protected function setUp()
    {
        // generate classVars and add test column
        rex_article::getClassVars();
        $class = new ReflectionClass(rex_article::class);
        $classVarsProperty = $class->getProperty('classVars');
        $classVarsProperty->setAccessible(true);
        $classVarsProperty->setValue(
            array_merge(
                $classVarsProperty->getValue(),
                ['art_foo']
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

        rex_article::clearInstancePool();
    }

    public function testHasValue()
    {
        $class = new ReflectionClass(rex_article::class);
        /** @var rex_article $instance */
        $instance = $class->newInstanceWithoutConstructor();

        $instance->art_foo = 'teststring';

        static::assertTrue($instance->hasValue('foo'));
        static::assertTrue($instance->hasValue('art_foo'));

        static::assertFalse($instance->hasValue('bar'));
        static::assertFalse($instance->hasValue('art_bar'));
    }

    public function testGetValue()
    {
        $class = new ReflectionClass(rex_article::class);
        /** @var rex_article $instance */
        $instance = $class->newInstanceWithoutConstructor();

        $instance->art_foo = 'teststring';

        static::assertEquals('teststring', $instance->getValue('foo'));
        static::assertEquals('teststring', $instance->getValue('art_foo'));

        static::assertNull($instance->getValue('bar'));
        static::assertNull($instance->getValue('art_bar'));
    }
}
