<?php

use PHPUnit\Framework\TestCase;

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
        $commandTester = new rex_console_command_tester(new rex_command_config_get());
        $configValue = $commandTester->execute([
            'config-key' => $key,
        ]);
        static::assertEquals($expectedValue, $configValue);
    }

    public function dataKeyFound() {
        return [
            [0, 'setup'],
            [null, 'session.cookie.backend.lifetime'],
            ['root', 'db.1.login'],
        ];
    }

    public function testKeyNotFound() {
        $commandTester = new rex_console_command_tester(new rex_command_config_get());
        $commandTester->execute([
            'config-key' => 'foo.bar'
        ]);
        $this->assertEquals(1, $commandTester->getStatusCode());
    }
}
