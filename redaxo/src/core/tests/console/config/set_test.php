<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
class rex_command_config_set_test extends TestCase
{
    private string $initialConfig;

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

    #[DataProvider('dataSetBoolean')]
    public function testSetBoolean(bool $expectedValue, string $value): void
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

    /** @return list<array{bool, string}> */
    public static function dataSetBoolean(): array
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
