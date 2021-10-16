<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_list_test extends TestCase
{
    /**
     * @dataProvider dataPrepareCountQuery
     */
    public function testPrepareCountQuery(string $expected, string $query): void
    {
        $method = new ReflectionMethod(rex_list::class, 'prepareCountQuery');
        $method->setAccessible(true);

        static::assertSame($expected, $method->invoke(null, $query));
    }

    public function dataPrepareCountQuery(): iterable
    {
        return [
            [
                'SELECT COUNT(*) AS `rows` FROM foo',
                'SELECT * FROM foo',
            ],
            [
                'SELECT COUNT(*) AS `rows` FROM foo WHERE 1 = (SELECT baz FROM bar)',
                "\n".'SELECT bar, baz FROM foo WHERE 1 = (SELECT baz FROM bar)',
            ],
            [
                'SELECT COUNT(*) AS `rows` FROM foo',
                'SELECT *, COUNT(1), IF(foo, bar + 1, 0) FROM foo',
            ],
            [
                'SELECT COUNT(*) AS `rows` FROM foo',
                'SELECT foo, (SELECT bar FROM baz WHERE x = 1), qux, IFNULL(quux, 2) FROM foo',
            ],
        ];
    }
}
