<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
class rex_command_config_set_test extends TestCase
{
    protected function tearDown()
    {
        $configPath = rex_path::coreData('config.yml');
        $config = rex_file::getConfig($configPath);
        unset($config['test']);
        rex_file::putConfig($configPath, $config);
    }

    /**
     * @dataProvider dataSetBoolean
     */
    public function testSetBoolean($expectedValue, $value)
    {
        $commandTester = new CommandTester(new rex_command_config_set());
        $commandTester->execute([
            '--type' => 'bool',
            'config-key' => 'test',
            'value' => $value,
        ]);
        $config = rex_file::getConfig(rex_path::coreData('config.yml'));
        static::assertIsBool($config['test']);
        static::assertEquals($expectedValue, $config['test']);
        static::assertEquals(0, $commandTester->getStatusCode());
    }

    public function dataSetBoolean()
    {
        return [
            [true, '1'],
            [false, '0'],
            [true, 'true'],
            [false, 'false'],
            [true, 'on'],
            [false, 'off'],
        ];
    }
}
