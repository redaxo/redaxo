<?php

class rex_history_login extends rex_backend_login
{
    public function __construct()
    {
        parent::__construct();

    }

    public function checkSessionLogin($userSession, $userLogin)
    {
        $this->user = rex_sql::factory($this->DB);
        $this->user->setQuery($this->loginQuery . ' and session_id = :session_id', [':login' => $userLogin, ':session_id' => $userSession]);
        if ($this->user->getRows() == 1) {
            $this->setSessionVar('STAMP', time());
            $this->setSessionVar('UID', $this->user->getValue($this->idColumn));
            return parent::checkLogin();

        }

        return null;
    }
}
