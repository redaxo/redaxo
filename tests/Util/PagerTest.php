<?php

namespace Redaxo\Core\Tests\Util;

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Util\Pager;

/** @internal */
final class PagerTest extends TestCase
{
    public function testSetCursor(): void
    {
        $_REQUEST['start'] = 60;

        $pager = new Pager(30);
        self::assertSame(60, $pager->getCursor());
        self::assertSame(2, $pager->getCurrentPage());

        $pager->setCursor(0);
        self::assertSame(0, $pager->getCursor());
        self::assertSame(0, $pager->getCurrentPage());

        $pager->setCursor(30);
        self::assertSame(30, $pager->getCursor());
        self::assertSame(1, $pager->getCurrentPage());
    }

    public function testSetPage(): void
    {
        $_REQUEST['start'] = 60;

        $pager = new Pager(30);
        self::assertSame(60, $pager->getCursor());
        self::assertSame(2, $pager->getCurrentPage());

        $pager->setPage(0);
        self::assertSame(0, $pager->getCursor());
        self::assertSame(0, $pager->getCurrentPage());

        $pager->setPage(1);
        self::assertSame(30, $pager->getCursor());
        self::assertSame(1, $pager->getCurrentPage());
    }
}
