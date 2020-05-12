<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_password_policy_test extends TestCase
{
    /**
     * @dataProvider provideCheck
     */
    public function testCheck(array $options, $expected, $password)
    {
        $policy = new rex_password_policy($options);

        $result = $policy->check($password);

        if ($expected) {
            static::assertTrue($result);
        } else {
            static::assertIsString($result);
        }
    }

    public function provideCheck()
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
}
