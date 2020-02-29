<?php

/**
 * @package redaxo\core\login
 *
 * @method null|rex_user getUser()
 * @method null|rex_user getImpersonator()
 */
class rex_backend_login extends rex_login
{
    public const SYSTEM_ID = 'backend_login';
    public const LOGIN_TRIES_1 = 3;
    public const RELOGIN_DELAY_1 = 5;    // relogin delay after LOGIN_TRIES_1 tries
    public const LOGIN_TRIES_2 = 50;
    public const RELOGIN_DELAY_2 = 3600; // relogin delay after LOGIN_TRIES_2 tries
    /**
     * @var string
     */
    private $tableName;
    private $stayLoggedIn;

    public function __construct()
    {
        parent::__construct();

        $tableName = rex::getTablePrefix() . 'user';
        $this->setSqlDb(1);
        $this->setSystemId(self::SYSTEM_ID);
        $this->setSessionDuration(rex::getProperty('session_duration'));
        $qry = 'SELECT * FROM ' . $tableName;
        $this->setUserQuery($qry . ' WHERE id = :id AND status = 1');
        $this->setImpersonateQuery($qry . ' WHERE id = :id');
        // XXX because with concat the time into the sql query, users of this class should use checkLogin() immediately after creating the object.
        $this->setLoginQuery($qry . ' WHERE
            status = 1
            AND login = :login
            AND (login_tries < ' . self::LOGIN_TRIES_1 . '
                OR login_tries < ' . self::LOGIN_TRIES_2 . ' AND lasttrydate < "' . rex_sql::datetime(time() - self::RELOGIN_DELAY_1) . '"
                OR lasttrydate < "' . rex_sql::datetime(time() - self::RELOGIN_DELAY_2) . '"
            )'
        );
        $this->tableName = $tableName;
    }

    public function setStayLoggedIn($stayLoggedIn = false)
    {
        $this->stayLoggedIn = $stayLoggedIn;
    }

    public function checkLogin()
    {
        $sql = rex_sql::factory();
        $userId = $this->getSessionVar('UID');
        $cookiename = self::getStayLoggedInCookieName();

        if ($cookiekey = rex_cookie($cookiename, 'string')) {
            if (!$userId) {
                $sql->setQuery('SELECT id FROM ' . rex::getTable('user') . ' WHERE cookiekey = ? LIMIT 1', [$cookiekey]);
                if (1 == $sql->getRows()) {
                    $this->setSessionVar('UID', $sql->getValue('id'));
                    rex_response::sendCookie($cookiename, $cookiekey, ['expires' => strtotime('+1 year'), 'samesite' => 'strict']);
                } else {
                    self::deleteStayLoggedInCookie();
                }
            }
            $this->setSessionVar('STAMP', time());
        }

        $check = parent::checkLogin();

        if ($check) {
            // gelungenen versuch speichern | login_tries = 0
            if ('' != $this->userLogin || !$userId) {
                self::regenerateSessionId();
                $params = [];
                $add = '';
                if ($this->stayLoggedIn || $cookiekey) {
                    $cookiekey = sha1($this->systemId . time() . $this->userLogin);
                    $add = 'cookiekey = ?, ';
                    $params[] = $cookiekey;
                    rex_response::sendCookie($cookiename, $cookiekey, ['expires' => strtotime('+1 year'), 'samesite' => 'strict']);
                }
                if (self::passwordNeedsRehash($this->user->getValue('password'))) {
                    $add .= 'password = ?, ';
                    $params[] = self::passwordHash($this->userPassword, true);
                }
                array_push($params, rex_sql::datetime(), rex_sql::datetime(), session_id(), $this->userLogin);
                $sql->setQuery('UPDATE ' . $this->tableName . ' SET ' . $add . 'login_tries=0, lasttrydate=?, lastlogin=?, session_id=? WHERE login=? LIMIT 1', $params);
            }

            $this->user = new rex_user($this->user);

            if ($this->impersonator instanceof rex_sql) {
                $this->impersonator = new rex_user($this->impersonator);
            }
        } else {
            // fehlversuch speichern | login_tries++
            if ('' != $this->userLogin) {
                $sql->setQuery('SELECT login_tries FROM ' . $this->tableName . ' WHERE login=? LIMIT 1', [$this->userLogin]);
                if ($sql->getRows() > 0) {
                    $login_tries = $sql->getValue('login_tries');
                    $sql->setQuery('UPDATE ' . $this->tableName . ' SET login_tries=login_tries+1,session_id="",cookiekey="",lasttrydate=? WHERE login=? LIMIT 1', [rex_sql::datetime(), $this->userLogin]);
                    if ($login_tries >= self::LOGIN_TRIES_1 - 1) {
                        $time = $login_tries < self::LOGIN_TRIES_2 ? self::RELOGIN_DELAY_1 : self::RELOGIN_DELAY_2;
                        $hours = floor($time / 3600);
                        $mins = floor(($time - ($hours * 3600)) / 60);
                        $secs = $time % 60;
                        $formatted = ($hours ? $hours . 'h ' : '') . ($hours || $mins ? $mins . 'min ' : '') . $secs . 's';
                        $this->message .= ' ' . rex_i18n::rawMsg('login_wait', '<strong data-time="' . $time . '">' . $formatted . '</strong>');
                    }
                }
            }
        }

        if ($this->isLoggedOut() && '' != $userId) {
            $sql->setQuery('UPDATE ' . $this->tableName . ' SET session_id="", cookiekey="" WHERE id=? LIMIT 1', [$userId]);
            self::deleteStayLoggedInCookie();
        }

        return $check;
    }

    public static function deleteSession()
    {
        self::startSession();

        unset($_SESSION[static::getSessionNamespace()][self::SYSTEM_ID]);
        self::deleteStayLoggedInCookie();

        rex_csrf_token::removeAll();
    }

    private static function deleteStayLoggedInCookie()
    {
        rex_response::sendCookie(self::getStayLoggedInCookieName(), '');
    }

    /**
     * @return string
     */
    private static function getStayLoggedInCookieName()
    {
        return 'rex_user_' . sha1(rex::getProperty('instname'));
    }

    /**
     * @return bool
     */
    public static function hasSession()
    {
        // try to fast-fail, so we dont need to start a session in all cases (which would require a session lock...)
        if (!isset($_COOKIE[session_name()])) {
            return false;
        }
        self::startSession();

        $sessionNs = static::getSessionNamespace();
        return isset($_SESSION[$sessionNs][self::SYSTEM_ID]['UID']) && $_SESSION[$sessionNs][self::SYSTEM_ID]['UID'] > 0;
    }

    /**
     * Creates the user object if it does not already exist.
     *
     * Helpful if you want to check permissions of the backend user in frontend.
     * If you only want to know if there is any backend session, use {@link rex_backend_login::hasSession()}.
     *
     * @return rex_user|null
     */
    public static function createUser()
    {
        if (!self::hasSession()) {
            return null;
        }
        if ($user = rex::getUser()) {
            return $user;
        }

        $login = new self();
        rex::setProperty('login', $login);
        if ($login->checkLogin()) {
            $user = $login->getUser();
            rex::setProperty('user', $user);
            return $user;
        }
        return null;
    }

    /**
     * returns the backends session namespace.
     *
     * @return string
     */
    protected static function getSessionNamespace()
    {
        return rex::getProperty('instname'). '_backend';
    }
}
