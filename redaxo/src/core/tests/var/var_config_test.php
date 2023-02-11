<?php

class rex_var_config_test extends rex_var_base_test
{
    protected function setUp(): void
    {
        rex::setConfig('myCoreConfig', 'myCoreConfigValue');
        rex_addon::get('project')->setConfig('myPackageConfig', 'myPackageConfigValue');
    }

    protected function tearDown(): void
    {
        rex::removeConfig('myCoreConfig');
        rex_addon::get('project')->removeConfig('tests');
    }

    /** @return list<array{string, string}> */
    public function configReplaceProvider(): array
    {
        return [
            ['REX_CONFIG[key=myCoreConfig]', 'myCoreConfigValue'],
            ['REX_CONFIG[namespace=project key=myPackageConfig]', 'myPackageConfigValue'],
        ];
    }

    /**
     * @dataProvider configReplaceProvider
     */
    public function testConfigReplace($content, $expectedOutput): void
    {
        $this->assertParseOutputEquals($expectedOutput, $content);
    }
}
