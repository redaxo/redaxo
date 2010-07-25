<?php


/**
 * Klasse zum handling des Login/Logout-Mechanismuses
 *
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_login_sql extends rex_sql
{
  function rex_login_sql($DBID = 1)
  {
    parent::rex_sql($DBID);
  }
  
  /*protected*/ function isValueOf($feld, $prop)
  {
    if ($prop == '')
    {
      return true;
    }
    else
    {
      if ($feld == 'rights')
        return strpos($this->getValue($feld), '#' . $prop . '#') !== false;
      else
        return strpos($this->getValue($feld), $prop) !== false;
    }
  }

  /*public*/ function getUserLogin()
  {
    return $this->getValue('login');
  }

  /*public*/ function isAdmin()
  {
    return $this->isValueOf('rights', 'admin[]');
  }

  /*public*/ function hasPerm($perm)
  {
    // check ob echtes recht geprueft wird, oder nur ein shorthand fuer complexe checks gegeben wurde
    if(strpos($perm, '[') === false)
    {
      $callable = array($this, $perm);
      if(is_callable($callable))
      {
        return call_user_func($callable); 
      }
    }
    return $this->isValueOf('rights', $perm);
  }

  /*public*/ function hasCategoryPerm($category_id, $depricatedSecondParam = null)
  {
  	
  	// 1. Volle Rechte auf direkte Kategorie, csw
  	// 2. Leserechte, bei Kategorien "zwischen" main und eigener navi, aber nicht sichtbar, csr
  	// 3. Volle Rechte, wenn Kategorie unterhalb eine vollen Rechte Kat
  	
  	if(	$this->isAdmin() || 
  			$this->hasPerm('csw[0]') || 
  			$this->hasPerm('csw[' . $category_id . ']')
  		)
  		return TRUE;
  	
    if($c = OOCategory::getCategoryById($category_id))
    {
      foreach($c->getPathAsArray() as $k)
      {
        if($this->hasPerm('csw[' . $k . ']'))
          return TRUE;	
	    }
    }

    /*if(!$rw)
  	{
   		 if( $this->hasPerm('csr[' . $category_id . ']') )
   		 	return TRUE;
  	} */   
    
    return FALSE;
  }
  
	/*public*/ function hasMediaCategoryPerm($category_id)
  {
    return $this->isValueOf('rights', 'admin[]') ||
           $this->isValueOf('rights', 'media[0]') ||
           $this->isValueOf('rights', 'media[' . $category_id . ']');
  }
  
	/*public*/ function hasMediaPerm()
  {
    return $this->isValueOf('rights', 'admin[]') ||
           $this->isValueOf('rights', 'media[0]') ||
           strpos($this->getValue('rights'), '#media[') !== false || 
           $this->isValueOf('rights', 'mediapool[]');
  }
  
  /*public*/ function hasStructurePerm()
  {
    return $this->isValueOf('rights', 'admin[]') || 
           strpos($this->getValue("rights"), "#csw[") !== false /*||
           strpos($this->getValue("rights"), "#csr[") !== false*/;
  }

  /*public*/ function getMountpoints()
  {
    // csw[0] = alle kategorien, daher kein mountpoint
		preg_match_all('|\#csw\[([1-9]+[0-9]*)\]+|U', $this->getValue("rights"), $return, PREG_PATTERN_ORDER);
		return $return[1];
  }
  
  /*public*/ function hasMountpoints()
  {
  	if($this->isValueOf('rights', 'csw[0]') || $this->isValueOf('rights', 'admin[]'))
  		return false;
  	if(count($this->getMountpoints())>0)
  		return true;
    return false;
  }
  
  /*public*/ function getClangPerm()
  {
    global $REX;
    if($this->isValueOf('rights', 'admin[]'))
      return array_keys($REX['CLANG']);
    preg_match_all('|\#clang\[([0-9]*)\]+|U', $this->getValue("rights"), $result, PREG_PATTERN_ORDER);
    $clangs = array();
    foreach($result[1] as $clang_id)
    {
      if(isset($REX['CLANG'][$clang_id]))
        $clangs[] = $clang_id;
    }
    return $clangs;
  }

  /*public*/ function getPermAsArray($perm)
  {
    preg_match_all('|#'. preg_quote($perm, '|') .'\[([^\]]*)\]+|', $this->getValue("rights"), $return, PREG_PATTERN_ORDER);
    return $return[1];
  }
  
  /**
   * Gibt eine SQL Where Bedingung zurück, die eine Abfrage auf die rex_article Tabelle so
   * begrenzt, sodas nur Datensätze zurückgegeben werden auf die der User rechte hat.
   */
  /*public*/ function getCategoryPermAsSql()
  {
    global $REX;
    
    $whereCond = '';
    
    if( $this->isAdmin() || 
        $this->hasPerm('csw[0]'))
    {
      // vollzugriff ueberall
      $whereCond = '1=1';
    }
    else
    {
      $whereCond = '1=0';
      $categoryPerms = $REX['USER']->getPermAsArray('csw');
      foreach($categoryPerms as $catPerm)
      {
        $whereCond .= ' OR path LIKE "%|'. $catPerm .'|%"';
      }
    }

    return '('. $whereCond .')';
  }
  
  /*public*/ function removePerm($perm)
  {
    $rights = preg_replace('|#'. preg_quote($perm, '|') .'\[([^\]]*)\]+|' , '', $this->getValue("rights"));
    return $rights;
  }
}

class rex_login
{
  var $DB;
  var $session_duration;
  var $login_query;
  var $user_query;
  var $system_id;
  var $usr_login;
  var $usr_psw;
  var $logout;
  var $message;
  var $uid;
  var $USER;
  var $passwordfunction;
  var $cache;
  var $login_status;

  /*public*/ function rex_login()
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
  /*public*/ function setCache($status = true)
  {
    $this->cache = $status;
  }

  /**
   * Setzt die Id der zu verwendenden SQL Connection
   */
  /*public*/ function setSqlDb($DB)
  {
    $this->DB = $DB;
  }

  /**
   * Setzt eine eindeutige System Id, damit mehrere
   * Sessions auf der gleichen Domain unterschieden werden können
   */
  /*public*/ function setSysID($system_id)
  {
    $this->system_id = $system_id;
  }

  /**
   * Setzt das Session Timeout
   */
  /*public*/ function setSessiontime($session_duration)
  {
    $this->session_duration = $session_duration;
  }

  /**
   * Setzt den Login und das Password
   */
  /*public*/ function setLogin($usr_login, $usr_psw)
  {
    $this->usr_login = $usr_login;
    $this->usr_psw = $this->encryptPassword($usr_psw);
  }

  /**
   * Markiert die aktuelle Session als ausgeloggt
   */
  /*public*/ function setLogout($logout)
  {
    $this->logout = $logout;
  }

  /**
   * Prüft, ob die aktuelle Session ausgeloggt ist
   */
  /*public*/ function isLoggedOut()
  {
    return $this->logout;
  }

  /**
   * Setzt den UserQuery
   *
   * Dieser wird benutzt, um einen bereits eingeloggten User
   * im Verlauf seines Aufenthaltes auf der Webseite zu verifizieren
   */
  /*public*/ function setUserquery($user_query)
  {
    $this->user_query = $user_query;
  }

  /**
   * Setzt den LoginQuery
   *
   * Dieser wird benutzt, um den eigentlichne Loginvorgang durchzuführen.
   * Hier wird das eingegebene Password und der Login eingesetzt.
   */
  /*public*/ function setLoginquery($login_query)
  {
    $this->login_query = $login_query;
  }

  /**
   * Setzt den Namen der Spalte, der die User-Id enthält
   */
  /*public*/ function setUserID($uid)
  {
    $this->uid = $uid;
  }

  /**
   * Setzt einen Meldungstext
   */
  /*public*/ function setMessage($message)
  {
    $this->message = $message;
  }

  /**
   * Prüft die mit setLogin() und setPassword() gesetzten Werte
   * anhand des LoginQueries/UserQueries und gibt den Status zurück
   *
   * Gibt true zurück bei erfolg, sonst false
   */
  /*public*/ function checkLogin()
  {
    global $REX, $I18N;

    if (!is_object($I18N)) $I18N = rex_create_lang();

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

        $this->USER = new rex_login_sql($this->DB);
        $USR_LOGIN = $this->usr_login;
        $USR_PSW = $this->usr_psw;

        $query = str_replace('USR_LOGIN', $this->usr_login, $this->login_query);
        $query = str_replace('USR_PSW', $this->usr_psw, $query);

        $this->USER->setQuery($query);
        if ($this->USER->getRows() == 1)
        {
          $ok = true;
          $this->setSessionVar('UID', $this->USER->getValue($this->uid));
          $this->sessionFixation();
        }
        else
        {
          $this->message = $I18N->msg('login_error', '<strong>'. $REX['RELOGINDELAY'] .'</strong>');
          $this->setSessionVar('UID', '');
        }

      }
      elseif ($this->getSessionVar('UID') != '')
      {
        // wenn kein login und kein logout dann nach sessiontime checken
        // message schreiben und falls falsch auf error verweisen

        $this->USER = new rex_login_sql($this->DB);
        $query = str_replace('USR_UID', $this->getSessionVar('UID'), $this->user_query);

        $this->USER->setQuery($query);
        if ($this->USER->getRows() == 1)
        {
          if (($this->getSessionVar('STAMP') + $this->session_duration) > time())
          {
            $ok = true;
            $this->setSessionVar('UID', $this->USER->getValue($this->uid));
          }
          else
          {
	          $this->message = $I18N->msg('login_session_expired');
          }
        }
        else
        {
          $this->message = $I18N->msg('login_user_not_found');
        }
      }
      else
      {
        $this->message = $I18N->msg('login_welcome');
        $ok = false;
      }
    }
    else
    {
      $this->message = $I18N->msg('login_logged_out');
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

  /**
   * Gibt einen Benutzer-Spezifischen Wert zurück
   */
  /*public*/ function getValue($value, $default = NULL)
  {
  	if($this->USER)
    	return $this->USER->getValue($value);
    	
  	return $default;
  }

  /**
   * Setzt eine Password-Funktion
   */
  /*public*/ function setPasswordFunction($pswfunc)
  {
    $this->passwordfunction = $pswfunc;
  }

  /**
   * Verschlüsselt den übergebnen String, falls eine Password-Funktion gesetzt ist.
   */
  /*protected*/ function encryptPassword($psw)
  {
    if ($this->passwordfunction == "")
      return $psw;

    return call_user_func($this->passwordfunction, $psw);
  }

  /**
   * Setzte eine Session-Variable
   */
  /*public*/ function setSessionVar($varname, $value)
  {
    $_SESSION[$this->system_id][$varname] = $value;
  }

  /**
   * Gibt den Wert einer Session-Variable zurück
   */
  /*public*/ function getSessionVar($varname, $default = '')
  {
    if (isset ($_SESSION[$this->system_id][$varname]))
      return $_SESSION[$this->system_id][$varname];

    return $default;
  }

  /*
   * Session fixation
   */
  /*public*/ function sessionFixation()
  {
    // 1. parameter ist erst seit php5.1 verfügbar
    if (version_compare(phpversion(), '5.1.0', '>=') == 1)
    {
      session_regenerate_id(true);
    }
    else if (function_exists('session_regenerate_id'))
    {
      session_regenerate_id();
    }
  }
}

class rex_backend_login extends rex_login
{
  var $tableName;

  /*public*/ function rex_backend_login($tableName)
  {
    global $REX;

    parent::rex_login();

    $this->setSqlDb(1);
    $this->setSysID($REX['INSTNAME']);
    $this->setSessiontime($REX['SESSION_DURATION']);
    $this->setUserID($tableName .'.user_id');
    $this->setUserquery('SELECT * FROM '. $tableName .' WHERE status=1 AND user_id = "USR_UID"');
    $this->setLoginquery('SELECT * FROM '.$tableName .' WHERE status=1 AND login = "USR_LOGIN" AND psw = "USR_PSW" AND lasttrydate <'. (time()-$REX['RELOGINDELAY']).' AND login_tries<'.$REX['MAXLOGINS']);
    $this->tableName = $tableName;
  }

  /*public*/ function checkLogin()
  {
    global $REX;

    $fvs = rex_sql::factory();
    // $fvs->debugsql = true;
    $userId = $this->getSessionVar('UID');
    $check = parent::checkLogin();

    if($check)
    {
      // gelungenen versuch speichern | login_tries = 0
      if($this->usr_login != '')
      {
        $this->sessionFixation();
        $fvs->setQuery('UPDATE '.$this->tableName.' SET login_tries=0, lasttrydate='.time().', session_id="'. session_id() .'" WHERE login="'. $this->usr_login .'" LIMIT 1');
        
        if($fvs->hasError())
          return $fvs->getError();
      }
    }
    else
    {
      // fehlversuch speichern | login_tries++
      if($this->usr_login != '')
      {
        $fvs->setQuery('UPDATE '.$this->tableName.' SET login_tries=login_tries+1,session_id="",lasttrydate='.time().' WHERE login="'. $this->usr_login .'" LIMIT 1');
        
        if($fvs->hasError())
          return $fvs->getError();
      }
    }

    if ($this->isLoggedOut() && $userId != '')
    {
      $fvs->setQuery('UPDATE '.$this->tableName.' SET session_id="" WHERE user_id="'. $userId .'" LIMIT 1');
    }

    if($fvs->hasError())
      return $fvs->getError();

    return $check;
  }
  
  /*public*/ function getLanguage()
	{
	  global $REX;
	  
		if (preg_match_all('@#be_lang\[([^\]]*)\]#@' , $this->getValue("rights"), $matches))
    {
      foreach ($matches[1] as $match)
      {
        return $match;
      }
    }
    return $REX['LANG'];
	}

	/*public*/ function getStartpage()
	{
	  global $REX;
	  
  	if (preg_match_all('@#startpage\[([^\]]*)\]#@' , $this->getValue("rights"), $matches))
  	{
    	foreach ($matches[1] as $match)
    	{
      	return $match;
    	}
  	}
  	return $REX['START_PAGE'];
	}
}

/**
 * Prüft, ob der aktuelle Benutzer im Backend eingeloggt ist.
 * 
 * Diese Funktion kann auch aus dem Frontend heraus verwendet werden.
 */
function rex_hasBackendSession()
{
  global $REX;
  
  if(!isset($_SESSION))
    return false;
    
  if(!isset($REX))
    return false;
    
  if(!isset($REX['INSTNAME']))
    return false;
    
  if(!isset($_SESSION[$REX['INSTNAME']]))
    return false;
    
  return $_SESSION[$REX['INSTNAME']]['UID'] > 0;
}