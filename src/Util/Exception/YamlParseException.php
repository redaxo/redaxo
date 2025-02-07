<?php

namespace Redaxo\Core\Util\Exception;

use Redaxo\Core\Exception\Exception;
use Redaxo\Core\Exception\RuntimeException;

/**
 * Exception class for yaml parse errors.
 */
final class YamlParseException extends RuntimeException implements Exception {}
