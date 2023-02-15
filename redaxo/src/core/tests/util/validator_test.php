<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_validator_test extends TestCase
{
    public function testNotEmpty(): void
    {
        $validator = rex_validator::factory();
        static::assertFalse($validator->notEmpty(''));
        static::assertTrue($validator->notEmpty('0'));
        static::assertTrue($validator->notEmpty('aaa'));
    }

    /** @return list<array{string, string, bool}> */
    public static function dataType(): array
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

    #[DataProvider('dataType')]
    public function testType(string $value, string $type, bool $expected): void
    {
        static::assertEquals($expected, rex_validator::factory()->type($value, $type));
    }

    public function testMinLength(): void
    {
        $validator = rex_validator::factory();
        static::assertFalse($validator->minLength('ab', 3));
        static::assertTrue($validator->minLength('abc', 3));
    }

    public function testMaxLength(): void
    {
        $validator = rex_validator::factory();
        static::assertFalse($validator->maxLength('abc', 2));
        static::assertTrue($validator->maxLength('ab', 2));
    }

    public function testMin(): void
    {
        $validator = rex_validator::factory();
        static::assertFalse($validator->min('4', 5));
        static::assertTrue($validator->min('5', 5));
    }

    public function testMax(): void
    {
        $validator = rex_validator::factory();
        static::assertFalse($validator->max('5', 4));
        static::assertTrue($validator->max('4', 4));
    }

    /** @return list<array{string, bool}> */
    public static function dataUrl(): array
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

    #[DataProvider('dataUrl')]
    public function testUrl(string $value, bool $isValid): void
    {
        static::assertEquals($isValid, rex_validator::factory()->url($value));
    }

    /** @return list<array{string, bool}> */
    public static function dataEmail(): array
    {
        return [
            ['', false],
            ['abc', false],
            ['@example.com', false],
            ['info@example.com', true],
            ['abc.def@sub.example.com', true],
        ];
    }

    #[DataProvider('dataEmail')]
    public function testEmail(string $value, bool $isValid): void
    {
        static::assertEquals($isValid, rex_validator::factory()->email($value));
    }

    public function testMatch(): void
    {
        $validator = rex_validator::factory();
        static::assertFalse($validator->match('aa', '/^.$/'));
        static::assertTrue($validator->match('a', '/^.$/'));
    }

    public function testNotMatch(): void
    {
        $validator = rex_validator::factory();
        static::assertTrue($validator->notMatch('aa', '/^.$/'));
        static::assertFalse($validator->notMatch('a', '/^.$/'));
    }

    public function testValues(): void
    {
        $validator = rex_validator::factory();
        static::assertFalse($validator->values('abc', ['def', 'ghi']));
        static::assertTrue($validator->values('ghi', ['def', 'ghi']));
    }

    #[DataProvider('dataCustom')]
    public function testCustom(bool $expected, string $value): void
    {
        $validator = rex_validator::factory();

        $isCalled = false;

        $callback = function ($v) use ($value, &$isCalled) {
            $isCalled = true;
            $this->assertEquals($value, $v);
            return 'abc' === $value;
        };

        static::assertSame($expected, $validator->custom($value, $callback));
        static::assertTrue($isCalled, 'Custom callback is called');
    }

    /** @return iterable<int, array{bool, string}> */
    public static function dataCustom(): iterable
    {
        return [
            [true, 'abc'],
            [false, 'def'],
        ];
    }

    public function testIsValid(): void
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

    public function testGetRules(): void
    {
        $validator = rex_validator::factory();
        // mix of add/addRule should be returned in getRules()
        $validator->add(rex_validation_rule::NOT_EMPTY, 'not-empty');
        $validator->addRule(new rex_validation_rule('minLength', 'min-length', 3));

        static::assertCount(2, $validator->getRules());
        static::assertIsArray($validator->getRules());
        static::assertArrayHasKey(0, $validator->getRules());
        static::assertArrayHasKey(1, $validator->getRules());
    }
}
