<?php

namespace Redaxo\Core\Tests\View;

use PHPUnit\Framework\TestCase;
use stdClass;

use function Redaxo\Core\View\escape;

/** @internal */
final class EscapeTest extends TestCase
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
