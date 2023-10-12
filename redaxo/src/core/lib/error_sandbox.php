<?php

/**
 * @package redaxo\core
 * @internal
 */
class rex_error_sandbox
{
    private static ?string $error = null;

    public static function run(callable $cb): mixed {
        self::$error = null;
        set_error_handler(function (int $type, string $msg): void {
            self::$error = $msg;
        });

        try {
            return $cb();
        } finally {
            restore_error_handler();
        }
    }

    public static function getError(): ?string {
        return self::$error;
    }
}
