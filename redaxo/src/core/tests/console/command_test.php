<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** @internal */
final class rex_console_command_test extends TestCase
{
    public function testDecodeMessage(): void
    {
        $method = new ReflectionMethod(rex_console_command::class, 'decodeMessage');

        $command = new class extends rex_console_command {
            public function execute(InputInterface $input, OutputInterface $output): int
            {
                return 0;
            }
        };

        self::assertSame("\"Foo\"\nbar\nbaz\nabc\ndef", $method->invoke($command, "&quot;Foo&quot;<br><b>bar</b><br />\nbaz<br/>\rabc<br>\r\ndef"));
    }

    public function testDecodeMessageSingleQuotes(): void
    {
        $method = new ReflectionMethod(rex_console_command::class, 'decodeMessage');

        $command = new class extends rex_console_command {
            public function execute(InputInterface $input, OutputInterface $output): int
            {
                return 0;
            }
        };

        self::assertSame("Couldn't find the required PHP extension module session!", $method->invoke($command, 'Couldn&#039;t find the required PHP extension module session!'));
    }
}
