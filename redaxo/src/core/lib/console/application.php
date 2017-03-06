<?php

use Symfony\Component\Console\Application;

/**
 * @package redaxo\core
 */
class rex_console_application extends Application
{
    public function __construct()
    {
        parent::__construct('REDAXO', rex::getVersion());
    }
}
