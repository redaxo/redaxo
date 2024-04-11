<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** @internal */
final class rex_package_test extends TestCase
{
    /** @param array{string, ?string} $expected */
    #[DataProvider('dataSplitId')]
    public function testSplitId(array $expected, string $packageId): void
    {
        self::assertSame($expected, rex_package::splitId($packageId));
    }

    /** @return list<array{array{string, ?string}, string}> */
    public static function dataSplitId(): array
    {
        return [
            [['foo', null], 'foo'],
            [['foo', 'bar'], 'foo/bar'],
        ];
    }
}
