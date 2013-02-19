<?php

/**
 * Class to monitor extension points
 *
 * @author staabm
 */
class rex_logger_debug extends rex_logger
{
    /**
     * Logs the given message
     *
     * @param string  $message the message to log
     * @param integer $errno
     */
    static public function log($message, $errno = E_USER_ERROR)
    {
        if (!empty($message)) {
            $firephp = FirePHP::getInstance(true);

            switch ($errno) {
                case E_USER_NOTICE:
                case E_NOTICE:
                    $firephp->log($message);
                    break;

                case E_USER_WARNING:
                case E_WARNING:
                case E_COMPILE_WARNING:
                    $firephp->warn($message);
                    break;

                default:
                    $firephp->error($message);
                    break;
            }
        }

        parent::log($message, $errno);
    }
}
