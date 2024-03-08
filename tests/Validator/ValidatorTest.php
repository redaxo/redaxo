<?php

namespace Redaxo\Core\Tests\Validator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Validator\ValidationRule;
use Redaxo\Core\Validator\Validator;

/**
 * @internal
 */
class ValidatorTest extends TestCase
{
    public function testNotEmpty(): void
    {
        $validator = Validator::factory();
        self::assertFalse($validator->notEmpty(''));
        self::assertTrue($validator->notEmpty('0'));
        self::assertTrue($validator->notEmpty('aaa'));
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
        self::assertEquals($expected, Validator::factory()->type($value, $type));
    }

    public function testMinLength(): void
    {
        $validator = Validator::factory();
        self::assertFalse($validator->minLength('ab', 3));
        self::assertTrue($validator->minLength('abc', 3));
    }

    public function testMaxLength(): void
    {
        $validator = Validator::factory();
        self::assertFalse($validator->maxLength('abc', 2));
        self::assertTrue($validator->maxLength('ab', 2));
    }

    public function testMin(): void
    {
        $validator = Validator::factory();
        self::assertFalse($validator->min('4', 5));
        self::assertTrue($validator->min('5', 5));
    }

    public function testMax(): void
    {
        $validator = Validator::factory();
        self::assertFalse($validator->max('5', 4));
        self::assertTrue($validator->max('4', 4));
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
        self::assertEquals($isValid, Validator::factory()->url($value));
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
        self::assertEquals($isValid, Validator::factory()->email($value));
    }

    public function testMatch(): void
    {
        $validator = Validator::factory();
        self::assertFalse($validator->match('aa', '/^.$/'));
        self::assertTrue($validator->match('a', '/^.$/'));
    }

    public function testNotMatch(): void
    {
        $validator = Validator::factory();
        self::assertTrue($validator->notMatch('aa', '/^.$/'));
        self::assertFalse($validator->notMatch('a', '/^.$/'));
    }

    public function testValues(): void
    {
        $validator = Validator::factory();
        self::assertFalse($validator->values('abc', ['def', 'ghi']));
        self::assertTrue($validator->values('ghi', ['def', 'ghi']));
    }

    #[DataProvider('dataCustom')]
    public function testCustom(bool $expected, string $value): void
    {
        $validator = Validator::factory();

        $isCalled = false;

        $callback = function ($v) use ($value, &$isCalled) {
            $isCalled = true;
            $this->assertEquals($value, $v);
            return 'abc' === $value;
        };

        self::assertSame($expected, $validator->custom($value, $callback));
        self::assertTrue($isCalled, 'Custom callback is called');
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
        $validator = Validator::factory();

        self::assertTrue($validator->isValid(''));
        self::assertNull($validator->getMessage());

        $validator->add('notEmpty', 'not-empty');
        $validator->add('minLength', 'min-length', 3);

        self::assertFalse($validator->isValid(''));
        self::assertEquals('not-empty', $validator->getMessage());

        self::assertFalse($validator->isValid('ab'));
        self::assertEquals('min-length', $validator->getMessage());

        self::assertTrue($validator->isValid('abc'));
        self::assertNull($validator->getMessage());
    }

    public function testGetRules(): void
    {
        $validator = Validator::factory();
        // mix of add/addRule should be returned in getRules()
        $validator->add(ValidationRule::NOT_EMPTY, 'not-empty');
        $validator->addRule(new ValidationRule('minLength', 'min-length', 3));

        self::assertCount(2, $validator->getRules());
        self::assertIsArray($validator->getRules());
        self::assertArrayHasKey(0, $validator->getRules());
        self::assertArrayHasKey(1, $validator->getRules());
    }
}
