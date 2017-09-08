<?php

/**
 * @package redaxo\core
 */
class rex_login
{
    protected $DB = 1;
    protected $sessionDuration;
    protected $loginQuery;
    protected $userQuery;
    protected $systemId = 'default';
    protected $userLogin;
    protected $userPassword;
    protected $logout = false;
    protected $idColumn = 'id';
    protected $passwordColumn = 'password';
    protected $cache = false;
    protected $loginStatus = 0; // 0 = noch checken, 1 = ok, -1 = not ok
    protected $message = '';
    protected $user;

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
    public function setSystemId($system_id)
    {
        $this->systemId = $system_id;
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
     */
    public function setUserQuery($user_query)
    {
        $this->userQuery = $user_query;
    }

    /**
     * Setzt den LoginQuery.
     *
     * Dieser wird benutzt, um den eigentlichne Loginvorgang durchzuführen.
     * Hier wird das eingegebene Password und der Login eingesetzt.
     */
    public function setLoginQuery($login_query)
    {
        $this->loginQuery = $login_query;
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
     */
    public function checkLogin()
    {
        // wenn logout dann header schreiben und auf error seite verweisen
        // message schreiben

        $ok = false;

        if (!$this->logout) {
            // LoginStatus: 0 = noch checken, 1 = ok, -1 = not ok

            // gecachte ausgabe erlaubt ? checkLogin schonmal ausgeführt ?
            if ($this->cache && $this->loginStatus != 0) {
                return $this->loginStatus > 0;
            }

            if ($this->userLogin != '') {
                // wenn login daten eingegeben dann checken
                // auf error seite verweisen und message schreiben

                $this->user = rex_sql::factory($this->DB);

                $this->user->setQuery($this->loginQuery, [':login' => $this->userLogin]);
                if ($this->user->getRows() == 1 && self::passwordVerify($this->userPassword, $this->user->getValue($this->passwordColumn), true)) {
                    $ok = true;
                    self::regenerateSessionId();
                    $this->setSessionVar('UID', $this->user->getValue($this->idColumn));
                } else {
                    $this->message = rex_i18n::msg('login_error');
                }
            } elseif ($this->getSessionVar('UID') != '') {
                // wenn kein login und kein logout dann nach sessiontime checken
                // message schreiben und falls falsch auf error verweisen

                $this->user = rex_sql::factory($this->DB);

                $this->user->setQuery($this->userQuery, [':id' => $this->getSessionVar('UID')]);
                if ($this->user->getRows() == 1) {
                    if (($this->getSessionVar('STAMP') + $this->sessionDuration) > time()) {
                        $ok = true;
                        $this->setSessionVar('UID', $this->user->getValue($this->idColumn));
                    } else {
                        $this->message = rex_i18n::msg('login_session_expired');
                    }
                } else {
                    $this->message = rex_i18n::msg('login_user_not_found');
                }
            }
        } else {
            $this->message = rex_i18n::msg('login_logged_out');
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
        }

        if ($ok) {
            $this->loginStatus = 1;
        } else {
            $this->loginStatus = -1;
        }

        return $ok;
    }

    /**
     * @return rex_user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Gibt einen Benutzer-Spezifischen Wert zurück.
     */
    public function getValue($value, $default = null)
    {
        if ($this->user) {
            return $this->user->getValue($value);
        }

        return $default;
    }

    /**
     * Setzte eine Session-Variable.
     */
    public function setSessionVar($varname, $value)
    {
        $_SESSION[rex::getProperty('instname')][$this->systemId][$varname] = $value;
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
                $_SESSION[rex::getProperty('instname')][$this->systemId] = [];
            }
            $sessChecked = true;
        }

        if (isset($_SESSION[rex::getProperty('instname')][$this->systemId][$varname])) {
            return $_SESSION[rex::getProperty('instname')][$this->systemId][$varname];
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
        }

        // session-id is shared between frontend/backend or even redaxo instances per server because it's the same http session
        $_SESSION['REX_SESSID'] = session_id();
    }

    /**
     * starts a http-session if not already started.
     */
    public static function startSession()
    {
        if (session_id() == '') {
            if (!@session_start()) {
                $error = error_get_last();
                if ($error) {
                    rex_error_handler::handleError($error['type'], $error['message'], $error['file'], $error['line']);
                } else {
                    throw new rex_exception('Unable to start session!');
                }
            }
        }
    }

    /**
     * Verschlüsselt den übergebnen String.
     */
    public static function passwordHash($password, $isPreHashed = false)
    {
        $password = $isPreHashed ? $password : sha1($password);
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function passwordVerify($password, $hash, $isPreHashed = false)
    {
        $password = $isPreHashed ? $password : sha1($password);
        return password_verify($password, $hash);
    }

    public static function passwordNeedsRehash($hash)
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }
}
