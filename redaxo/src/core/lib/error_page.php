<?php

/**
 * @package redaxo\core
 */
class rex_error_page extends \Whoops\Handler\PrettyPageHandler {
    public function handle()
    {
        if (($user = rex_backend_login::createUser()) && $user->isAdmin()) {
            return parent::handle();
        }
    }
}
