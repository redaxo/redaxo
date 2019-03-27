<?php

class rex_validator_test extends PHPUnit_Framework_TestCase
{
    public function testNotEmpty()
    {
        $validator = rex_validator::factory();
        $this->assertFalse($validator->notEmpty(''));
        $this->assertTrue($validator->notEmpty('0'));
        $this->assertTrue($validator->notEmpty('aaa'));
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
        $this->assertEquals($expected, rex_validator::factory()->type($value, $type));
    }

    public function testMinLength()
    {
        $validator = rex_validator::factory();
        $this->assertFalse($validator->minLength('ab', 3));
        $this->assertTrue($validator->minLength('abc', 3));
    }

    public function testMaxLength()
    {
        $validator = rex_validator::factory();
        $this->assertFalse($validator->maxLength('abc', 2));
        $this->assertTrue($validator->maxLength('ab', 2));
    }

    public function testMin()
    {
        $validator = rex_validator::factory();
        $this->assertFalse($validator->min('4', 5));
        $this->assertTrue($validator->min('5', 5));
    }

    public function testMax()
    {
        $validator = rex_validator::factory();
        $this->assertFalse($validator->max('5', 4));
        $this->assertTrue($validator->max('4', 4));
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
        $this->assertEquals($isValid, rex_validator::factory()->url($value));
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
        $this->assertEquals($isValid, rex_validator::factory()->email($value));
    }

    public function testMatch()
    {
        $validator = rex_validator::factory();
        $this->assertFalse($validator->match('aa', '/^.$/'));
        $this->assertTrue($validator->match('a', '/^.$/'));
    }

    public function testNotMatch()
    {
        $validator = rex_validator::factory();
        $this->assertTrue($validator->notMatch('aa', '/^.$/'));
        $this->assertFalse($validator->notMatch('a', '/^.$/'));
    }

    public function testValues()
    {
        $validator = rex_validator::factory();
        $this->assertFalse($validator->values('abc', ['def', 'ghi']));
        $this->assertTrue($validator->values('ghi', ['def', 'ghi']));
    }

    public function testCustom()
    {
        $validator = rex_validator::factory();

        $callback = function ($v) use (&$value, &$isCalled) {
            $isCalled = true;
            $this->assertEquals($value, $v);
            return $value === 'abc';
        };

        $isCalled = false;
        $value = 'abc';
        $this->assertTrue($validator->custom($value, $callback));
        $this->assertTrue($isCalled, 'Custom callback is called');

        $isCalled = false;
        $value = 'def';
        $this->assertFalse($validator->custom($value, $callback));
        $this->assertTrue($isCalled, 'Custom callback is called');
    }

    public function testIsValid()
    {
        $validator = rex_validator::factory();

        $this->assertTrue($validator->isValid(''));
        $this->assertNull($validator->getMessage());

        $validator->add('notEmpty', 'not-empty');
        $validator->add('minLength', 'min-length', 3);

        $this->assertFalse($validator->isValid(''));
        $this->assertEquals('not-empty', $validator->getMessage());

        $this->assertFalse($validator->isValid('ab'));
        $this->assertEquals('min-length', $validator->getMessage());

        $this->assertTrue($validator->isValid('abc'));
        $this->assertNull($validator->getMessage());
    }
}
