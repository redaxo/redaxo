<?php

class rex_backend_login extends rex_login
{
  private
    $tableName,
    $stayLoggedIn;

  public function __construct()
  {
    parent::__construct();

    $tableName = rex::getTablePrefix() . 'user';
    $this->setSqlDb(1);
    $this->setSysID(rex::getProperty('instname'));
    $this->useSalt();
    $this->setPasswordFunction(rex::getProperty('pswfunc'));
    $this->setSessiontime(rex::getProperty('session_duration'));
    $this->setUserID('user_id');
    $qry = 'SELECT * FROM ' . $tableName . ' WHERE status=1';
    $this->setUserquery($qry . ' AND user_id = :id');
    $this->setLoginquery($qry . ' AND login = :login AND password = :password AND lasttrydate <' . (time() - rex::getProperty('relogindelay')) . ' AND login_tries<' . rex::getProperty('maxlogins'));
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
        array_push($params, time(), session_id(), $this->usr_login);
        $sql->setQuery('UPDATE ' . $this->tableName . ' SET ' . $add . 'login_tries=0, lasttrydate=?, session_id=? WHERE login=? LIMIT 1', $params);
      }
      $this->USER = new rex_user($this->USER);
    } else {
      // fehlversuch speichern | login_tries++
      if ($this->usr_login != '') {
        $sql->setQuery('UPDATE ' . $this->tableName . ' SET login_tries=login_tries+1,session_id="",cookiekey="",lasttrydate=? WHERE login=? LIMIT 1', array(time(), $this->usr_login));
      }
    }

    if ($this->isLoggedOut() && $userId != '') {
      $sql->setQuery('UPDATE ' . $this->tableName . ' SET session_id="", cookiekey="" WHERE user_id=? LIMIT 1', array($userId));
      setcookie($cookiename, '', time() - 3600);
    }

    return $check;
  }

  public function encryptPassword($psw)
  {
    // the service side encryption of pw is only required
    // when not already encrypted by client using javascript
    if (rex_post('javascript') == '0')
      $psw = sha1($psw);
    return parent::encryptPassword($psw);
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
