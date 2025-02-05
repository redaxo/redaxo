<?php

namespace Redaxo\Core\Tests\Util;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Exception\InvalidArgumentException;
use Redaxo\Core\Util\Type;
use stdClass;
use Throwable;
use TypeError;

/**
 * @internal
 * @psalm-import-type TCastType from Type
 */
final class TypeTest extends TestCase
{
    /** @return list<array{mixed, TCastType, mixed}> */
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
            ['a', null, 'a'],
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

    /** @param TCastType $type */
    #[DataProvider('castProvider')]
    public function testCast(mixed $var, string|callable|array|null $type, mixed $expectedResult): void
    {
        self::assertSame($expectedResult, Type::cast($var, $type));
    }

    /** @return list<array{0: mixed, 1?: class-string<Throwable>}> */
    public static function castWrongVartypeProvider(): array
    {
        return [
            ['wrongVartype'],
            [1],
            ['array['],
            ['array[abc]'],
            [[]],
            [[1, ['foo', 'bool']]],
            [new stdClass(), TypeError::class],
        ];
    }

    /** @param class-string<Throwable> $exceptionClass */
    #[DataProvider('castWrongVartypeProvider')]
    public function testCastWrongVartype(mixed $type, string $exceptionClass = InvalidArgumentException::class): void
    {
        $this->expectException($exceptionClass);

        /** @psalm-suppress MixedArgument */
        Type::cast(1, $type);
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
