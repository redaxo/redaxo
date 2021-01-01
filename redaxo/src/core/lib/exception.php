<?php

/**
 * @package redaxo\core
 */
class rex_exception extends Exception
{
    /**
     * @param string    $message
     * @param Exception $previous
     */
    public function __construct($message, Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}

/**
 * @package redaxo\core
 */
class rex_sql_exception extends rex_exception
{
    /**
     * @var null|\rex_sql
     */
    private $sql;

    public function __construct($message, Exception $previous = null, rex_sql $sql = null)
    {
        parent::__construct($message, $previous);

        $this->sql = $sql;
    }

    /**
     * @return null|rex_sql
     */
    public function getSql()
    {
        return $this->sql;
    }

    public function isCouldNotConnectError(): bool
    {
        return false !== strpos($this->getMessage(), rex_sql::ERROR_MESSAGE_COULD_NOT_CONNECT);
    }

    /**
     * Returns the mysql native error code.
     */
    public function getErrorCode(): ?int
    {
        $previous = $this->getPrevious();
        if ($previous instanceof \PDOException) {
            return $previous->errorInfo[1] ?? null;
        }
        return null;
    }
}

/**
 * Exception class for user-friendly error messages.
 *
 * @package redaxo\core
 */
class rex_functional_exception extends rex_exception
{
}

/**
 * Exception class for http-status code handling.
 *
 * @package redaxo\core
 */
class rex_http_exception extends rex_exception
{
    /**
     * @var string
     */
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
}

/**
 * Exception class for yaml parse errors.
 *
 * @package redaxo\core
 */
class rex_yaml_parse_exception extends rex_exception
{
}
