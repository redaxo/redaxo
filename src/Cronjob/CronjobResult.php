<?php

namespace Redaxo\Core\Cronjob;

/** @internal */
final readonly class CronjobResult
{
    public function __construct(
        public string $name,
        public bool $success,
        public string $message,
    ) {}
}
