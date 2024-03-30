<?php

namespace Redaxo\Core\Tests\Structure;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Structure\Article;
use Redaxo\Core\Structure\Category;
use ReflectionClass;

/** @internal */
final class CategoryTest extends TestCase
{
    protected function setUp(): void
    {
        // generate classVars and add test column
        Category::getClassVars();
        $class = new ReflectionClass(Category::class);
        /** @psalm-suppress MixedArgument */
        $class->setStaticPropertyValue('classVars', array_merge(
            $class->getStaticPropertyValue('classVars'),
            ['cat_foo'],
        ));
    }

    protected function tearDown(): void
    {
        // reset static properties
        $class = new ReflectionClass(Article::class);
        $class->setStaticPropertyValue('classVars', null);

        Category::clearInstancePool();
    }

    public function testHasValue(): void
    {
        $instance = $this->createCategoryWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $instance->cat_foo = 'teststring'; // @phpstan-ignore-line

        self::assertTrue($instance->hasValue('foo'));
        self::assertTrue($instance->hasValue('cat_foo'));

        self::assertFalse($instance->hasValue('bar'));
        self::assertFalse($instance->hasValue('cat_bar'));
    }

    public function testGetValue(): void
    {
        $instance = $this->createCategoryWithoutConstructor();

        /** @psalm-suppress UndefinedPropertyAssignment */
        $instance->cat_foo = 'teststring'; // @phpstan-ignore-line

        self::assertEquals('teststring', $instance->getValue('foo'));
        self::assertEquals('teststring', $instance->getValue('cat_foo'));

        self::assertNull($instance->getValue('bar'));
        self::assertNull($instance->getValue('cat_bar'));
    }

    #[DataProvider('dataGetClosestValue')]
    public function testGetClosestValue(string|int|null $expectedValue, Category $category): void
    {
        self::assertSame($expectedValue, $category->getClosestValue('cat_foo'));
    }

    /** @return iterable<int, array{(int|string|null), Category}> */
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
    public function testIsOnlineIncludingParents(bool $expected, Category $category): void
    {
        self::assertSame($expected, $category->isOnlineIncludingParents());
    }

    /** @return iterable<int, array{bool, Category}> */
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
    public function testGetClosest(?Category $expected, Category $category, callable $callback): void
    {
        self::assertSame($expected, $category->getClosest($callback));
    }

    /** @return iterable<int, array{?Category, Category, callable}> */
    public static function dataGetClosest(): iterable
    {
        $callback = static function (Category $category) {
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

        $callback = static function (Category $category) {
            return $category->getValue('cat_foo') > 3;
        };

        [$lev1, $_, $lev3] = self::createCategories(['cat_foo' => 4], [], ['cat_foo' => 2]);
        yield [$lev1, $lev3, $callback];
    }

    private function createCategoryWithoutConstructor(): Category
    {
        return (new ReflectionClass(Category::class))->newInstanceWithoutConstructor();
    }

    /** @return array{Category, Category, Category} */
    private static function createCategories(array $lev1Params, array $lev2Params, array $lev3Params): array
    {
        $lev1 = self::createCategory(null, $lev1Params);
        $lev2 = self::createCategory($lev1, $lev2Params);
        $lev3 = self::createCategory($lev2, $lev3Params);

        return [$lev1, $lev2, $lev3];
    }

    private static function createCategory(?Category $parent, array $params): Category
    {
        return new class($parent, $params) extends Category {
            public function __construct(
                private ?Category $parent,
                array $params,
            ) {
                foreach ($params as $key => $value) {
                    $this->$key = $value;
                }
            }

            public function getParent(): ?Category
            {
                /** @var static|null */
                return $this->parent;
            }
        };
    }
}
