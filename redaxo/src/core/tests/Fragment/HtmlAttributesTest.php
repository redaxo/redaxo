<?php

namespace Redaxo\Core\Tests\Fragment;

use BackedEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Fragment\Component\ButtonSize;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @internal
 */
final class HtmlAttributesTest extends TestCase
{
    /** @param array<literal-string, null|bool|string|int|BackedEnum|array<string|int, string|bool>> $attributes */
    #[DataProvider('dataConstruct')]
    public function testConstruct(string $expected, array $attributes): void
    {
        $attributes = new HtmlAttributes($attributes);

        static::assertSame($expected, $attributes->toString());
    }


    /** @return list<array{string, array<literal-string, null|bool|string|int|BackedEnum|array<string|int, string|bool>>}> */
    public static function dataConstruct(): array
    {
        return [
            ['', []],
            ['', ['foo' => null, 'bar' => false, 'baz' => []]],
            [
                'title="foo" maxlength="5" disabled size="small"',
                ['title' => 'foo', 'maxlength' => 5, 'disabled' => true, 'size' => ButtonSize::Small],
            ],
            [
                'title="foo" class="cls1 cls3 cls4"',
                ['title' => 'foo', 'value' => null, 'class' => [
                    'cls1',
                    'cls2' => false,
                    'cls3',
                    'cls4' => true,
                ]],
            ],
        ];
    }

    /**
     * @param array<literal-string, null|bool|string|int|BackedEnum|array<string|int, string|bool>> $initial
     * @param array<literal-string, null|bool|string|int|BackedEnum|array<string|int, string|bool>> $with
     */
    #[DataProvider('dataWith')]
    public function testWith(string $expected, array $initial, array $with): void
    {
        $initial = new HtmlAttributes($initial);
        $with = $initial->with($with);

        static::assertNotSame($initial, $with);
        static::assertSame($expected, $with->toString());
    }


    /** @return list<array{string, array<literal-string, null|bool|string|int|BackedEnum|array<string|int, string|bool>>, array<literal-string, null|bool|string|int|BackedEnum|array<string|int, string|bool>>}> */
    public static function dataWith(): array
    {
        return [
            ['', [], []],
            [
                '',
                ['foo' => 'bar', 'disabled' => true],
                ['foo' => null, 'disabled' => false],
            ],
            [
                'foo="bar" disabled',
                ['foo' => null, 'disabled' => false],
                ['foo' => 'bar', 'disabled' => true],
            ],
            [
                'title="foo" value="5"',
                ['value' => 5],
                ['title' => 'foo'],
            ],
            [
                'class="cls1 cls3" value="5"',
                ['value' => 5],
                ['class' => ['cls1', 'cls2' => false, 'cls3' => true]],
            ],
            [
                'class="cls1 cls3 foo bar"',
                ['class' => 'foo cls2 bar'],
                ['class' => ['cls1', 'cls2' => false, 'cls3' => true]],
            ],
            [
                'class="cls1 cls3 foo bar"',
                ['class' => ['cls1' => false, 'cls2', 'foo', 'cls3', 'bar' => true, 'baz' => false]],
                ['class' => ['cls1', 'cls2' => false, 'cls3' => true]],
            ],
            [
                'class="cls1 cls2"',
                ['class' => ['cls1' => false, 'foo', 'bar' => true]],
                ['class' => 'cls1 cls2'],
            ],
            [
                'class="cls1 cls2"',
                ['class' => 'cls1 foo bar'],
                ['class' => 'cls1 cls2'],
            ],
        ];
    }
}
