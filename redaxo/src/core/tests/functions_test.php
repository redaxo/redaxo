<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_functions_test extends TestCase
{
    public function testEscapeObject(): void
    {
        $obj = new stdClass();
        $obj->num = 1;
        $str = '<b>foo</b>';
        $obj->str = $str;

        $escapped = rex_escape($obj);

        /** @psalm-suppress RedundantCondition */
        static::assertSame($str, $obj->str);

        static::assertInstanceOf(stdClass::class, $escapped);
        static::assertSame(1, $escapped->num);
        static::assertSame('&lt;b&gt;foo&lt;/b&gt;', $escapped->str);
    }
}
