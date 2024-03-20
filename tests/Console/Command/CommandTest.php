<?php

namespace Redaxo\Core\Tests\Console\Command;

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Console\Command\AbstractCommand;
use ReflectionMethod;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** @internal */
final class CommandTest extends TestCase
{
    public function testDecodeMessage(): void
    {
        $method = new ReflectionMethod(AbstractCommand::class, 'decodeMessage');

        $command = new class() extends AbstractCommand {
            public function execute(InputInterface $input, OutputInterface $output): int
            {
                return 0;
            }
        };

        self::assertSame("\"Foo\"\nbar\nbaz\nabc\ndef", $method->invoke($command, "&quot;Foo&quot;<br><b>bar</b><br />\nbaz<br/>\rabc<br>\r\ndef"));
    }

    public function testDecodeMessageSingleQuotes(): void
    {
        $method = new ReflectionMethod(AbstractCommand::class, 'decodeMessage');

        $command = new class() extends AbstractCommand {
            public function execute(InputInterface $input, OutputInterface $output): int
            {
                return 0;
            }
        };

        self::assertSame("Couldn't find the required PHP extension module session!", $method->invoke($command, 'Couldn&#039;t find the required PHP extension module session!'));
    }
}
