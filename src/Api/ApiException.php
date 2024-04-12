<?php

namespace Redaxo\Core\Api;

use rex_exception;

/**
 * Exception-Type to indicate exceptions in an api function.
 * The messages of this exception will be displayed to the end-user.
 *
 * @see ApiFunction
 */
class ApiException extends rex_exception {}
