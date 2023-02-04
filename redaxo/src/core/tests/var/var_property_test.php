<?php

class rex_var_property_test extends rex_var_base_test
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
    public function propertyReplaceProvider(): array
    {
        return [
            ['REX_PROPERTY[key=myCoreProperty]', 'myCorePropertyValue'],
            ['REX_PROPERTY[namespace=project key=myPackageProperty]', 'myPackagePropertyValue'],
        ];
    }

    /**
     * @dataProvider propertyReplaceProvider
     */
    public function testPropertyReplace($content, $expectedOutput): void
    {
        $this->assertParseOutputEquals($expectedOutput, $content);
    }
}
