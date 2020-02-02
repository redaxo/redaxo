<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_package_manager_test extends TestCase
{
    /**
     * @dataProvider dataMatchVersionConstraints
     */
    public function testMatchVersionConstraints($expected, $version, $constraints)
    {
        $method = new ReflectionMethod('rex_package_manager', 'matchVersionConstraints');
        $method->setAccessible(true);

        static::assertSame($expected, $method->invoke(null, $version, $constraints));
    }

    public function dataMatchVersionConstraints()
    {
        return [
            [true, '1.0.4', '1.0.4'],
            [false, '1.0.4', '1.0.5'],
            [true, '1.0.4', '*'],
            [false, '1.0.4', '>=1.1'],
            [false, '1.1.0-beta1', '>=1.1'],
            [true, '1.1.0', '>=1.1'],
            [true, '2.0', '>=1.1'],
            [false, '3.0', '>=1.1, <3.0'],
            [false, '1.0', '^1.0.3'],
            [true, '1.0.3', '^1.0.3'],
            [true, '1.9', '^1.0.3'],
            [false, '2.0', '^1.0.3'],
            [false, '2.0-beta1', '^1.0.3'],
            [true, '1.0.3', '~1.0.3'],
            [true, '1.0.5', '~1.0.3'],
            [false, '1.1', '~1.0.3'],
            [false, '1.1-beta1', '~1.0.3'],
        ];
    }
}
