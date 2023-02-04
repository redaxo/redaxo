<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_console_command_test extends TestCase
{
    public function testDecodeMessage(): void
    {
        $method = new ReflectionMethod(rex_console_command::class, 'decodeMessage');

        $command = $this->getMockForAbstractClass(rex_console_command::class);

        static::assertSame("\"Foo\"\nbar\nbaz\nabc\ndef", $method->invoke($command, "&quot;Foo&quot;<br><b>bar</b><br />\nbaz<br/>\rabc<br>\r\ndef"));
    }

    public function testDecodeMessageSingleQuotes(): void
    {
        $method = new ReflectionMethod(rex_console_command::class, 'decodeMessage');

        $command = $this->getMockForAbstractClass(rex_console_command::class);

        static::assertSame("Couldn't find the required PHP extension module session!", $method->invoke($command, 'Couldn&#039;t find the required PHP extension module session!'));
    }
}
