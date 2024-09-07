<?php

use PHPUnit\Framework\TestCase;

use function Redaxo\Core\View\escape;

/** @internal */
final class rex_functions_test extends TestCase
{
    public function testEscapeObject(): void
    {
        $obj = new stdClass();
        $obj->num = 1;
        $str = '<b>foo</b>';
        $obj->str = $str;

        $escapped = escape($obj);

        /** @psalm-suppress RedundantCondition */
        self::assertSame($str, $obj->str);

        self::assertInstanceOf(stdClass::class, $escapped);
        self::assertSame(1, $escapped->num);
        self::assertSame('&lt;b&gt;foo&lt;/b&gt;', $escapped->str);
    }
}
