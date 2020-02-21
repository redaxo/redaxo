<?php

namespace J13k\YamlLint;

/**
 * Runtime exception for triggering usage message
 *
 * @property int $code Exception code is passed through as script exit code
 */
class UsageException extends \RuntimeException
{
}
