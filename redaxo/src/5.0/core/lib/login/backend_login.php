<?php

class rex_backend_login extends rex_login
{
  private $tableName;

  public function __construct()
  {
    parent::__construct();

    $tableName = rex::getTablePrefix().'user';
    $this->setSqlDb(1);
    $this->setSysID(rex::getProperty('instname'));
    $this->setSessiontime(rex::getProperty('session_duration'));
    $this->setUserID('user_id');
    $qry = 'SELECT * FROM '. $tableName .' WHERE status=1';
    $this->setUserquery($qry .' AND user_id = :id');
    $this->setLoginquery($qry .' AND login = :login AND password = :password AND lasttrydate <'. (time()-rex::getProperty('relogindelay')).' AND login_tries<'.rex::getProperty('maxlogins'));
    $this->tableName = $tableName;
  }

  public function checkLogin()
  {
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
      }
      $this->USER = new rex_user($this->USER);
    }
    else
    {
      // fehlversuch speichern | login_tries++
      if($this->usr_login != '')
      {
        $fvs->setQuery('UPDATE '.$this->tableName.' SET login_tries=login_tries+1,session_id="",lasttrydate='.time().' WHERE login="'. $this->usr_login .'" LIMIT 1');
      }
    }

    if ($this->isLoggedOut() && $userId != '')
    {
      $fvs->setQuery('UPDATE '.$this->tableName.' SET session_id="" WHERE user_id="'. $userId .'" LIMIT 1');
    }

    return $check;
  }
}