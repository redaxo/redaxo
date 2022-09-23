<?php

/**
 * @package redaxo\core\login
 */
class rex_user_session
{
    private const SESSION_VAR_LAST_DB_UPDATE = 'last_db_update';

    public static function storeCurrentSession(): void
    {
        if (false === session_id()) {
            return;
        }

        $login = new rex_backend_login();

        rex_sql::factory()
            ->setTable(rex::getTable('user_session'))
            ->setValue('session_id', session_id())
            ->setValue('user_id', $login->getSessionVar(rex_backend_login::SESSION_IMPERSONATOR, null) ?? $login->getSessionVar(rex_backend_login::SESSION_USER_ID))
            ->setValue('ip', rex_request::server('REMOTE_ADDR', 'string'))
            ->setValue('useragent', rex_request::server('HTTP_USER_AGENT', 'string'))
            ->setValue('starttime', rex_sql::datetime($login->getSessionVar(rex_backend_login::SESSION_START_TIME, time())))
            ->setValue('last_activity', rex_sql::datetime($login->getSessionVar(rex_backend_login::SESSION_LAST_ACTIVITY)))
            ->insertOrUpdate();

        $login->setSessionVar(self::SESSION_VAR_LAST_DB_UPDATE, time());
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

    public static function updateLastActivity(): void
    {
        if (false === session_id()) {
            return;
        }

        $login = new rex_backend_login();

        // only once a minute
        if ($login->getSessionVar(self::SESSION_VAR_LAST_DB_UPDATE, 0) > (time() - 60)) {
            return;
        }

        self::storeCurrentSession();
    }
}
