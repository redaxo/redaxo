<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_validator_test extends TestCase
{
    public function testNotEmpty()
    {
        $validator = rex_validator::factory();
        static::assertFalse($validator->notEmpty(''));
        static::assertTrue($validator->notEmpty('0'));
        static::assertTrue($validator->notEmpty('aaa'));
    }

    public function dataType()
    {
        return [
            ['',      'int', false],
            ['a',     'int', false],
            ['0',     'int', true],
            ['1234',  'int', true],
            ['34.54', 'int', false],
            ['',      'float', false],
            ['a',     'float', false],
            ['0',     'float', true],
            ['1234',  'float', true],
            ['34.54', 'float', true],
        ];
    }

    /**
     * @dataProvider dataType
     */
    public function testType($value, $type, $expected)
    {
        static::assertEquals($expected, rex_validator::factory()->type($value, $type));
    }

    public function testMinLength()
    {
        $validator = rex_validator::factory();
        static::assertFalse($validator->minLength('ab', 3));
        static::assertTrue($validator->minLength('abc', 3));
    }

    public function testMaxLength()
    {
        $validator = rex_validator::factory();
        static::assertFalse($validator->maxLength('abc', 2));
        static::assertTrue($validator->maxLength('ab', 2));
    }

    public function testMin()
    {
        $validator = rex_validator::factory();
        static::assertFalse($validator->min('4', 5));
        static::assertTrue($validator->min('5', 5));
    }

    public function testMax()
    {
        $validator = rex_validator::factory();
        static::assertFalse($validator->max('5', 4));
        static::assertTrue($validator->max('4', 4));
    }

    public function dataUrl()
    {
        return [
            ['', false],
            ['abc', false],
            ['www.example.com', false],
            ['http://localhost', true],
            ['http://www.example.com/path/to/file.php?page=2&x=3', true],
            ['http://www.example.com:8080/', true],
        ];
    }

    /**
     * @dataProvider dataUrl
     */
    public function testUrl($value, $isValid)
    {
        static::assertEquals($isValid, rex_validator::factory()->url($value));
    }

    public function dataEmail()
    {
        return [
            ['', false],
            ['abc', false],
            ['@example.com', false],
            ['info@example.com', true],
            ['abc.def@sub.example.com', true],
        ];
    }

    /**
     * @dataProvider dataEmail
     */
    public function testEmail($value, $isValid)
    {
        static::assertEquals($isValid, rex_validator::factory()->email($value));
    }

    public function testMatch()
    {
        $validator = rex_validator::factory();
        static::assertFalse($validator->match('aa', '/^.$/'));
        static::assertTrue($validator->match('a', '/^.$/'));
    }

    public function testNotMatch()
    {
        $validator = rex_validator::factory();
        static::assertTrue($validator->notMatch('aa', '/^.$/'));
        static::assertFalse($validator->notMatch('a', '/^.$/'));
    }

    public function testValues()
    {
        $validator = rex_validator::factory();
        static::assertFalse($validator->values('abc', ['def', 'ghi']));
        static::assertTrue($validator->values('ghi', ['def', 'ghi']));
    }

    public function testCustom()
    {
        $validator = rex_validator::factory();

        $callback = function ($v) use (&$value, &$isCalled) {
            $isCalled = true;
            $this->assertEquals($value, $v);
            return 'abc' === $value;
        };

        $isCalled = false;
        $value = 'abc';
        static::assertTrue($validator->custom($value, $callback));
        static::assertTrue($isCalled, 'Custom callback is called');

        $isCalled = false;
        $value = 'def';
        static::assertFalse($validator->custom($value, $callback));
        static::assertTrue($isCalled, 'Custom callback is called');
    }

    public function testIsValid()
    {
        $validator = rex_validator::factory();

        static::assertTrue($validator->isValid(''));
        static::assertNull($validator->getMessage());

        $validator->add('notEmpty', 'not-empty');
        $validator->add('minLength', 'min-length', 3);

        static::assertFalse($validator->isValid(''));
        static::assertEquals('not-empty', $validator->getMessage());

        static::assertFalse($validator->isValid('ab'));
        static::assertEquals('min-length', $validator->getMessage());

        static::assertTrue($validator->isValid('abc'));
        static::assertNull($validator->getMessage());
    }
}
