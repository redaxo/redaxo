<?php

/**
 * @package redaxo\core\login
 */
class rex_user_session
{
    use rex_singleton_trait;

    private const SESSION_VAR_LAST_DB_UPDATE = 'last_db_update';

    private function __construct()
    {
        rex_extension::register('RESPONSE_SHUTDOWN', [self::class, 'clearExpiredSessions']);
    }

    public function storeCurrentSession(): void
    {
        if (false === session_id()) {
            return;
        }

        $login = new rex_backend_login();
        $userId = $login->getSessionVar(rex_login::SESSION_IMPERSONATOR, null);
        if (null === $userId) {
            $userId = $login->getSessionVar(rex_login::SESSION_USER_ID);
        }

        rex_sql::factory()
            ->setTable(rex::getTable('user_session'))
            ->setValue('session_id', session_id())
            ->setValue('user_id', $userId)
            ->setValue('ip', rex_request::server('REMOTE_ADDR', 'string'))
            ->setValue('useragent', rex_request::server('HTTP_USER_AGENT', 'string'))
            ->setValue('starttime', rex_sql::datetime($login->getSessionVar(rex_login::SESSION_START_TIME, time())))
            ->setValue('last_activity', rex_sql::datetime($login->getSessionVar(rex_login::SESSION_LAST_ACTIVITY)))
            ->insertOrUpdate();

        $login->setSessionVar(self::SESSION_VAR_LAST_DB_UPDATE, time());
    }

    public function clearCurrentSession(): void
    {
        if (false === session_id()) {
            return;
        }

        rex_sql::factory()
            ->setTable(rex::getTable('user_session'))
            ->setWhere('session_id = ?', [session_id()])
            ->delete();
    }

    public function updateLastActivity(): void
    {
        if (false === session_id()) {
            return;
        }

        $login = new rex_backend_login();

        // only once a minute
        if ($login->getSessionVar(self::SESSION_VAR_LAST_DB_UPDATE, 0) > (time() - 60)) {
            return;
        }

        $this->storeCurrentSession();
    }

    public static function clearExpiredSessions(): void
    {
        rex_sql::factory()
            ->setTable(rex::getTable('user_session'))
            ->setWhere('UNIX_TIMESTAMP(last_activity) < ?', [time() - (int) rex::getProperty('session_duration')])
            ->delete();
    }

    public function removeSession(string $sessionId, int $userId): bool
    {
        $sql = rex_sql::factory()
            ->setTable(rex::getTable('user_session'))
            ->setWhere('session_id = ? and user_id = ?', [$sessionId, $userId])
            ->delete();

        return $sql->getRows() > 0;
    }
}
