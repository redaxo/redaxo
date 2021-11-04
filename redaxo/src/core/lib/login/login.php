<?php

/**
 * @package redaxo\core\login
 */
class rex_login
{
    /**
     * @psalm-var positive-int
     *
     * @var int
     */
    protected $DB = 1;
    protected $sessionDuration;
    protected $loginQuery;
    protected $userQuery;
    protected $impersonateQuery;
    /**
     * @var string
     */
    protected $systemId = 'default';
    protected $userLogin;
    protected $userPassword;
    /**
     * @var bool
     */
    protected $logout = false;
    /**
     * @var string
     */
    protected $idColumn = 'id';
    /**
     * @var string
     */
    protected $passwordColumn = 'password';
    /**
     * @var bool
     */
    protected $cache = false;
    /**
     * @var int
     */
    protected $loginStatus = 0; // 0 = noch checken, 1 = ok, -1 = not ok
    /**
     * @var string
     */
    protected $message = '';

    /** @var rex_sql|rex_user */
    protected $user;

    /** @var rex_sql|rex_user|null */
    protected $impersonator;

    /**
     * Constructor.
     */
    public function __construct()
    {
        self::startSession();
    }

    /**
     * Setzt, ob die Ergebnisse der Login-Abfrage
     * pro Seitenaufruf gecached werden sollen.
     */
    public function setCache($status = true)
    {
        $this->cache = $status;
    }

    /**
     * Setzt die Id der zu verwendenden SQL Connection.
     */
    public function setSqlDb($DB)
    {
        $this->DB = $DB;
    }

    /**
     * Setzt eine eindeutige System Id, damit mehrere
     * Sessions auf der gleichen Domain unterschieden werden können.
     */
    public function setSystemId($systemId)
    {
        $this->systemId = $systemId;
    }

    /**
     * Setzt das Session Timeout.
     */
    public function setSessionDuration($sessionDuration)
    {
        $this->sessionDuration = $sessionDuration;
    }

    /**
     * Setzt den Login und das Password.
     */
    public function setLogin($login, $password, $isPreHashed = false)
    {
        $this->userLogin = $login;
        $this->userPassword = $isPreHashed ? $password : sha1($password);
    }

    /**
     * Markiert die aktuelle Session als ausgeloggt.
     */
    public function setLogout($logout)
    {
        $this->logout = $logout;
    }

    /**
     * Prüft, ob die aktuelle Session ausgeloggt ist.
     */
    public function isLoggedOut()
    {
        return $this->logout;
    }

    /**
     * Setzt den UserQuery.
     *
     * Dieser wird benutzt, um einen bereits eingeloggten User
     * im Verlauf seines Aufenthaltes auf der Webseite zu verifizieren
     *
     * @param string $userQuery
     */
    public function setUserQuery($userQuery)
    {
        $this->userQuery = $userQuery;
    }

    /**
     * Setzt den ImpersonateQuery.
     *
     * Dieser wird benutzt, um den User abzurufen, dessen Identität ein Admin einnehmen möchte.
     */
    public function setImpersonateQuery($impersonateQuery)
    {
        $this->impersonateQuery = $impersonateQuery;
    }

    /**
     * Setzt den LoginQuery.
     *
     * Dieser wird benutzt, um den eigentlichne Loginvorgang durchzuführen.
     * Hier wird das eingegebene Password und der Login eingesetzt.
     *
     * @param string $loginQuery
     */
    public function setLoginQuery($loginQuery)
    {
        $this->loginQuery = $loginQuery;
    }

    /**
     * Setzt den Namen der Spalte, der die User-Id enthält.
     */
    public function setIdColumn($idColumn)
    {
        $this->idColumn = $idColumn;
    }

    /**
     * Sets the password column.
     *
     * @param string $passwordColumn
     */
    public function setPasswordColumn($passwordColumn)
    {
        $this->passwordColumn = $passwordColumn;
    }

    /**
     * Setzt einen Meldungstext.
     */
    protected function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Returns the message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Prüft die mit setLogin() und setPassword() gesetzten Werte
     * anhand des LoginQueries/UserQueries und gibt den Status zurück.
     *
     * Gibt true zurück bei erfolg, sonst false
     *
     * @return bool
     */
    public function checkLogin()
    {
        // wenn logout dann header schreiben und auf error seite verweisen
        // message schreiben

        $ok = false;

        if (!$this->logout) {
            // LoginStatus: 0 = noch checken, 1 = ok, -1 = not ok

            // gecachte ausgabe erlaubt ? checkLogin schonmal ausgeführt ?
            if ($this->cache && 0 != $this->loginStatus) {
                return $this->loginStatus > 0;
            }

            if ('' != $this->userLogin) {
                // wenn login daten eingegeben dann checken
                // auf error seite verweisen und message schreiben

                $this->user = rex_sql::factory($this->DB);

                $this->user->setQuery($this->loginQuery, [':login' => $this->userLogin]);
                if (1 == $this->user->getRows() && self::passwordVerify($this->userPassword, $this->user->getValue($this->passwordColumn), true)) {
                    $ok = true;
                    self::regenerateSessionId();
                    $this->setSessionVar('UID', $this->user->getValue($this->idColumn));
                } else {
                    $this->message = rex_i18n::msg('login_error');
                }
            } elseif ('' != $this->getSessionVar('UID')) {
                // wenn kein login und kein logout dann nach sessiontime checken
                // message schreiben und falls falsch auf error verweisen

                $ok = true;
                $sessionStartStamp = (int) $this->getSessionVar('STAMP');
                if (($sessionStartStamp + $this->sessionDuration) < time()) {
                    $ok = false;
                    $this->message = rex_i18n::msg('login_session_expired');

                    rex_csrf_token::removeAll();
                }

                if ($ok && $impersonator = $this->getSessionVar('impersonator')) {
                    $this->impersonator = rex_sql::factory($this->DB);
                    $this->impersonator->setQuery($this->userQuery, [':id' => $impersonator]);

                    if (!$this->impersonator->getRows()) {
                        $ok = false;
                        $this->message = rex_i18n::msg('login_user_not_found');
                    }
                }

                if ($ok) {
                    $query = $this->impersonator && $this->impersonateQuery ? $this->impersonateQuery : $this->userQuery;
                    $this->user = rex_sql::factory($this->DB);
                    $this->user->setQuery($query, [':id' => $this->getSessionVar('UID')]);

                    if (!$this->user->getRows()) {
                        $ok = false;
                        $this->message = rex_i18n::msg('login_user_not_found');
                    }
                }
            }
        } else {
            $this->message = rex_i18n::msg('login_logged_out');

            rex_csrf_token::removeAll();
        }

        if ($ok) {
            // wenn alles ok dann REX[UID][system_id] schreiben
            $this->setSessionVar('STAMP', time());

            // each code-path which set $ok=true, must also set a UID
            $sessUid = $this->getSessionVar('UID');
            if (empty($sessUid)) {
                throw new rex_exception('Login considered successfull but no UID found');
            }
        } else {
            // wenn nicht, dann UID loeschen und error seite
            $this->setSessionVar('STAMP', '');
            $this->setSessionVar('UID', '');
            $this->setSessionVar('impersonator', null);
        }

        $this->loginStatus = $ok ? 1 : -1;

        return $ok;
    }

    public function impersonate($id)
    {
        if (!$this->user) {
            throw new RuntimeException('Can not impersonate a user without valid user session.');
        }
        if ($this->user->getValue($this->idColumn) == $id) {
            throw new RuntimeException('Can not impersonate the current user.');
        }

        $user = rex_sql::factory($this->DB);
        $user->setQuery($this->impersonateQuery ?: $this->userQuery, [':id' => $id]);

        if (!$user->getRows()) {
            throw new RuntimeException(sprintf('User with id "%d" not found.', $id));
        }

        $this->impersonator = $this->user;
        $this->user = $user;

        $this->setSessionVar('UID', $id);
        $this->setSessionVar('impersonator', $this->impersonator->getValue($this->idColumn));
    }

    public function depersonate()
    {
        if (!$this->impersonator) {
            return;
        }

        $this->user = $this->impersonator;
        $this->impersonator = null;

        $this->setSessionVar('UID', $this->user->getValue($this->idColumn));
        $this->setSessionVar('impersonator', null);
    }

    /**
     * @return rex_sql|rex_user|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return rex_sql|rex_user|null
     */
    public function getImpersonator()
    {
        return $this->impersonator;
    }

    /**
     * Gibt einen Benutzer-Spezifischen Wert zurück.
     */
    public function getValue($key, $default = null)
    {
        if ($this->user) {
            return $this->user->getValue($key);
        }

        return $default;
    }

    /**
     * Setzte eine Session-Variable.
     */
    public function setSessionVar($varname, $value)
    {
        $_SESSION[static::getSessionNamespace()][$this->systemId][$varname] = $value;
    }

    /**
     * Gibt den Wert einer Session-Variable zurück.
     */
    public function getSessionVar($varname, $default = '')
    {
        static $sessChecked = false;
        // validate session-id - once per request - to prevent fixation
        if (!$sessChecked) {
            $rexSessId = !empty($_SESSION['REX_SESSID']) ? $_SESSION['REX_SESSID'] : '';

            if (!empty($rexSessId) && $rexSessId !== session_id()) {
                // clear redaxo related session properties on a possible attack
                $_SESSION[static::getSessionNamespace()][$this->systemId] = [];
            }
            $sessChecked = true;
        }

        if (isset($_SESSION[static::getSessionNamespace()][$this->systemId][$varname])) {
            return $_SESSION[static::getSessionNamespace()][$this->systemId][$varname];
        }

        return $default;
    }

    /*
     * refresh session on permission elevation for security reasons
     */
    protected static function regenerateSessionId()
    {
        if ('' != session_id()) {
            session_regenerate_id(true);

            $cookieParams = static::getCookieParams();
            if ($cookieParams['samesite']) {
                self::rewriteSessionCookie($cookieParams['samesite']);
            }

            rex_csrf_token::removeAll();
        }

        // session-id is shared between frontend/backend or even redaxo instances per server because it's the same http session
        $_SESSION['REX_SESSID'] = session_id();
    }

    /**
     * starts a http-session if not already started.
     */
    public static function startSession()
    {
        if ('' == session_id()) {
            $cookieParams = static::getCookieParams();

            session_set_cookie_params(
                $cookieParams['lifetime'],
                $cookieParams['path'],
                $cookieParams['domain'],
                $cookieParams['secure'],
                $cookieParams['httponly']
            );

            rex_timer::measure(__METHOD__, static function () {
                error_clear_last();

                if (!@session_start()) {
                    if ($error = error_get_last()) {
                        throw new rex_exception('Unable to start session: '.$error['message']);
                    }
                    throw new rex_exception('Unable to start session.');
                }
            });

            if ($cookieParams['samesite']) {
                self::rewriteSessionCookie($cookieParams['samesite']);
            }
        }
    }

    /**
     * Einstellen der Cookie Paramter bevor die session gestartet wird.
     *
     * @return array
     */
    private static function getCookieParams()
    {
        $cookieParams = session_get_cookie_params();

        $key = rex::isBackend() ? 'backend' : 'frontend';
        $sessionConfig = rex::getProperty('session', []);

        if ($sessionConfig) {
            foreach ($sessionConfig[$key]['cookie'] as $name => $value) {
                if (null !== $value) {
                    $cookieParams[$name] = $value;
                }
            }
        }

        return $cookieParams;
    }

    /**
     * php does not natively support SameSite for cookies yet,
     * rewrite the session cookie manually.
     *
     * see https://wiki.php.net/rfc/same-site-cookie
     *
     * @param string $sameSite
     */
    private static function rewriteSessionCookie($sameSite)
    {
        $cookiesHeaders = [];

        // since header_remove() will remove all sent cookies, we need to collect all of them,
        // rewrite only the session cookie and send all cookies again.
        $cookieHeadersPrefix = 'Set-Cookie: ';
        $sessionCookiePrefix = 'Set-Cookie: '. session_name() .'=';
        foreach (headers_list() as $rawHeader) {
            // rewrite the session cookie
            if (str_starts_with($rawHeader, $sessionCookiePrefix)) {
                $rawHeader .= '; SameSite='. $sameSite;
            }
            // collect all cookies
            if (str_starts_with($rawHeader, $cookieHeadersPrefix)) {
                $cookiesHeaders[] = $rawHeader;
            }
        }

        // remove all cookies
        header_remove('Set-Cookie');

        // re-add all (inl. the rewritten session cookie)
        foreach ($cookiesHeaders as $rawHeader) {
            header($rawHeader);
        }
    }

    /**
     * Verschlüsselt den übergebnen String.
     *
     * @throws rex_exception
     *
     * @return string Returns the hashed password
     */
    public static function passwordHash($password, $isPreHashed = false)
    {
        $password = $isPreHashed ? $password : sha1($password);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        if (!is_string($hash)) {
            throw new rex_exception('error while hashing password');
        }
        return $hash;
    }

    /**
     * @return bool returns TRUE if the password and hash match, or FALSE otherwise
     */
    public static function passwordVerify($password, $hash, $isPreHashed = false)
    {
        $password = $isPreHashed ? $password : sha1($password);
        return password_verify($password, $hash);
    }

    /**
     * @return bool returns TRUE if the hash should be rehashed to match the given algo and options, or FALSE otherwise
     */
    public static function passwordNeedsRehash($hash)
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }

    /**
     * returns the current session namespace.
     *
     * @return string
     */
    protected static function getSessionNamespace()
    {
        return rex_request::getSessionNamespace();
    }
}
