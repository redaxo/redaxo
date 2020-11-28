<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_package_test extends TestCase
{
    /**
     * @dataProvider dataSplitPackageId
     */
    public function testSplitPackageId(array $expected, string $packageId)
    {
        static::assertSame($expected, rex_package::splitPackageId($packageId));
    }

    public function dataSplitPackageId(): iterable
    {
        return [
            [['foo', null], 'foo'],
            [['foo', 'bar'], 'foo/bar'],
        ];
    }
}
