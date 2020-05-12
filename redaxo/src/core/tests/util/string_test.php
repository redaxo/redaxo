<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_string_test extends TestCase
{
    public function testSize()
    {
        static::assertEquals(3, rex_string::size('aä'));
    }

    public function normalizeProvider()
    {
        return [
            [
                'ae_oe_ue_ae_oe_ue_ss_ae_oe_ue_ae_oe_ue',
                "Ä Ö Ü ä ö ü ß A\xcc\x88 O\xcc\x88 U\xcc\x88 a\xcc\x88 o\xcc\x88 u\xcc\x88",
            ],
            ['test-12-3-4-a', 'Test. 12+3+-4 [a]', '-'],
            ['test123', '"test" 123', ''],
            ['[€_1]', '[€ 1]', '_', '[]€'],
        ];
    }

    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize($expected, $string, $replaceChar = '_', $allowedChars = '')
    {
        static::assertEquals($expected, rex_string::normalize($string, $replaceChar, $allowedChars));
    }

    public function splitProvider()
    {
        return [
            ['',                                          []],
            ['a b c',                                     ['a', 'b', 'c']],
            ['"a b" cdef \'ghi kl\'',                     ['a b', 'cdef', 'ghi kl']],
            ['a=1 b=xyz c="hu hu" 123=\'he he\'',         ['a' => 1, 'b' => 'xyz', 'c' => 'hu hu', '123' => 'he he']],
            ['a="a \"b\" c" b=\'a \\\'b\\\'\' c="a\\\\"', ['a' => 'a "b" c', 'b' => "a 'b'", 'c' => 'a\\']],
            ["\n a=1\n b='aa\nbb'\n c='a'\n ",            ['a' => '1', 'b' => "aa\nbb", 'c' => 'a']],
            ['"a b" c "d e',                              ['a b', 'c', '"d', 'e']],
            ['"a"b" "c"d',                                ['a"b', '"c"d']],
        ];
    }

    /**
     * @dataProvider splitProvider
     */
    public function testSplit($string, $expectedArray)
    {
        static::assertEquals($expectedArray, rex_string::split($string));
    }

    public function buildQueryProvider()
    {
        return [
            ['', []],
            ['page=system/settings&a%2Bb=test+test', ['page' => 'system/settings', 'a+b' => 'test test']],
            ['arr[0]=a&arr[1]=b&arr[key]=c', ['arr' => ['a', 'b', 'key' => 'c']]],
            ['a=1&amp;b=2', ['a' => 1, 'b' => 2], '&amp;'],
        ];
    }

    /**
     * @dataProvider buildQueryProvider
     */
    public function testBuildQuery($expected, $params, $argSeparator = '&')
    {
        static::assertEquals($expected, rex_string::buildQuery($params, $argSeparator));
    }

    public function testBuildAttributes()
    {
        static::assertEquals(
            ' id="rex-test" class="a b" alt="" checked data-foo="&lt;foo&gt; &amp; &quot;bar&quot;" href="index.php?foo=1&amp;bar=2"',
            rex_string::buildAttributes([
                'id' => 'rex-test',
                'class' => ['a', 'b'],
                'alt' => '',
                'checked',
                'data-foo' => '<foo> & "bar"',
                'href' => 'index.php?foo=1&amp;bar=2',
            ])
        );
    }

    public function testSanitizeHtml()
    {
        $input = <<<'INPUT'
<p align=center><img src="foo.jpg" style="width: 200px"></p>
<a name="test"></a>

<script>alert(1)</script>
<a href="javascript:alert(1)">Foo</a>
<a href="index.php" onclick="alert(1)">Foo</a>
<img src="foo.jpg" onmouseover="alert(1)"/>

<pre><code>
    &lt;script&gt; foo() &lt;/script&gt;
</code></pre>
INPUT;

        $expected = <<<'EXPECTED'
<p align=center><img src="foo.jpg" style="width: 200px"></p>
<a name="test"></a>


<a href="(1)">Foo</a>
<a href="index.php">Foo</a>
<img src="foo.jpg" />

<pre><code>
    &lt;script&gt; foo() &lt;/script&gt;
</code></pre>
EXPECTED;

        static::assertSame($expected, rex_string::sanitizeHtml($input));
    }
}
