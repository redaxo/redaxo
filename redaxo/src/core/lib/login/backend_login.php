<?php

class rex_backend_login extends rex_login
{
  const
    LOGIN_TRIES_1   = 3,
    RELOGIN_DELAY_1 = 5,    // relogin delay after LOGIN_TRIES_1 tries
    LOGIN_TRIES_2   = 50,
    RELOGIN_DELAY_2 = 3600; // relogin delay after LOGIN_TRIES_2 tries

  private
    $tableName,
    $stayLoggedIn;

  public function __construct()
  {
    parent::__construct();

    $tableName = rex::getTablePrefix() . 'user';
    $this->setSqlDb(1);
    $this->setSysID(rex::getProperty('instname'));
    $this->setSessiontime(rex::getProperty('session_duration'));
    $this->setUserID('user_id');
    $qry = 'SELECT * FROM ' . $tableName . ' WHERE status=1';
    $this->setUserquery($qry . ' AND user_id = :id');
    $this->setLoginquery($qry . '
      AND login = :login
      AND (login_tries < ' . self::LOGIN_TRIES_1 . '
        OR login_tries < ' . self::LOGIN_TRIES_2 . ' AND lasttrydate < ' . (time() - self::RELOGIN_DELAY_1) . '
        OR lasttrydate < ' . (time() - self::RELOGIN_DELAY_2) . '
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
    $cookiename = 'rex_user_' . sha1(rex::getProperty('instname'));

    if ($cookiekey = rex_cookie($cookiename, 'string')) {
      if (!$userId) {
        $sql->setQuery('SELECT user_id FROM ' . rex::getTable('user') . ' WHERE cookiekey = ? LIMIT 1', array($cookiekey));
        if ($sql->getRows() == 1) {
          $this->setSessionVar('UID', $sql->getValue('user_id'));
          setcookie($cookiename, $cookiekey, time() + 60 * 60 * 24 * 365);
        } else {
          setcookie($cookiename, '', time() - 3600);
        }
      }
      $this->setSessionVar('STAMP', time());
    }

    $check = parent::checkLogin();

    if ($check) {
      // gelungenen versuch speichern | login_tries = 0
      if ($this->usr_login != '' || !$userId) {
        $this->sessionFixation();
        $params = array();
        $add = '';
        if ($this->stayLoggedIn || $cookiekey) {
          $cookiekey = $this->USER->getValue('cookiekey') ?: sha1($this->system_id . time() . $this->usr_login);
          $add = 'cookiekey = ?, ';
          $params[] = $cookiekey;
          setcookie($cookiename, $cookiekey, time() + 60 * 60 * 24 * 365);
        }
        if (self::passwordNeedsRehash($this->USER->getValue('password'))) {
          $add .= 'password = ?, ';
          $params[] = self::passwordHash($this->usr_psw, true);
        }
        array_push($params, time(), session_id(), $this->usr_login);
        $sql->setQuery('UPDATE ' . $this->tableName . ' SET ' . $add . 'login_tries=0, lasttrydate=?, session_id=? WHERE login=? LIMIT 1', $params);
      }
      $this->USER = new rex_user($this->USER);
    } else {
      // fehlversuch speichern | login_tries++
      if ($this->usr_login != '') {
        $sql->setQuery('SELECT login_tries FROM ' . $this->tableName . ' WHERE login=? LIMIT 1', array($this->usr_login));
        if ($sql->getRows() > 0) {
          $login_tries = $sql->getValue('login_tries');
          $sql->setQuery('UPDATE ' . $this->tableName . ' SET login_tries=login_tries+1,session_id="",cookiekey="",lasttrydate=? WHERE login=? LIMIT 1', array(time(), $this->usr_login));
          if ($login_tries >= self::LOGIN_TRIES_1 - 1) {
            $time = $login_tries < self::LOGIN_TRIES_2 ? self::RELOGIN_DELAY_1 : self::RELOGIN_DELAY_2;
            $hours = floor($time / 3600);
            $mins  = floor(($time - ($hours * 3600)) / 60);
            $secs  = $time % 60;
            $formatted = ($hours ? $hours . 'h ' : '') . ($hours || $mins ? $mins . 'min ' : '') . $secs . 's';
            $this->message .= ' ' . rex_i18n::msg('login_wait', '<strong data-time="' . $time . '">' . $formatted . '</strong>');
          }
        }
      }
    }

    if ($this->isLoggedOut() && $userId != '') {
      $sql->setQuery('UPDATE ' . $this->tableName . ' SET session_id="", cookiekey="" WHERE user_id=? LIMIT 1', array($userId));
      setcookie($cookiename, '', time() - 3600);
    }

    return $check;
  }

  static public function deleteSession()
  {
    if (session_id() == '')
      session_start();

    unset($_SESSION[rex::getProperty('instname')]);
    setcookie('rex_user_' . sha1(rex::getProperty('instname')), '', time() - 3600);
  }

  static public function hasSession()
  {
    if (session_id() == '')
      session_start();

    $instname = rex::getProperty('instname');

    return isset($_SESSION[$instname]['UID']) && $_SESSION[$instname]['UID'] > 0;
  }

  /**
   * Creates the user object if it does not already exist
   *
   * Helpful if you want to check permissions of the backend user in frontend.
   * If you only want to know if there is any backend session, use {@link rex_backend_login::hasSession()}.
   *
   * @return rex_user
   */
  static public function createUser()
  {
    if (!self::hasSession()) {
      return null;
    }
    if ($user = rex::getUser()) {
      return $user;
    }

    $login = new self;
    rex::setProperty('login', $login);
    if ($login->checkLogin()) {
      $user = $login->getUser();
      rex::setProperty('user', $user);
      return $user;
    }
    return null;
  }
}
