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

## Duplicate Test

## Duplicate Test

## [Title with Markdown](#sub-1)

## <i>Title with HTML</i>

## Title with "quotes" & 'other' special &lt;chars&gt;
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
<li>
<a href="#header-duplicate-test">Duplicate Test</a>
</li>
<li>
<a href="#header-duplicate-test-2">Duplicate Test</a>
</li>
<li>
<a href="#header-title-with-markdown">Title with Markdown</a>
</li>
<li>
<a href="#header-title-with-html">Title with HTML</a>
</li>
<li>
<a href="#header-title-with-quotes-other-special-chars">Title with &quot;quotes&quot; &amp; &#039;other&#039; special &lt;chars&gt;</a>
</li>
</ul>

HTML;

        static::assertSame($expected, $toc);
    }
}
