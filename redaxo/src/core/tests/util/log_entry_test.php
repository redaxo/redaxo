<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_log_entry_test extends TestCase
{
    public function testConstruct()
    {
        $time = time();
        $data = ['test1', 'test2'];
        $entry = new rex_log_entry($time, $data);

        static::assertSame($time, $entry->getTimestamp());
        static::assertSame($data, $entry->getData());
    }

    public function testCreateFromString()
    {
        $time = time();
        $entry = rex_log_entry::createFromString(date('Y-m-d H:i:s', $time) . ' | test1 |  |  test2\nt \| test3 |');

        static::assertInstanceOf('rex_log_entry', $entry);
        static::assertSame($time, $entry->getTimestamp());
        static::assertSame(['test1', '', "test2\nt | test3", ''], $entry->getData());
    }

    /**
     * @depends testConstruct
     */
    public function testGetTimestamp()
    {
        $time = time();
        $entry = new rex_log_entry($time, []);

        static::assertSame($time, $entry->getTimestamp());
        $format = '%d.%m.%Y %H:%M:%S';
        static::assertSame(strftime($format, $time), $entry->getTimestamp($format));
    }

    /**
     * @depends testConstruct
     */
    public function testToString()
    {
        $time = time();
        $entry = new rex_log_entry($time, ['test1', ' ', " test2\nt | test3\r\ntest4 "]);

        static::assertSame(date('Y-m-d H:i:s', $time) . ' | test1 |  | test2\nt \| test3\ntest4', $entry->__toString());
    }
}
