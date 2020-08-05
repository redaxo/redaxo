<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_category_test extends TestCase
{
    protected function setUp(): void
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

    protected function tearDown(): void
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

        /** @psalm-suppress UndefinedPropertyAssignment */
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

        /** @psalm-suppress UndefinedPropertyAssignment */
        $instance->cat_foo = 'teststring';

        static::assertEquals('teststring', $instance->getValue('foo'));
        static::assertEquals('teststring', $instance->getValue('cat_foo'));

        static::assertNull($instance->getValue('bar'));
        static::assertNull($instance->getValue('cat_bar'));
    }

    /**
     * @dataProvider dataGetClosestValue
     */
    public function testGetClosestValue($expectedValue, rex_category $category): void
    {
        static::assertSame($expectedValue, $category->getClosestValue('cat_foo'));
    }

    public function dataGetClosestValue(): iterable
    {
        [$lev1, $lev2, $lev3] = $this->createCategories([], [], []);
        yield [null, $lev1];
        yield [null, $lev3];

        [$lev1, $lev2, $lev3] = $this->createCategories([], [], ['cat_foo' => 'foo']);
        yield ['foo', $lev3];

        [$lev1, $lev2, $lev3] = $this->createCategories([], ['cat_foo' => 'bar'], ['cat_foo' => 'foo']);
        yield ['foo', $lev3];

        [$lev1, $lev2, $lev3] = $this->createCategories([], ['cat_foo' => 'bar'], []);
        yield ['bar', $lev3];

        [$lev1, $lev2, $lev3] = $this->createCategories(['cat_foo' => 'baz'], ['cat_foo' => 'bar'], []);
        yield ['bar', $lev3];

        [$lev1, $lev2, $lev3] = $this->createCategories(['cat_foo' => 'baz'], [], []);
        yield ['baz', $lev1];
        yield ['baz', $lev3];

        [$lev1, $lev2, $lev3] = $this->createCategories([], ['cat_foo' => 0], []);
        yield [0, $lev3];
    }

    /**
     * @dataProvider dataIsOnlineIncludingParents
     */
    public function testIsOnlineIncludingParents(bool $expected, rex_category $category): void
    {
        static::assertSame($expected, $category->isOnlineIncludingParents());
    }

    public function dataIsOnlineIncludingParents(): iterable
    {
        [$lev1, $lev2, $lev3] = $this->createCategories(['status' => 0], ['status' => 0], ['status' => 0]);
        yield [false, $lev1];
        yield [false, $lev3];

        [$lev1, $lev2, $lev3] = $this->createCategories(['status' => 1], ['status' => 1], ['status' => 1]);
        yield [true, $lev1];
        yield [true, $lev3];

        [$lev1, $lev2, $lev3] = $this->createCategories(['status' => 1], ['status' => 1], ['status' => 0]);
        yield [false, $lev3];

        [$lev1, $lev2, $lev3] = $this->createCategories(['status' => 0], ['status' => 1], ['status' => 1]);
        yield [false, $lev3];

        [$lev1, $lev2, $lev3] = $this->createCategories(['status' => 1], ['status' => 2], ['status' => 1]);
        yield [false, $lev3];
    }

    /**
     * @dataProvider dataGetClosest
     */
    public function testGetClosest(?rex_category $expected, rex_category $category, callable $callback): void
    {
        static::assertSame($expected, $category->getClosest($callback));
    }

    public function dataGetClosest(): iterable
    {
        $callback = static function (rex_category $category) {
            return 1 === $category->getValue('status');
        };

        [$lev1, $lev2, $lev3] = $this->createCategories(['status' => 0], ['status' => 0], ['status' => 0]);
        yield [null, $lev1, $callback];
        yield [null, $lev3, $callback];

        [$lev1, $lev2, $lev3] = $this->createCategories(['status' => 1], ['status' => 1], ['status' => 1]);
        yield [$lev1, $lev1, $callback];
        yield [$lev3, $lev3, $callback];

        [$lev1, $lev2, $lev3] = $this->createCategories(['status' => 1], ['status' => 1], ['status' => 0]);
        yield [$lev2, $lev3, $callback];

        [$lev1, $lev2, $lev3] = $this->createCategories(['status' => 1], ['status' => 0], ['status' => 0]);
        yield [$lev1, $lev3, $callback];

        $callback = static function (rex_category $category) {
            return $category->getValue('cat_foo') > 3;
        };

        [$lev1, $lev2, $lev3] = $this->createCategories(['cat_foo' => 4], [], ['cat_foo' => 2]);
        yield [$lev1, $lev3, $callback];
    }

    private function createCategories(array $lev1Params, array $lev2Params, array $lev3Params): array
    {
        $lev1 = $this->createCategory(null, $lev1Params);
        $lev2 = $this->createCategory($lev1, $lev2Params);
        $lev3 = $this->createCategory($lev2, $lev3Params);

        return [$lev1, $lev2, $lev3];
    }

    private function createCategory(?rex_category $parent, array $params): rex_category
    {
        return new class($parent, $params) extends rex_category {
            private $parent;

            public function __construct(?rex_category $parent, array $params)
            {
                $this->parent = $parent;

                foreach ($params as $key => $value) {
                    $this->$key = $value;
                }
            }

            public function getParent()
            {
                return $this->parent;
            }
        };
    }
}
