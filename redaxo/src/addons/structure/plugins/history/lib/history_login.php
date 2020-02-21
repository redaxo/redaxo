<?php

/**
 * @author dergel
 *
 * @package redaxo\structure\history
 *
 * @internal
 */
class rex_history_login extends rex_backend_login
{
    public function __construct()
    {
        parent::__construct();
    }

    public function checkTempSession($historyLogin, $historySession, $historyValidtime)
    {
        $user_sql = rex_sql::factory($this->DB);
        $user_sql->setQuery($this->loginQuery, [':login' => $historyLogin]);

        if (1 == $user_sql->getRows()) {
            if (self::verifySessionKey($historyLogin . $user_sql->getValue('session_id') . $historyValidtime, $historySession)) {
                $this->user = $user_sql;
                $this->setSessionVar('STAMP', time());
                $this->setSessionVar('UID', $this->user->getValue($this->idColumn));
                return parent::checkLogin();
            }
        }

        return null;
    }

    /**
     * @return null|string
     */
    public static function createSessionKey($login, $session, $validtime)
    {
        return password_hash($login . $session . $validtime, PASSWORD_DEFAULT);
    }

    /**
     * @return bool
     */
    public static function verifySessionKey($key1, $key2)
    {
        return password_verify($key1, $key2);
    }
}
