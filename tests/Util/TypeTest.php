<?php

namespace Redaxo\Core\Tests\Util;

use BackedEnum;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Util\Type;
use stdClass;

/** @internal */
final class TypeTest extends TestCase
{
    /** @return list<array{mixed, string|callable(mixed):mixed|list<int|string|BackedEnum>|list<array{0: string, 1: string|callable(mixed):mixed|list<mixed>, 2?: mixed}>, mixed}> */
    public static function castProvider(): array
    {
        $callback = static function ($var) {
            return $var . 'b';
        };

        $arrayVar = ['key1' => 1, 'key2' => '2', 'key4' => 'a', 'key5' => 0];
        $arrayCasts = [
            ['key1', 'string', 0],
            ['key2', 'int', 1],
            ['key3', 'string', -1],
            ['key4', $callback],
        ];
        $arrayExpected = ['key1' => '1', 'key2' => 2, 'key3' => -1, 'key4' => 'ab'];

        return [
            ['a', '', 'a'],
            [1, 'string', '1'],
            [1, 'bool', true],
            [[], 'bool', false],
            [['foo'], 'bool', true],
            [['foo'], 'string', ''],
            ['', 'array', []],
            [1, 'array', [1]],
            [[1, '2'], 'array[int]', [1, 2]],
            ['a', $callback, 'ab'],
            ['bar', ['foo', 'bar', 'baz'], 'bar'],
            ['qux', ['foo', 'bar', 'baz'], 'foo'],
            ['qux', ['foo', 'bar', 'baz'], 'foo'],
            ['2', [1, 2, 4], 2],
            [3, [1, 2, 4], 1],
            ['bar', TypeTestEnumString::cases(), TypeTestEnumString::Bar],
            ['2', TypeTestEnumInteger::cases(), TypeTestEnumInteger::Bar],
            [$arrayVar, $arrayCasts, $arrayExpected],
            [
                ['k' => $arrayVar],
                [['k', $arrayCasts]],
                ['k' => $arrayExpected],
            ],
            [
                ['key1' => 'bar', 'key3' => 'qux'],
                [
                    ['key1', ['foo', 'bar', 'baz']],
                    ['key2', ['foo', 'bar', 'baz']],
                    ['key3', ['foo', 'bar'], 'baz'],
                ],
                ['key1' => 'bar', 'key2' => 'foo', 'key3' => 'baz'],
            ],
        ];
    }

    /** @param string|callable(mixed):mixed|list<int|string|BackedEnum>|list<array{0: string, 1: string|callable(mixed):mixed|list<mixed>, 2?: mixed}> $vartype */
    #[DataProvider('castProvider')]
    public function testCast(mixed $var, string|callable|array $vartype, mixed $expectedResult): void
    {
        self::assertSame($expectedResult, Type::cast($var, $vartype));
    }

    /** @return list<array{mixed}> */
    public static function castWrongVartypeProvider(): array
    {
        return [
            ['wrongVartype'],
            [1],
            [false],
            ['array['],
            ['array[abc]'],
            [[]],
            [[1, ['foo', 'bool']]],
            [new stdClass()],
        ];
    }

    #[DataProvider('castWrongVartypeProvider')]
    public function testCastWrongVartype(mixed $vartype): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @psalm-suppress MixedArgument */
        Type::cast(1, $vartype);
    }
}

enum TypeTestEnumString: string
{
    case Foo = 'foo';
    case Bar = 'bar';
}

enum TypeTestEnumInteger: int
{
    case Foo = 1;
    case Bar = 2;
}
