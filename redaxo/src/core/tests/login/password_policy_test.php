<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_password_policy_test extends TestCase
{
    #[DataProvider('provideCheck')]
    public function testCheck(array $options, bool $expected, string $password): void
    {
        $policy = new rex_password_policy($options);

        $result = $policy->check($password);

        if ($expected) {
            static::assertTrue($result);
        } else {
            static::assertIsString($result);
        }
    }

    /** @return iterable<int, array{array<string, array{min?: int, max?: int}>, bool, string}> */
    public static function provideCheck(): iterable
    {
        yield [[], true, 'foo'];

        $options = ['length' => ['min' => 3]];

        yield [$options, false, '1a'];
        yield [$options, true, '1ab'];
        yield [$options, true, '1abcdefghijklmopqrstuvxxyz234567890'];

        $options = [
            'length' => ['min' => 8, 'max' => 15],
            'digit' => ['max' => 3],
            'lowercase' => ['min' => 2],
            'uppercase' => ['min' => 1],
            'letter' => ['min' => 3],
            'symbol' => ['min' => 1, 'max' => 2],
        ];

        yield [$options, false, 'abcdefghi'];
        yield [$options, false, 'abC!12'];
        yield [$options, false, 'abcdef1234!%AB'];
        yield [$options, true, 'abcdef123!%AB'];
        yield [$options, true, 'AB7=E8#fg9'];
        yield [$options, false, 'AB7=E8#fg9?'];
        yield [$options, false, 'abcdef123!%ABuegrouwouewhifggreigeioger'];
    }

    public function testGetRule(): void
    {
        $getRule = new ReflectionMethod(rex_password_policy::class, 'getRule');

        $policy = new rex_password_policy(['length' => ['min' => 5, 'max' => 25]]);
        $rule = $getRule->invoke($policy);

        static::assertStringContainsString('5', $rule);
        static::assertStringContainsString('25', $rule);

        $policy = new rex_password_policy(['length' => ['min' => 0, 'max' => 25]]);
        $rule = $getRule->invoke($policy);

        static::assertStringNotContainsString('0', $rule);
        static::assertStringContainsString('25', $rule);

        $policy = new rex_password_policy(['length' => ['min' => 0]]);
        $rule = $getRule->invoke($policy);

        static::assertSame('', $rule);
    }

    /**
     * @param array<string, string> $expected
     * @param array<string, array{min?: int, max?: int}> $options
     */
    #[DataProvider('provideGetHtmlAttributes')]
    public function testGetHtmlAttributes(array $expected, array $options): void
    {
        $policy = new rex_password_policy($options);

        static::assertSame($expected, $policy->getHtmlAttributes());
    }

    /** @return iterable<int, array{array<string, string>, array<string, array{min?: int, max?: int}>}> */
    public static function provideGetHtmlAttributes(): iterable
    {
        yield [
            ['passwordrules' => 'allowed: upper, lower, digit, special'],
            [],
        ];

        yield [
            [
                'minlength' => '5',
                'passwordrules' => 'required: digit; allowed: upper, lower, digit, special',
            ],
            [
                'length' => ['min' => 5],
                'digit' => ['min' => 2],
            ],
        ];

        yield [
            [
                'minlength' => '8',
                'maxlength' => '1000',
                'passwordrules' => 'required: lower; required: digit; allowed: upper, lower, digit',
            ],
            [
                'length' => ['min' => 8, 'max' => 1000],
                'uppercase' => ['min' => 0, 'max' => 5],
                'lowercase' => ['min' => 1],
                'digit' => ['min' => 2],
                'symbol' => ['max' => 0],
            ],
        ];
    }
}
