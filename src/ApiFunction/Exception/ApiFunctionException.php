<?php

namespace Redaxo\Core\ApiFunction\Exception;

use rex_exception;

/**
 * Exception-Type to indicate exceptions in an api function.
 * The messages of this exception will be displayed to the end-user.
 *
 * @see ApiFunction
 */
class ApiFunctionException extends rex_exception {}
