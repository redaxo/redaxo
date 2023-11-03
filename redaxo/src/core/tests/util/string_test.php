<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_string_test extends TestCase
{
    public function testSize(): void
    {
        static::assertEquals(3, rex_string::size('aä'));
    }

    /** @return list<array{0: string, 1: string, 2?: string, 3?: string}> */
    public static function normalizeProvider(): array
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

    #[DataProvider('normalizeProvider')]
    public function testNormalize(string $expected, string $string, string $replaceChar = '_', string $allowedChars = ''): void
    {
        static::assertEquals($expected, rex_string::normalize($string, $replaceChar, $allowedChars));
    }

    /** @return list<array{string, array<int|string, string|int>}> */
    public static function splitProvider(): array
    {
        return [
            ['',                                          []],
            ['a b c',                                     ['a', 'b', 'c']],
            ['"a b" cdef \'ghi kl\'',                     ['a b', 'cdef', 'ghi kl']],
            ['a=1 b=xyz c="hu hu" 123=\'he he\'',         ['a' => '1', 'b' => 'xyz', 'c' => 'hu hu', '123' => 'he he']],
            ['a="a \"b\" c" b=\'a \\\'b\\\'\' c="a\\\\"', ['a' => 'a "b" c', 'b' => "a 'b'", 'c' => 'a\\']],
            ["\n a=1\n b='aa\nbb'\n c='a'\n ",            ['a' => '1', 'b' => "aa\nbb", 'c' => 'a']],
            ['"a b" c "d e',                              ['a b', 'c', '"d', 'e']],
            ['"a"b" "c"d',                                ['a"b', '"c"d']],
        ];
    }

    #[DataProvider('splitProvider')]
    public function testSplit(string $string, array $expectedArray): void
    {
        static::assertSame($expectedArray, rex_string::split($string));
    }

    /** @return list<array{0: string, 1: array, 2?: string}> */
    public static function buildQueryProvider(): array
    {
        return [
            ['', []],
            ['page=system/settings&a%2Bb=test+test', ['page' => 'system/settings', 'a+b' => 'test test']],
            ['arr[0]=a&arr[1]=b&arr[key]=c', ['arr' => ['a', 'b', 'key' => 'c']]],
            ['a=1&amp;b=2', ['a' => 1, 'b' => 2], '&amp;'],
        ];
    }

    #[DataProvider('buildQueryProvider')]
    public function testBuildQuery(string $expected, array $params, string $argSeparator = '&'): void
    {
        static::assertEquals($expected, rex_string::buildQuery($params, $argSeparator));
    }

    public function testBuildAttributes(): void
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
            ]),
        );
    }

    public function testSanitizeHtml(): void
    {
        $input = <<<'INPUT'
            <p align=center><img src="foo.jpg" style="width: 200px"></p>
            <a name="test"></a>

            <script>
                alert(1);
                window.location.replace(my_link);
            </script>
            <a href="javascript:alert(1)">Foo</a>
            <a href="index.php" onclick="alert(1)">Foo</a>
            <img src="foo.jpg" onmouseover="alert(1)"/>

            <pre><code>
                &lt;script&gt;
                    foo();
                    window.location.replace(my_link);
                &lt;/script&gt;
            </code></pre>
            INPUT;

        $expected = <<<'EXPECTED'
            <p align=center><img src="foo.jpg" style="width: 200px"></p>
            <a name="test"></a>


            <a href="(1)">Foo</a>
            <a href="index.php">Foo</a>
            <img src="foo.jpg" />

            <pre><code>
                &lt;script&gt;
                    foo();
                    window.location.replace(my_link);
                &lt;/script&gt;
            </code></pre>
            EXPECTED;

        static::assertSame($expected, rex_string::sanitizeHtml($input));
    }
}
