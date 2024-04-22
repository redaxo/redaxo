<?php

use PHPUnit\Framework\TestCase;
use Redaxo\Core\View\Listing;

/** @internal */
final class rex_list_test extends TestCase
{
    public function testPrepareCountQuery(): void
    {
        $method = new ReflectionMethod(Listing::class, 'prepareCountQuery');

        $query = 'SELECT *, IF(foo = 1, 0, (SELECT x FROM bar)) as qux FROM foo ORDER BY qux';
        $expected = 'SELECT COUNT(*) AS `rows` FROM (SELECT *, IF(foo = 1, 0, (SELECT x FROM bar)) as qux FROM foo ORDER BY qux) t';

        self::assertSame($expected, $method->invoke(null, $query));
    }
}
