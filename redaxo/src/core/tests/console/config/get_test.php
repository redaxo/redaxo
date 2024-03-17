<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/** @internal */
final class rex_command_config_get_test extends TestCase
{
    #[DataProvider('dataKeyFound')]
    public function testKeyFound(string $expectedValue, string $key): void
    {
        $commandTester = new CommandTester(new rex_command_config_get());
        $commandTester->execute([
            'config-key' => $key,
        ]);
        self::assertEquals($expectedValue, $commandTester->getDisplay(true));
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    /** @return list<array{string, string}> */
    public static function dataKeyFound(): array
    {
        return [
            ["false\n", 'setup'],
            ["\"root\"\n", 'db.1.login'],
        ];
    }

    public function testKeyNotFound(): void
    {
        $commandTester = new CommandTester(new rex_command_config_get());
        $commandTester->execute([
            'config-key' => 'foo.bar',
        ]);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testPackageKeyFound(): void
    {
        $commandTester = new CommandTester(new rex_command_config_get());
        $commandTester->execute([
            'config-key' => 'author',
            '--package' => 'project', ],
        );
        self::assertEquals("\"Project Admin\"\n", $commandTester->getDisplay(true));
        self::assertEquals(0, $commandTester->getStatusCode());
    }
}
