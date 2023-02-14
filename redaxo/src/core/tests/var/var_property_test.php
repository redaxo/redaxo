<?php

use PHPUnit\Framework\Attributes\DataProvider;

require_once __DIR__.'/var_test_base.php';

class rex_var_property_test extends rex_var_test_base
{
    protected function setUp(): void
    {
        rex::setProperty('myCoreProperty', 'myCorePropertyValue');
        rex_addon::get('project')->setProperty('myPackageProperty', 'myPackagePropertyValue');
    }

    protected function tearDown(): void
    {
        rex::removeProperty('myCoreProperty');
        rex_addon::get('project')->removeProperty('tests');
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
