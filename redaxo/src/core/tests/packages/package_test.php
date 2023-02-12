<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_package_test extends TestCase
{
    #[DataProvider('dataSplitId')]
    public function testSplitId(array $expected, string $packageId): void
    {
        static::assertSame($expected, rex_package::splitId($packageId));
    }

    public static function dataSplitId(): iterable
    {
        return [
            [['foo', null], 'foo'],
            [['foo', 'bar'], 'foo/bar'],
        ];
    }
}
