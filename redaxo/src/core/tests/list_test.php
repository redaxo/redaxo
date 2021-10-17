<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_list_test extends TestCase
{
    public function testPrepareCountQuery(): void
    {
        $method = new ReflectionMethod(rex_list::class, 'prepareCountQuery');
        $method->setAccessible(true);

        $query = 'SELECT *, IF(foo = 1, 0, (SELECT x FROM bar)) as qux FROM foo ORDER qux';
        $expected = 'SELECT COUNT(*) AS `rows` FROM (SELECT *, IF(foo = 1, 0, (SELECT x FROM bar)) as qux FROM foo ORDER qux) t';

        static::assertSame($expected, $method->invoke(null, $query));
    }
}
