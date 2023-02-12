<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_type_test extends TestCase
{
    /** @return list<array{mixed, string|callable(mixed):mixed|list<array{string, string, mixed}>, mixed}> */
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
            [$arrayVar, $arrayCasts, $arrayExpected],
            [
                ['k' => $arrayVar],
                [['k', $arrayCasts]],
                ['k' => $arrayExpected],
            ],
        ];
    }

    /** @param string|callable(mixed):mixed|list<array{string, string, mixed}> $vartype */
    #[DataProvider('castProvider')]
    public function testCast(mixed $var, string|callable|array $vartype, mixed $expectedResult): void
    {
        static::assertSame($expectedResult, rex_type::cast($var, $vartype));
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
            [[1]],
            [new stdClass()],
        ];
    }

    #[DataProvider('castWrongVartypeProvider')]
    public function testCastWrongVartype(mixed $vartype): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @psalm-suppress MixedArgument */
        rex_type::cast(1, $vartype);
    }
}
