<?php

class rex_markdown_test extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider parseProvider
     */
    public function testParse($expected, $code)
    {
        $markdown = new rex_markdown();
        $this->assertSame($expected, $markdown->parse($code));
    }

    public function parseProvider()
    {
        return [
            ['', ''],
            ['<p>foo <em>bar</em> <strong>baz</strong></p>', 'foo _bar_ **baz**'],
            ["<p>foo<br />\nbar</p>\n<p>baz</p>", "foo\nbar\n\nbaz"],
        ];
    }
}
