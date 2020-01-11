<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_category_test extends TestCase
{
    protected function tearDown() {
        // reset static properties
        $class = new ReflectionClass(rex_article::class);
        $classVarsProperty = $class->getProperty('classVars');
        $classVarsProperty->setAccessible(true);
        $classVarsProperty->setValue(null);

        rex_category::clearInstancePool();
    }

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


    public function testHasValue()
    {
        $class = new ReflectionClass(rex_category::class);
        /** @var rex_category $instance */
        $instance = $class->newInstanceWithoutConstructor();
        $instance->cat_foo = 'teststring';

        $this->assertTrue($instance->hasValue('foo'));
        $this->assertTrue($instance->hasValue('cat_foo'));

        $this->assertFalse($instance->hasValue('bar'));
        $this->assertFalse($instance->hasValue('cat_bar'));
    }

    public function testGetValue()
    {
        $class = new ReflectionClass(rex_category::class);
        /** @var rex_category $instance */
        $instance = $class->newInstanceWithoutConstructor();
        $instance->cat_foo = 'teststring';

        $this->assertEquals('teststring', $instance->getValue('foo'));
        $this->assertEquals('teststring', $instance->getValue('cat_foo'));

        $this->assertNull($instance->getValue('bar'));
        $this->assertNull($instance->getValue('cat_bar'));
    }
}
