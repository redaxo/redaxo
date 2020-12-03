<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_package_test extends TestCase
{
    /**
     * @dataProvider dataSplitId
     */
    public function testSplitId(array $expected, string $packageId)
    {
        static::assertSame($expected, rex_package::splitId($packageId));
    }

    public function dataSplitId(): iterable
    {
        return [
            [['foo', null], 'foo'],
            [['foo', 'bar'], 'foo/bar'],
        ];
    }
}
