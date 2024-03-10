<?php

use PHPUnit\Framework\Attributes\DataProvider;
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
        /** @psalm-suppress MixedArgument */
        $class->setStaticPropertyValue('classVars', array_merge(
            $class->getStaticPropertyValue('classVars'),
            ['cat_foo'],
        ));
    }

    protected function tearDown(): void
    {
        // reset static properties
        $class = new ReflectionClass(rex_article::class);
        $class->setStaticPropertyValue('classVars', null);

        rex_category::clearInstancePool();
    }

    public function testHasValue(): void
    {
        $instance = $this->createCategoryWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $instance->cat_foo = 'teststring';

        self::assertTrue($instance->hasValue('foo'));
        self::assertTrue($instance->hasValue('cat_foo'));

        self::assertFalse($instance->hasValue('bar'));
        self::assertFalse($instance->hasValue('cat_bar'));
    }

    public function testGetValue(): void
    {
        $instance = $this->createCategoryWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $instance->cat_foo = 'teststring';

        self::assertEquals('teststring', $instance->getValue('foo'));
        self::assertEquals('teststring', $instance->getValue('cat_foo'));

        self::assertNull($instance->getValue('bar'));
        self::assertNull($instance->getValue('cat_bar'));
    }

    #[DataProvider('dataGetClosestValue')]
    public function testGetClosestValue(string|int|null $expectedValue, rex_category $category): void
    {
        self::assertSame($expectedValue, $category->getClosestValue('cat_foo'));
    }

    /** @return iterable<int, array{int|string|null, rex_category}> */
    public static function dataGetClosestValue(): iterable
    {
        [$lev1, $_, $lev3] = self::createCategories([], [], []);
        yield [null, $lev1];
        yield [null, $lev3];

        [$_, $_, $lev3] = self::createCategories([], [], ['cat_foo' => 'foo']);
        yield ['foo', $lev3];

        [$_, $_, $lev3] = self::createCategories([], ['cat_foo' => 'bar'], ['cat_foo' => 'foo']);
        yield ['foo', $lev3];

        [$_, $_, $lev3] = self::createCategories([], ['cat_foo' => 'bar'], []);
        yield ['bar', $lev3];

        [$_, $_, $lev3] = self::createCategories(['cat_foo' => 'baz'], ['cat_foo' => 'bar'], []);
        yield ['bar', $lev3];

        [$lev1, $_, $lev3] = self::createCategories(['cat_foo' => 'baz'], [], []);
        yield ['baz', $lev1];
        yield ['baz', $lev3];

        [$_, $_, $lev3] = self::createCategories([], ['cat_foo' => 0], []);
        yield [0, $lev3];
    }

    #[DataProvider('dataIsOnlineIncludingParents')]
    public function testIsOnlineIncludingParents(bool $expected, rex_category $category): void
    {
        self::assertSame($expected, $category->isOnlineIncludingParents());
    }

    /** @return iterable<int, array{bool, rex_category}> */
    public static function dataIsOnlineIncludingParents(): iterable
    {
        [$lev1, $_, $lev3] = self::createCategories(['status' => 0], ['status' => 0], ['status' => 0]);
        yield [false, $lev1];
        yield [false, $lev3];

        [$lev1, $_, $lev3] = self::createCategories(['status' => 1], ['status' => 1], ['status' => 1]);
        yield [true, $lev1];
        yield [true, $lev3];

        [$_, $_, $lev3] = self::createCategories(['status' => 1], ['status' => 1], ['status' => 0]);
        yield [false, $lev3];

        [$_, $_, $lev3] = self::createCategories(['status' => 0], ['status' => 1], ['status' => 1]);
        yield [false, $lev3];

        [$_, $_, $lev3] = self::createCategories(['status' => 1], ['status' => 2], ['status' => 1]);
        yield [false, $lev3];
    }

    #[DataProvider('dataGetClosest')]
    public function testGetClosest(?rex_category $expected, rex_category $category, callable $callback): void
    {
        self::assertSame($expected, $category->getClosest($callback));
    }

    /** @return iterable<int, array{?rex_category, rex_category, callable}> */
    public static function dataGetClosest(): iterable
    {
        $callback = static function (rex_category $category) {
            return 1 === $category->getValue('status');
        };

        [$lev1, $_, $lev3] = self::createCategories(['status' => 0], ['status' => 0], ['status' => 0]);
        yield [null, $lev1, $callback];
        yield [null, $lev3, $callback];

        [$lev1, $_, $lev3] = self::createCategories(['status' => 1], ['status' => 1], ['status' => 1]);
        yield [$lev1, $lev1, $callback];
        yield [$lev3, $lev3, $callback];

        [$_, $lev2, $lev3] = self::createCategories(['status' => 1], ['status' => 1], ['status' => 0]);
        yield [$lev2, $lev3, $callback];

        [$lev1, $_, $lev3] = self::createCategories(['status' => 1], ['status' => 0], ['status' => 0]);
        yield [$lev1, $lev3, $callback];

        $callback = static function (rex_category $category) {
            return $category->getValue('cat_foo') > 3;
        };

        [$lev1, $_, $lev3] = self::createCategories(['cat_foo' => 4], [], ['cat_foo' => 2]);
        yield [$lev1, $lev3, $callback];
    }

    private function createCategoryWithoutConstructor(): rex_category
    {
        return (new ReflectionClass(rex_category::class))->newInstanceWithoutConstructor();
    }

    /** @return array{rex_category, rex_category, rex_category} */
    private static function createCategories(array $lev1Params, array $lev2Params, array $lev3Params): array
    {
        $lev1 = self::createCategory(null, $lev1Params);
        $lev2 = self::createCategory($lev1, $lev2Params);
        $lev3 = self::createCategory($lev2, $lev3Params);

        return [$lev1, $lev2, $lev3];
    }

    private static function createCategory(?rex_category $parent, array $params): rex_category
    {
        return new class($parent, $params) extends rex_category {
            public function __construct(
                private ?rex_category $parent,
                array $params,
            ) {
                foreach ($params as $key => $value) {
                    $this->$key = $value;
                }
            }

            public function getParent()
            {
                /** @var static|null */
                return $this->parent;
            }
        };
    }
}
