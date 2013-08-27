<?php

class rex_log_entry_test extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $time = time();
        $data = ['test1', 'test2'];
        $entry = new rex_log_entry($time, $data);

        $this->assertSame($time, $entry->getTimestamp());
        $this->assertSame($data, $entry->getData());
    }

    public function testCreateFromString()
    {
        $time = time();
        $entry = rex_log_entry::createFromString(date('Y-m-d H:i:s', $time) . ' | test1 |  |  test2');

        $this->assertInstanceOf('rex_log_entry', $entry);
        $this->assertSame($time, $entry->getTimestamp());
        $this->assertSame(['test1', '', 'test2'], $entry->getData());
    }

    /**
     * @depends testConstruct
     */
    public function testGetTimestamp()
    {
        $time = time();
        $entry = new rex_log_entry($time, []);

        $this->assertSame($time, $entry->getTimestamp());
        $format = '%d.%m.%Y %H:%M:%S';
        $this->assertSame(strftime($format, $time), $entry->getTimestamp($format));
    }

    /**
     * @depends testConstruct
     */
    public function testToString()
    {
        $time = time();
        $entry = new rex_log_entry($time, ['test1', ' ', " test2\nt "]);

        $this->assertSame(date('Y-m-d H:i:s', $time) . ' | test1 |  | test2t', $entry->__toString());
    }
}
