<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
class rex_command_config_get_test extends TestCase
{
    /**
     * @dataProvider dataKeyFound
     */
    public function testKeyFound($expectedValue, $key)
    {
        $commandTester = new CommandTester(new rex_command_config_get());
        $commandTester->execute([
            'config-key' => $key,
        ]);
        static::assertEquals($expectedValue, $commandTester->getDisplay());
        static::assertEquals(0, $commandTester->getStatusCode());
    }

    public function dataKeyFound()
    {
        return [
            ["false\n", 'setup'],
            ["\"root\"\n", 'db.1.login'],
        ];
    }

    public function testKeyNotFound()
    {
        $commandTester = new CommandTester(new rex_command_config_get());
        $commandTester->execute([
            'config-key' => 'foo.bar',
        ]);
        static::assertEquals(1, $commandTester->getStatusCode());
    }
}
