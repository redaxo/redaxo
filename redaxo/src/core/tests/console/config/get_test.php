<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
class rex_command_config_get_test extends TestCase
{
    #[DataProvider('dataKeyFound')]
    public function testKeyFound(string $expectedValue, string $key): void
    {
        $commandTester = new CommandTester(new rex_command_config_get());
        $commandTester->execute([
            'config-key' => $key,
        ]);
        static::assertEquals($expectedValue, $commandTester->getDisplay(true));
        static::assertEquals(0, $commandTester->getStatusCode());
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
        static::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testPackageKeyFound(): void
    {
        $commandTester = new CommandTester(new rex_command_config_get());
        $commandTester->execute([
            'config-key' => 'author',
            '--package' => 'backup', ],
        );
        static::assertEquals("\"Jan Kristinus, Markus Staab\"\n", $commandTester->getDisplay(true));
        static::assertEquals(0, $commandTester->getStatusCode());
    }
}
