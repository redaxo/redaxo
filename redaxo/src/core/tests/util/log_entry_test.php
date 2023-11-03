<?php

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_log_entry_test extends TestCase
{
    public function testConstruct(): void
    {
        $time = time();
        $data = ['test1', 'test2'];
        $entry = new rex_log_entry($time, $data);

        static::assertSame($time, $entry->getTimestamp());
        static::assertSame($data, $entry->getData());
    }

    public function testCreateFromString(): void
    {
        $time = time();
        $entry = rex_log_entry::createFromString(date(rex_log_entry::DATE_FORMAT, $time) . ' | test1 |  |  test2\nt \| test3 |');

        static::assertInstanceOf(rex_log_entry::class, $entry);
        static::assertSame($time, $entry->getTimestamp());
        static::assertSame(['test1', '', "test2\nt | test3", ''], $entry->getData());
    }

    #[Depends('testConstruct')]
    public function testGetTimestamp(): void
    {
        $time = time();
        $entry = new rex_log_entry($time, []);

        static::assertSame($time, $entry->getTimestamp());
        $format = '%d.%m.%Y %H:%M:%S';

        $expected = @strftime($format, $time); /** @phpstan-ignore-line */

        static::assertSame($expected, @$entry->getTimestamp($format));
    }

    #[Depends('testConstruct')]
    public function testToString(): void
    {
        $time = time();
        $entry = new rex_log_entry($time, ['test1', ' ', " test2\nt | test3\r\ntest4 "]);

        static::assertSame(date(rex_log_entry::DATE_FORMAT, $time) . ' | test1 |  | test2\nt \| test3\ntest4', $entry->__toString());
    }
}
