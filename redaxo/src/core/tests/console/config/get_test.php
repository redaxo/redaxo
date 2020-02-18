<?php

/**
 * @internal
 */
class rex_command_config_get_test extends \PHPUnit\Framework\TestCase
{
    public function testGetConfig()
    {
        $commandTester = new \Symfony\Component\Console\Tester\CommandTester(new rex_command_config_get());
        $configValue = $commandTester->execute([
            'config-key' => 'setup',
        ]);
        static::assertTrue($configValue);
    }
}
