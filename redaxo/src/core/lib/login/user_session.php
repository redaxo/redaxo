<?php

/**
 * @package redaxo\core\login
 */
class rex_user_session
{
    public static function storeCurrentSession(): void
    {
        $login = new rex_backend_login();

        rex_sql::factory()
            ->setTable(rex::getTable('user_session'))
            ->setValue('session_id', session_id())
            ->setValue('user_id', $login->getSessionVar(rex_backend_login::SESSION_USER_ID))
            ->setValue('ip', rex_request::server('REMOTE_ADDR', 'string'))
            ->setValue('useragent', rex_request::server('HTTP_USER_AGENT', 'string'))
            ->setValue('starttime', rex_sql::datetime($login->getSessionVar(rex_backend_login::SESSION_START_TIME, time())))
            ->setValue('last_activity', rex_sql::datetime($login->getSessionVar(rex_backend_login::SESSION_LAST_ACTIVITY)))
            ->insertOrUpdate();
    }

    public static function clearCurrentSession(): void
    {
        if (false === session_id()) {
            return;
        }

        rex_sql::factory()
            ->setTable(rex::getTable('user_session'))
            ->setWhere('session_id = ?', [session_id()])
            ->delete();
    }
}
