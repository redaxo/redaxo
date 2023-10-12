<?php

/**
 * @package redaxo\core
 * @internal
 */
class rex_error_sandbox
{
    private ?string $error = null;

    public function run(callable $cb): mixed {
        $this->error = null;
        set_error_handler(function (int $type, string $msg): void {
            $this->error = $msg;
        });

        try {
            return $cb();
        } finally {
            restore_error_handler();
        }
    }

    public function getError(): ?string {
        return $this->error;
    }
}
