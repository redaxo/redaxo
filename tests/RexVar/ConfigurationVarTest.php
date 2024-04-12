<?php

use PHPUnit\Framework\Attributes\DataProvider;
use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Core;
use Redaxo\Core\Tests\RexVar\RexVarTestBase;

/**
 * @internal
 */
final class ConfigurationVarTest extends RexVarTestBase
{
    protected function setUp(): void
    {
        Core::setConfig('myCoreConfig', 'myCoreConfigValue');
        Addon::get('project')->setConfig('myPackageConfig', 'myPackageConfigValue');
    }

    protected function tearDown(): void
    {
        Core::removeConfig('myCoreConfig');
        Addon::get('project')->removeConfig('tests');
    }

    /** @return list<array{string, string}> */
    public static function configReplaceProvider(): array
    {
        return [
            ['REX_CONFIG[key=myCoreConfig]', 'myCoreConfigValue'],
            ['REX_CONFIG[namespace=project key=myPackageConfig]', 'myPackageConfigValue'],
        ];
    }

    #[DataProvider('configReplaceProvider')]
    public function testConfigReplace(string $content, string $expectedOutput): void
    {
        $this->assertParseOutputEquals($expectedOutput, $content);
    }
}
