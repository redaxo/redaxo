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

    private const SESSION_PASSWORD_CHANGE_REQUIRED = 'password_change_required';

    /** @var string */
    private $tableName;
    /** @var bool|null */
    private $stayLoggedIn;

    /** @var rex_backend_password_policy */
    private $passwordPolicy;

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
        $this->passwordPolicy = rex_backend_password_policy::factory();

        $loginPolicy = $this->getLoginPolicy();

        // XXX because with concat the time into the sql query, users of this class should use checkLogin() immediately after creating the object.
        $qry .= ' WHERE
            status = 1
            AND login = :login
            AND login_tries < '. $loginPolicy->getMaxTriesUntilBlock() .'
            AND (
                login_tries < ' . $loginPolicy->getMaxTriesUntilDelay() . '
                OR
                login_tries >= ' . $loginPolicy->getMaxTriesUntilDelay() . ' AND lasttrydate < "' . rex_sql::datetime(time() - $loginPolicy->getReloginDelay()) . '"
            )';

        if ($blockAccountAfter = $this->passwordPolicy->getBlockAccountAfter()) {
            $datetime = (new DateTimeImmutable())->sub($blockAccountAfter);
            $qry .= ' AND password_changed > "'.$datetime->format(rex_sql::FORMAT_DATETIME).'"';
        }

        $this->setLoginQuery($qry);

        $this->tableName = $tableName;
    }

    /**
     * @param bool $stayLoggedIn
     * @return void
     */
    public function setStayLoggedIn($stayLoggedIn = false)
    {
        if (!$this->getLoginPolicy()->isStayLoggedInEnabled()) {
            $stayLoggedIn = false;
        }

        $this->stayLoggedIn = $stayLoggedIn;
    }

    public function checkLogin()
    {
        $sql = rex_sql::factory();
        $userId = $this->getSessionVar(rex_login::SESSION_USER_ID);
        $cookiename = self::getStayLoggedInCookieName();
        $loggedInViaCookie = false;

        if ($cookiekey = rex_cookie($cookiename, 'string')) {
            if (!$userId) {
                $sql->setQuery('SELECT id, password FROM ' . rex::getTable('user') . ' WHERE cookiekey = ? LIMIT 1', [$cookiekey]);
                if (1 == $sql->getRows()) {
                    $this->setSessionVar(rex_login::SESSION_USER_ID, $sql->getValue('id'));
                    $this->setSessionVar(rex_login::SESSION_PASSWORD, $sql->getValue('password'));
                    self::setStayLoggedInCookie($cookiekey);
                    $loggedInViaCookie = true;
                } else {
                    self::deleteStayLoggedInCookie();
                }
            }
            $this->setSessionVar(rex_login::SESSION_LAST_ACTIVITY, time());
        }

        $check = parent::checkLogin();

        if ($check) {
            // gelungenen versuch speichern | login_tries = 0
            if ('' != $this->userLogin || !$userId) {
                self::regenerateSessionId();
                $params = [];
                $add = '';
                if ($this->stayLoggedIn || $cookiekey) {
                    $cookiekey = (string) $this->user->getValue('cookiekey');
                    if (!$cookiekey) {
                        $cookiekey = sha1($this->systemId . time() . $this->userLogin);
                        $add = 'cookiekey = ?, ';
                        $params[] = $cookiekey;
                    }
                    self::setStayLoggedInCookie($cookiekey);
                }
                if (self::passwordNeedsRehash($this->user->getValue('password'))) {
                    $add .= 'password = ?, ';
                    $params[] = self::passwordHash($this->userPassword, true);
                }
                array_push($params, rex_sql::datetime(), rex_sql::datetime(), session_id(), $this->userLogin);
                $sql->setQuery('UPDATE ' . $this->tableName . ' SET ' . $add . 'login_tries=0, lasttrydate=?, lastlogin=?, session_id=? WHERE login=? LIMIT 1', $params);
            }

            assert($this->user instanceof rex_sql);
            $this->user = rex_user::fromSql($this->user);

            if ($this->impersonator instanceof rex_sql) {
                $this->impersonator = rex_user::fromSql($this->impersonator);
            }

            if ($loggedInViaCookie || $this->userLogin) {
                if ($this->user->getValue('password_change_required')) {
                    $this->setSessionVar(self::SESSION_PASSWORD_CHANGE_REQUIRED, true);
                } elseif ($forceRenewAfter = $this->passwordPolicy->getForceRenewAfter()) {
                    $datetime = (new DateTimeImmutable())->sub($forceRenewAfter);
                    if (strtotime($this->user->getValue('password_changed')) < $datetime->getTimestamp()) {
                        $this->setSessionVar(self::SESSION_PASSWORD_CHANGE_REQUIRED, true);
                    }
                }
            }
        } else {
            // fehlversuch speichern | login_tries++
            if ('' != $this->userLogin) {
                $sql->setQuery('SELECT login_tries FROM ' . $this->tableName . ' WHERE login=? LIMIT 1', [$this->userLogin]);
                if ($sql->getRows() > 0) {
                    $loginPolify = $this->getLoginPolicy();

                    $loginTries = $sql->getValue('login_tries');
                    $this->increaseLoginTries();
                    if ($loginTries >= $loginPolify->getMaxTriesUntilDelay() - 1) {
                        $time = $loginPolify->getReloginDelay();
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
            $sql->setQuery('UPDATE ' . $this->tableName . ' SET session_id="" WHERE id=? LIMIT 1', [$userId]);
            self::deleteStayLoggedInCookie();
        }

        return $check;
    }

    public function increaseLoginTries(): void
    {
        $sql = rex_sql::factory();
        $sql->setQuery('UPDATE ' . $this->tableName . ' SET login_tries=login_tries+1,session_id="",lasttrydate=? WHERE login=? LIMIT 1', [rex_sql::datetime(), $this->userLogin]);
    }

    public function requiresPasswordChange(): bool
    {
        return (bool) $this->getSessionVar(self::SESSION_PASSWORD_CHANGE_REQUIRED, false);
    }

    /**
     * @param null|string $passwordHash Passing `null` or ommitting this param is DEPRECATED
     */
    public function changedPassword(
        #[\SensitiveParameter]
        ?string $passwordHash = null
    ): void {
        $this->setSessionVar(self::SESSION_PASSWORD_CHANGE_REQUIRED, false);

        if (null !== $passwordHash) {
            parent::changedPassword($passwordHash);
        }
    }

    /**
     * @return void
     */
    public static function deleteSession()
    {
        self::startSession();

        unset($_SESSION[static::getSessionNamespace()][self::SYSTEM_ID]);
        self::deleteStayLoggedInCookie();

        rex_csrf_token::removeAll();
    }

    private static function setStayLoggedInCookie(string $cookiekey): void
    {
        $sessionConfig = rex::getProperty('session', [])['backend']['cookie'] ?? [];

        rex_response::sendCookie(self::getStayLoggedInCookieName(), $cookiekey, [
            'expires' => strtotime('+1 year'),
            'secure' => $sessionConfig['secure'] ?? false,
            'samesite' => $sessionConfig['samesite'] ?? 'lax',
        ]);
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

        return ($_SESSION[static::getSessionNamespace()][self::SYSTEM_ID][rex_login::SESSION_USER_ID] ?? 0) > 0;
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

    public function getLoginPolicy(): rex_login_policy
    {
        $loginPolicy = (array) rex::getProperty('backend_login_policy', []);

        return new rex_login_policy($loginPolicy);
    }
}
