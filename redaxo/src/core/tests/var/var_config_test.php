<?php

class rex_var_config_test extends rex_var_base_test
{
    public function setUp()
    {
        rex::setConfig('myCoreConfig', 'myCoreConfigValue');
        rex_addon::get('tests')->setConfig('myPackageConfig', 'myPackageConfigValue');
    }

    public function tearDown()
    {
        rex::removeConfig('myCoreConfig');
        rex_addon::get('tests')->removeConfig('tests');
    }

    public function configReplaceProvider()
    {
        return [
            ['REX_CONFIG[key=myCoreConfig]', 'myCoreConfigValue'],
            ['REX_CONFIG[namespace=tests key=myPackageConfig]', 'myPackageConfigValue'],
        ];
    }

    /**
     * @dataProvider configReplaceProvider
     */
    public function testConfigReplace($content, $expectedOutput)
    {
        $this->assertParseOutputEquals($expectedOutput, $content);
    }
}
