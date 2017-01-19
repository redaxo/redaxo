<?php

class rex_markdown_test extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider parseProvider
     */
    public function testParse($expected, $code)
    {
        $this->assertSame($expected, rex_markdown::factory()->parse($code));
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
