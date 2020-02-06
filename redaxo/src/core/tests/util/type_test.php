<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_type_test extends TestCase
{
    public function castProvider()
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

    /**
     * @dataProvider castProvider
     */
    public function testCast($var, $vartype, $expectedResult)
    {
        static::assertSame($expectedResult, rex_type::cast($var, $vartype));
    }

    public function castWrongVartypeProvider()
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

    /**
     * @dataProvider castWrongVartypeProvider
     */
    public function testCastWrongVartype($vartype)
    {
        $this->expectException(\InvalidArgumentException::class);

        rex_type::cast(1, $vartype);
    }
}
