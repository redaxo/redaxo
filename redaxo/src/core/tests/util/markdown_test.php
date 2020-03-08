<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_markdown_test extends TestCase
{
    /**
     * @dataProvider parseProvider
     */
    public function testParse($expected, $code)
    {
        static::assertSame($expected, rex_markdown::factory()->parse($code));
    }

    public function parseProvider()
    {
        return [
            ['', ''],
            ['<p>foo <em>bar</em> <strong>baz</strong></p>', 'foo _bar_ **baz**'],
            ["<p>foo<br />\nbar</p>\n<p>baz</p>", "foo\nbar\n\nbaz"],
            [
                <<<'HTML'

<pre><code class="language-php">    &lt;script&gt;foo()&lt;/script&gt;</code></pre>
HTML
                ,
                <<<'MD'
<script> foo() </script>

```php
    <script>foo()</script>
```
MD
            ],
        ];
    }

    public function testParseWithToc()
    {
        $input = <<<'MARKDOWN'
Test
====

Foo bar

Sub 1
-----

Foo bar

### Sub 1.1

### Sub 1.2

#### Sub Sub 1.2.1

##### Sub Sub 1.2.1.1

## Sub 2

### Sub 2.1

## Sub 3

### Sub 3.1
MARKDOWN;

        [$toc, $content] = rex_markdown::factory()->parseWithToc($input, 2, 4);

        $expected = <<<'HTML'
<ul>
<li>
<a href="#header-sub-1">Sub 1</a>
<ul>
<li>
<a href="#header-sub-1-1">Sub 1.1</a>
</li>
<li>
<a href="#header-sub-1-2">Sub 1.2</a>
<ul>
<li>
<a href="#header-sub-sub-1-2-1">Sub Sub 1.2.1</a>
</li>
</ul>
</li>
</ul>
<li>
<a href="#header-sub-2">Sub 2</a>
<ul>
<li>
<a href="#header-sub-2-1">Sub 2.1</a>
</li>
</ul>
<li>
<a href="#header-sub-3">Sub 3</a>
<ul>
<li>
<a href="#header-sub-3-1">Sub 3.1</a>
</li>
</ul>
</li>
</ul>

HTML;

        static::assertSame($expected, $toc);
    }
}
