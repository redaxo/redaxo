<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
class rex_command_config_set_test extends TestCase
{
    private $initialConfig;

    protected function setUp(): void
    {
        $configPath = rex_path::coreData('config.yml');
        $this->initialConfig = file_get_contents($configPath);
    }

    protected function tearDown(): void
    {
        $configPath = rex_path::coreData('config.yml');
        file_put_contents($configPath, $this->initialConfig);
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
        static::assertArrayHasKey('test', $config);
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
