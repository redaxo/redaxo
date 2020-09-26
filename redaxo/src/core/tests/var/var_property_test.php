<?php

class rex_var_property_test extends rex_var_base_test
{
    public function setUp(): void
    {
        rex::setProperty('myCoreProperty', 'myCorePropertyValue');
        rex_addon::get('project')->setProperty('myPackageProperty', 'myPackagePropertyValue');
    }

    public function tearDown(): void
    {
        rex::removeProperty('myCoreProperty');
        rex_addon::get('project')->removeProperty('tests');
    }

    public function propertyReplaceProvider()
    {
        return [
            ['REX_PROPERTY[key=myCoreProperty]', 'myCorePropertyValue'],
            ['REX_PROPERTY[namespace=project key=myPackageProperty]', 'myPackagePropertyValue'],
        ];
    }

    /**
     * @dataProvider propertyReplaceProvider
     */
    public function testPropertyReplace($content, $expectedOutput)
    {
        $this->assertParseOutputEquals($expectedOutput, $content);
    }
}
