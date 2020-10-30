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
        $method = new ReflectionMethod(rex_package_manager::class, 'matchVersionConstraints');
        $method->setAccessible(true);

        static::assertSame($expected, $method->invoke(null, $version, $constraints));
    }

    public function testInstallReturnsFalse()
    {
        $addonMock = $this->getMockBuilder(rex_addon::class)
            ->setConstructorArgs(['Fake'])
            ->getMock();

        $packageManager = rex_addon_manager::factory($addonMock);

        $result = $packageManager->install();
        static::assertFalse($result);
    }

    public function testInstallMessageIsNotEmpty()
    {
        $packageName = 'fake';
        $propertyFakeArray = [
            'package' => $packageName,
        ];
        $addonMock = $this->getMockBuilder(rex_addon::class)
            ->setConstructorArgs([$packageName])
            ->getMock();
        $addonMock->method('getPath')->willReturnCallback(
            static function ($name) {
                if ('assets' === $name) {
                    return  '';
                }
                return __DIR__.'/fake_addon';
            }
        );
        $addonMock->method('getPackageId')->willReturn($packageName);
        $addonMock->method('getVersion')->willReturn('0.1');
        $addonMock->method('getProperty')->willReturnCallback(static function ($parameter, $default = null) use ($propertyFakeArray) {
            if (!isset($propertyFakeArray[$parameter])) {
                return $default;
            }
            return $propertyFakeArray[$parameter];
        });
        $addonMock->method('isInstalled')->willReturn(true);
        $packageManager = rex_addon_manager::factory($addonMock);

        $result = $packageManager->install(false);

        static::assertTrue($result);
    }

    public function testCanDisplayInstallMessage()
    {
        $packageName = 'fake';
        $propertyFakeArray = [
            'package' => $packageName,
            'installmsg' => 'Fake installed',
        ];
        $addonMock = $this->getMockBuilder(rex_addon::class)
            ->setConstructorArgs([$packageName])
            ->getMock();
        $addonMock->method('getPath')->willReturnCallback(
            static function ($name) {
                if ('assets' === $name) {
                    return  '';
                }
                return __DIR__.'/fake_addon';
            }
        );
        $addonMock->method('getPackageId')->willReturn($packageName);
        $addonMock->method('getVersion')->willReturn('0.1');
        $addonMock->method('getProperty')->willReturnCallback(static function ($parameter, $default = null) use ($propertyFakeArray) {
            if (!isset($propertyFakeArray[$parameter])) {
                return $default;
            }
            return $propertyFakeArray[$parameter];
        });
        $addonMock->method('isInstalled')->willReturn(true);
        $packageManager = rex_addon_manager::factory($addonMock);

        $result = $packageManager->install(false);
        $expectedMessage = rex_i18n::msg('addon_installed', '').'<p>Fake installed</p>';
        static::assertTrue($result);
        static::assertSame($expectedMessage, $packageManager->getMessage());
    }

    public function dataMatchVersionConstraints()
    {
        return [
            [true, '1.0.4', '1.0.4'],
            [false, '1.0.4', '1.0.5'],
            [true, '1.0.4', '*'],
            [true, '2.5.3', '2.*'],
            [false, '1.1', '2.*'],
            [false, '13.0', '12.*'],
            [false, '1.1', '1.2.*'],
            [false, '1.3', '1.2.*'],
            [true, '1.2.1', '1.2.*'],
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
