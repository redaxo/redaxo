<?php

namespace Redaxo\Core\Tests\View;

use PHPUnit\Framework\TestCase;
use Redaxo\Core\View\Listing;
use ReflectionMethod;

/** @internal */
final class ListingTest extends TestCase
{
    public function testPrepareCountQuery(): void
    {
        $method = new ReflectionMethod(Listing::class, 'prepareCountQuery');

        $query = 'SELECT *, IF(foo = 1, 0, (SELECT x FROM bar)) as qux FROM foo ORDER BY qux';
        $expected = 'SELECT COUNT(*) AS `rows` FROM (SELECT *, IF(foo = 1, 0, (SELECT x FROM bar)) as qux FROM foo ORDER BY qux) t';

        self::assertSame($expected, $method->invoke(null, $query));
    }
}
