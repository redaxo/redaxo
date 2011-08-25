<?php

class rex_login
{
  public
    $USER,
    $message;

  protected
    $DB,
    $session_duration,
    $login_query,
    $user_query,
    $system_id,
    $usr_login,
    $usr_psw,
    $logout,
    $uid,
    $passwordfunction,
    $cache,
    $login_status;

  public function __construct()
  {
    $this->DB = 1;
    $this->logout = false;
    $this->message = "";
    $this->system_id = "default";
    $this->cache = false;
    $this->login_status = 0; // 0 = noch checken, 1 = ok, -1 = not ok
    if (session_id() == "")
      session_start();
  }

  /**
   * Setzt, ob die Ergebnisse der Login-Abfrage
   * pro Seitenaufruf gecached werden sollen
   */
  public function setCache($status = true)
  {
    $this->cache = $status;
  }

  /**
   * Setzt die Id der zu verwendenden SQL Connection
   */
  public function setSqlDb($DB)
  {
    $this->DB = $DB;
  }

  /**
   * Setzt eine eindeutige System Id, damit mehrere
   * Sessions auf der gleichen Domain unterschieden werden können
   */
  public function setSysID($system_id)
  {
    $this->system_id = $system_id;
  }

  /**
   * Setzt das Session Timeout
   */
  public function setSessiontime($session_duration)
  {
    $this->session_duration = $session_duration;
  }

  /**
   * Setzt den Login und das Password
   */
  public function setLogin($usr_login, $usr_psw)
  {
    $this->usr_login = $usr_login;
    $this->usr_psw = $this->encryptPassword($usr_psw);
  }

  /**
   * Markiert die aktuelle Session als ausgeloggt
   */
  public function setLogout($logout)
  {
    $this->logout = $logout;
  }

  /**
   * Prüft, ob die aktuelle Session ausgeloggt ist
   */
  public function isLoggedOut()
  {
    return $this->logout;
  }

  /**
   * Setzt den UserQuery
   *
   * Dieser wird benutzt, um einen bereits eingeloggten User
   * im Verlauf seines Aufenthaltes auf der Webseite zu verifizieren
   */
  public function setUserquery($user_query)
  {
    $this->user_query = $user_query;
  }

  /**
   * Setzt den LoginQuery
   *
   * Dieser wird benutzt, um den eigentlichne Loginvorgang durchzuführen.
   * Hier wird das eingegebene Password und der Login eingesetzt.
   */
  public function setLoginquery($login_query)
  {
    $this->login_query = $login_query;
  }

  /**
   * Setzt den Namen der Spalte, der die User-Id enthält
   */
  public function setUserID($uid)
  {
    $this->uid = $uid;
  }

  /**
   * Setzt einen Meldungstext
   */
  public function setMessage($message)
  {
    $this->message = $message;
  }

  public function getMessage()
  {
    return $this->message;
  }

  /**
   * Prüft die mit setLogin() und setPassword() gesetzten Werte
   * anhand des LoginQueries/UserQueries und gibt den Status zurück
   *
   * Gibt true zurück bei erfolg, sonst false
   */
  public function checkLogin()
  {
    // wenn logout dann header schreiben und auf error seite verweisen
    // message schreiben

    $ok = false;

    if (!$this->logout)
    {
      // LoginStatus: 0 = noch checken, 1 = ok, -1 = not ok

      // checkLogin schonmal ausgeführt ? gecachte ausgabe erlaubt ?
      if ($this->cache)
      {
        if($this->login_status > 0)
          return true;
        elseif ($this->login_status < 0)
          return false;
      }


      if ($this->usr_login != '')
      {
        // wenn login daten eingegeben dann checken
        // auf error seite verweisen und message schreiben

        $this->USER = rex_sql::factory($this->DB);

        $params = array(
          ':login' => $this->usr_login,
          ':password' => $this->usr_psw
        );

        $this->USER->setQuery($this->login_query, $params);
        if ($this->USER->getRows() == 1)
        {
          $ok = true;
          $this->setSessionVar('UID', $this->USER->getValue($this->uid));
          $this->sessionFixation();
        }
        else
        {
          $this->message = rex_i18n::msg('login_error', '<strong>'. rex::getProperty('relogindelay') .'</strong>');
          $this->setSessionVar('UID', '');
        }
      }
      elseif ($this->getSessionVar('UID') != '')
      {
        // wenn kein login und kein logout dann nach sessiontime checken
        // message schreiben und falls falsch auf error verweisen

        $this->USER = rex_sql::factory($this->DB);

        $this->USER->setQuery($this->user_query, array(':id' => $this->getSessionVar('UID')));
        if ($this->USER->getRows() == 1)
        {
          if (($this->getSessionVar('STAMP') + $this->session_duration) > time())
          {
            $ok = true;
            $this->setSessionVar('UID', $this->USER->getValue($this->uid));
          }
          else
          {
            $this->message = rex_i18n::msg('login_session_expired');
          }
        }
        else
        {
          $this->message = rex_i18n::msg('login_user_not_found');
        }
      }
      else
      {
        $this->message = rex_i18n::msg('login_welcome');
        $ok = false;
      }
    }
    else
    {
      $this->message = rex_i18n::msg('login_logged_out');
      $this->setSessionVar('UID', '');
    }

    if ($ok)
    {
      // wenn alles ok dann REX[UID][system_id] schreiben
      $this->setSessionVar('STAMP', time());
    }
    else
    {
      // wenn nicht, dann UID loeschen und error seite
      $this->setSessionVar('STAMP', '');
      $this->setSessionVar('UID', '');
    }

    if ($ok)
      $this->login_status = 1;
    else
      $this->login_status = -1;

    return $ok;
  }

  public function getUser()
  {
    return $this->USER;
  }

  /**
   * Gibt einen Benutzer-Spezifischen Wert zurück
   */
  public function getValue($value, $default = NULL)
  {
    if($this->USER)
      return $this->USER->getValue($value);

    return $default;
  }

  /**
   * Setzt eine Password-Funktion
   */
  public function setPasswordFunction($pswfunc)
  {
    $this->passwordfunction = $pswfunc;
  }

  /**
   * Verschlüsselt den übergebnen String, falls eine Password-Funktion gesetzt ist.
   */
  protected function encryptPassword($psw)
  {
    if ($this->passwordfunction == "")
      return $psw;

    return call_user_func($this->passwordfunction, $psw);
  }

  /**
   * Setzte eine Session-Variable
   */
  public function setSessionVar($varname, $value)
  {
    $_SESSION[$this->system_id][$varname] = $value;
  }

  /**
   * Gibt den Wert einer Session-Variable zurück
   */
  public function getSessionVar($varname, $default = '')
  {
    if (isset ($_SESSION[$this->system_id][$varname]))
      return $_SESSION[$this->system_id][$varname];

    return $default;
  }

  /*
   * Session fixation
  */
  public function sessionFixation()
  {
    session_regenerate_id(true);
  }
}