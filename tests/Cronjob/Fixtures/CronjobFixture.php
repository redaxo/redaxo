<?php

namespace Redaxo\Core\Tests\Cronjob\Fixtures;

use RuntimeException;

/**
 * Methods used as cronjobs in the tests. They deliberately carry no `#[AsCronjob]` attribute, so that they don't
 * show up as real cronjobs of the development instance.
 *
 * @internal
 */
final class CronjobFixture
{
    public static int $calls = 0;

    public static function succeed(): string
    {
        ++self::$calls;

        return 'done';
    }

    public static function succeedVoid(): void
    {
        ++self::$calls;
    }

    public static function fail(): never
    {
        throw new RuntimeException('failed on purpose');
    }

    public static function returnInvalid(): int
    {
        return 1;
    }

    public static function requireParam(string $param): string
    {
        return $param;
    }

    public function nonStatic(): void {}
}
