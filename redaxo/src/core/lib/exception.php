<?php

use Redaxo\Core\HttpClient\Request;
use Redaxo\Core\Database\Sql;

class rex_exception extends Exception
{
    /**
     * @param string $message
     */
    public function __construct($message, ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}

/**
 * Exception class for http-status code handling.
 */
class rex_http_exception extends rex_exception
{
    /** @var string */
    private $httpCode;

    /**
     * @param string $httpCode
     */
    public function __construct(Exception $cause, $httpCode)
    {
        parent::__construct($cause->getMessage(), $cause);
        $this->httpCode = $httpCode;
    }

    /**
     * @return string
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    public function isClientError(): bool
    {
        return str_starts_with($this->httpCode, '4');
    }
}
