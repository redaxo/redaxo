<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_article_test extends TestCase
{

    protected function tearDown() {
        // reset static properties
        $class = new ReflectionClass(rex_article::class);
        $classVarsProperty = $class->getProperty('classVars');
        $classVarsProperty->setAccessible(true);
        $classVarsProperty->setValue(null);

        rex_article::clearInstancePool();
    }

    public function testHasValue()
    {
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

        /** @var rex_article $instance */
        $instance = $class->newInstanceWithoutConstructor();

        $instance->art_foo = 'teststring';

        $this->assertTrue($instance->hasValue('foo'));
        $this->assertTrue($instance->hasValue('art_foo'));

        $this->assertFalse($instance->hasValue('bar'));
        $this->assertFalse($instance->hasValue('art_bar'));
    }

    public function testGetValue()
    {
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

        /** @var rex_article $instance */
        $instance = $class->newInstanceWithoutConstructor();

        $instance->art_foo = 'teststring';

        $this->assertEquals('teststring', $instance->getValue('foo'));
        $this->assertEquals('teststring', $instance->getValue('art_foo'));

        $this->assertNull($instance->getValue('bar'));
        $this->assertNull($instance->getValue('art_bar'));
    }
}
