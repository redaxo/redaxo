<?php

use PHPUnit\Framework\Attributes\DataProvider;
use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Core;
use Redaxo\Core\Tests\RexVar\RexVarTestBase;

final class rex_var_property_test extends RexVarTestBase
{
    protected function setUp(): void
    {
        Core::setProperty('myCoreProperty', 'myCorePropertyValue');
        Addon::get('project')->setProperty('myPackageProperty', 'myPackagePropertyValue');
    }

    protected function tearDown(): void
    {
        Core::removeProperty('myCoreProperty');
        Addon::get('project')->removeProperty('tests');
    }

    /** @return list<array{string, string}> */
    public static function propertyReplaceProvider(): array
    {
        return [
            ['REX_PROPERTY[key=myCoreProperty]', 'myCorePropertyValue'],
            ['REX_PROPERTY[namespace=project key=myPackageProperty]', 'myPackagePropertyValue'],
        ];
    }

    #[DataProvider('propertyReplaceProvider')]
    public function testPropertyReplace(string $content, string $expectedOutput): void
    {
        $this->assertParseOutputEquals($expectedOutput, $content);
    }
}
