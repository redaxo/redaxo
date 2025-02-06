<?php

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
