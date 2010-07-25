<?php

/**
 * Klasse zur Verbindung und Interatkion mit der Datenbank
 * @version svn:$Id$
 */

class rex_sql
{
  var $values; // Werte von setValue
  var $fieldnames; // Spalten im ResultSet

  var $table; // Tabelle setzen
  var $wherevar; // WHERE Bediengung
  var $query; // letzter Query String
  var $counter; // ResultSet Cursor
  var $rows; // anzahl der treffer
  var $result; // ResultSet
  var $last_insert_id; // zuletzt angelegte auto_increment nummer
  var $debugsql; // debug schalter
  var $identifier; // Datenbankverbindung
  var $DBID; // ID der Verbindung

  var $error; // Fehlertext
  var $errno; // Fehlernummer

  /*private*/ function rex_sql($DBID = 1)
  {
    global $REX;

    $this->debugsql = false;
    $this->selectDB($DBID);

    if($REX['MYSQL_VERSION'] == '')
    {
      // ggf. Strict Mode abschalten
      $this->setQuery('SET SQL_MODE=""');

      // MySQL Version bestimmen
      $res = $this->getArray('SELECT VERSION() as VERSION');
      if(preg_match('/([0-9]+\.([0-9\.])+)/', $res[0]['VERSION'], $matches))
      {
        $REX['MYSQL_VERSION'] = $matches[1];
      }
      else
      {
        exit('Could not identifiy MySQL Version!');
      }

      // connection auf UTF8 trimmen
      if (rex_lang_is_utf8())
      {
        if(function_exists('mysql_set_charset') AND version_compare($REX['MYSQL_VERSION'], '5.0.7', '>='))
          mysql_set_charset('utf8', $this->identifier);
        else
          $this->setQuery('SET NAMES utf8');
      }
    }

    $this->flush();
  }

  /**
   * Stellt die Verbindung zur Datenbank her
   */
  /*protected*/ function selectDB($DBID)
  {
    global $REX;

    $this->DBID = $DBID;

    if($REX['DB'][$DBID]['PERSISTENT'])
      $this->identifier = @mysql_pconnect($REX['DB'][$DBID]['HOST'], $REX['DB'][$DBID]['LOGIN'], $REX['DB'][$DBID]['PSW']);
    else
      $this->identifier = @mysql_connect($REX['DB'][$DBID]['HOST'], $REX['DB'][$DBID]['LOGIN'], $REX['DB'][$DBID]['PSW']);

    if (!@mysql_select_db($REX['DB'][$DBID]['NAME'], $this->identifier))
    {
      echo "<font style='color:red; font-family:verdana,arial; font-size:11px;'>Class SQL 1.1 | Database down. | Please contact <a href=mailto:" . $REX['ERROR_EMAIL'] . ">" . $REX['ERROR_EMAIL'] . "</a>\n | Thank you!\n</font>";
      exit;
    }
    $REX['DB'][$DBID]['IDENTIFIER'] = $this->identifier;
  }

  /**
   * Gibt die DatenbankId der Abfrage (SQL) zurück,
   * oder false wenn die Abfrage keine DBID enthält
   *
   * @param $query Abfrage
   */
  /*protected static*/ function getQueryDBID($qry = null)
  {
    if(!$qry)
    {
      if(isset($this)) // Nur bei angelegtem Object
        $qry = $this->query;
      else
        return null;
    }

    $qry = trim($qry);

    if(preg_match('/\(DB([1-9]){1}\)/i', $qry, $matches))
      return $matches[1];

    return false;
  }

  /**
   * Entfernt die DBID aus einer Abfrage (SQL) und gibt die DBID zurück falls
   * vorhanden, sonst false
   *
   * @param $query Abfrage
   */
  /*protected static*/ function stripQueryDBID(&$qry)
  {
    $qry = trim($qry);

    if(($qryDBID = rex_sql::getQueryDBID($qry)) !== false)
      $qry = substr($qry, 6);

    return $qryDBID;
  }

  /**
   * Gibt den Typ der Abfrage (SQL) zurück,
   * oder false wenn die Abfrage keinen Typ enthält
   *
   * Mögliche Typen:
   * - SELECT
   * - SHOW
   * - UPDATE
   * - INSERT
   * - DELETE
   * - REPLACE
   *
   * @param $query Abfrage
   */
  /*protected*/ function getQueryType($qry = null)
  {
    if(!$qry)
    {
      if(isset($this)) // Nur bei angelegtem Object
        $qry = $this->query;
      else
        return null;
    }

    $qry = trim($qry);
    // DBID aus dem Query herausschneiden, falls vorhanden
    rex_sql::stripQueryDBID($qry);

    if(preg_match('/^(SELECT|SHOW|UPDATE|INSERT|DELETE|REPLACE)/i', $qry, $matches))
      return strtoupper($matches[1]);

    return false;
  }

  /**
   * Setzt eine Abfrage (SQL) ab, wechselt die DBID falls vorhanden
   *
   * @param $query Abfrage
   * @return boolean True wenn die Abfrage erfolgreich war (keine DB-Errors
   * auftreten), sonst false
   */
  /*public*/ function setDBQuery($qry)
  {
    if(($qryDBID = rex_sql::stripQueryDBID($qry)) !== false)
      $this->selectDB($qryDBID);

    return $this->setQuery($qry);
  }
  
  /**
   * Setzt Debugmodus an/aus
   *
   * @param $debug Debug TRUE/FALSE
   */
  /*public*/ function setDebug($debug = TRUE)
  {
	  $this->debugsql = $debug;
  }
  
  /**
   * Setzt eine Abfrage (SQL) ab
   *
   * @param $query Abfrage
   * @return boolean True wenn die Abfrage erfolgreich war (keine DB-Errors
   * auftreten), sonst false
   */
  /*public*/ function setQuery($qry)
  {
    // Alle Werte zurücksetzen
    $this->flush();

    $qry = trim($qry);
    $this->query = $qry;
    $this->result = @ mysql_query($qry, $this->identifier);

    if ($this->result)
    {
      if (($qryType = $this->getQueryType()) !== false)
      {
        switch ($qryType)
        {
          case 'SELECT' :
          case 'SHOW' :
          {
            $this->rows = mysql_num_rows($this->result);
            break;
          }
          case 'REPLACE' :
          case 'DELETE' :
          case 'UPDATE' :
          {
            $this->rows = mysql_affected_rows($this->identifier);
            break;
          }
          case 'INSERT' :
          {
            $this->rows = mysql_affected_rows($this->identifier);
            $this->last_insert_id = mysql_insert_id($this->identifier);
            break;
          }
        }
      }
    }
    else
    {
      $this->error = mysql_error($this->identifier);
      $this->errno = mysql_errno($this->identifier);
    }

    if ($this->debugsql || $this->error != '')
    {
      $this->printError($qry);
    }

    return $this->getError() === '';
  }

  /**
   * Setzt den Tabellennamen
   *
   * @param $table Tabellenname
   */
  /*public*/ function setTable($table)
  {
    $this->table = $table;
  }

  /**
   * Setzt den Wert eine Spalte
   *
   * @param $feldname Spaltenname
   * @param $wert Wert
   */
  /*public*/ function setValue($feldname, $wert)
  {
    $this->values[$feldname] = $wert;
  }

  /**
   * Setzt ein Array von Werten zugleich
   *
   * @param $valueArray Ein Array von Werten
   * @param $wert Wert
   */
  /*public*/ function setValues($valueArray)
  {
    if(is_array($valueArray))
    {
      foreach($valueArray as $name => $value)
      {
        $this->setValue($name, $value);
      }
      return true;
    }
    return false;
  }

  /**
   * Prüft den Wert einer Spalte der aktuellen Zeile ob ein Wert enthalten ist
   * @param $feld Spaltenname des zu prüfenden Feldes
   * @param $prop Wert, der enthalten sein soll
   */
  /*protected*/ function isValueOf($feld, $prop)
  {
    if ($prop == "")
    {
      return TRUE;
    }
    else
    {
      return strpos($this->getValue($feld), $prop) !== false;
    }
  }

  /**
   * Setzt die WHERE Bedienung der Abfrage
   */
  /*public*/ function setWhere($where)
  {
    $this->wherevar = "WHERE $where";
  }

  /**
   * Gibt den Wert einer Spalte im ResultSet zurück
   * @param $value Name der Spalte
   * @param [$row] Zeile aus dem ResultSet
   */
  /*public*/ function getValue($feldname, $row = null)
  {
  	if(isset($this->values[$feldname]))
  		return $this->values[$feldname];

    $_row = $this->counter;
    if (is_int($row))
    {
      $_row = $row;
    }

    $res = mysql_result($this->result, $_row, $feldname);
    if($res === false)
    {
      $sendWarnings = (error_reporting() & E_WARNING) == E_WARNING;

      if($sendWarnings && function_exists('debug_backtrace'))
      {
        $trace = debug_backtrace();
        $loc = $trace[0];
        echo '<b>Warning</b>:  mysql_result('. $feldname .'): Initial error found in file <b>'. $loc['file'] .'</b> on line <b>'. $loc['line'] .'</b><br />';
      }
    }
    return $res;
  }

  /**
   * Gibt den Wert der aktuellen Zeile im ResultSet zurueck und
   * bewegt den internen Zeiger auf die naechste Zeile 
   */
  /*public*/ function getRow($fetch_type = MYSQL_ASSOC)
  {
    return mysql_fetch_array($this->result, $fetch_type);
  }
  
  /**
   * Prüft, ob eine Spalte im Resultset vorhanden ist
   * @param $value Name der Spalte
   */
  /*public*/ function hasValue($feldname)
  {
    return in_array($feldname, $this->getFieldnames());
  }

  /**
   * Prüft, ob das Feld mit dem Namen $feldname Null ist.
   *
   * Falls das Feld nicht vorhanden ist,
   * wird Null zurückgegeben, sonst True/False
   */
  /*public*/ function isNull($feldname)
  {
    if($this->hasValue($feldname))
      return $this->getValue($feldname) === null;

    return null;
  }

  /**
   * Gibt die Anzahl der Zeilen zurück
   */
  /*public*/ function getRows()
  {
    return $this->rows;
  }

  /**
   * Gibt die Zeilennummer zurück, auf der sich gerade der
   * interne Zähler befindet
   * 
   * @deprecated since version 4.3.0
   */
  /*public*/ function getCounter()
  {
    return $this->counter;
  }

  /**
   * Gibt die Anzahl der Felder/Spalten zurück
   */
  /*public*/ function getFields()
  {
    return mysql_num_fields($this->result);
  }

  /**
   * Baut den SET bestandteil mit der
   * verfügbaren values zusammen und gibt diesen zurück
   *
   * @see setValue
   */
  /*protected*/ function buildSetQuery()
  {
    $qry = '';
    if (is_array($this->values))
    {
      foreach ($this->values as $fld_name => $value)
      {
        if ($qry != '')
        {
          $qry .= ',';
        }

        // Bei <tabelle>.<feld> Notation '.' ersetzen, da sonst `<tabelle>.<feld>` entsteht
        if(strpos($fld_name, '.') !== false)
          $fld_name = str_replace('.', '`.`', $fld_name);

        if($value === null)
          $qry .= '`' . $fld_name . '`= NULL';
        else
          $qry .= '`' . $fld_name . '`=\'' . $value .'\'';

// Da Werte via POST/GET schon mit magic_quotes escaped werden,
// brauchen wir hier nicht mehr escapen
//        $qry .= '`' . $fld_name . '`=' . $this->escape($value);
      }
    }

    return $qry;
  }

  /**
   * Setzt eine Select-Anweisung auf die angegebene Tabelle
   * mit den WHERE Parametern ab
   *
   * @see #setTable()
   * @see #setWhere()
   */
  /*public*/ function select($fields)
  {
    return $this->setQuery('SELECT '. $fields .' FROM `' . $this->table . '` '. $this->wherevar);
  }
  
  /**
   * Setzt eine Update-Anweisung auf die angegebene Tabelle
   * mit den angegebenen Werten und WHERE Parametern ab
   *
   * @see #setTable()
   * @see #setValue()
   * @see #setWhere()
   */
  /*public*/ function update($successMessage = null)
  {
    return $this->statusQuery('UPDATE `' . $this->table . '` SET ' . $this->buildSetQuery() .' '. $this->wherevar, $successMessage);
  }

  /**
   * Setzt eine Insert-Anweisung auf die angegebene Tabelle
   * mit den angegebenen Werten ab
   *
   * @see #setTable()
   * @see #setValue()
   */
  /*public*/ function insert($successMessage = null)
  {
    return $this->statusQuery('INSERT INTO `' . $this->table . '` SET ' . $this->buildSetQuery(), $successMessage);
  }

  /**
   * Setzt eine Replace-Anweisung auf die angegebene Tabelle
   * mit den angegebenen Werten ab
   *
   * @see #setTable()
   * @see #setValue()
   * @see #setWhere()
   */
  /*public*/ function replace($successMessage = null)
  {
    return $this->statusQuery('REPLACE INTO `' . $this->table . '` SET ' . $this->buildSetQuery() .' '. $this->wherevar, $successMessage);
  }

  /**
   * Setzt eine Delete-Anweisung auf die angegebene Tabelle
   * mit den angegebenen WHERE Parametern ab
   *
   * @see #setTable()
   * @see #setWhere()
   */
  /*public*/ function delete($successMessage = null)
  {
    return $this->statusQuery('DELETE FROM `' . $this->table . '` ' . $this->wherevar, $successMessage);
  }

  /**
   * Setzt den Query $query ab.
   *
   * Wenn die Variable $successMessage gefüllt ist, dann wird diese bei
   * erfolgreichem absetzen von $query zurückgegeben, sonst die MySQL
   * Fehlermeldung
   *
   * Wenn die Variable $successMessage nicht gefüllt ist, verhält sich diese
   * Methode genauso wie setQuery()
   *
   * Beispiel:
   *
   * <code>
   * $sql = rex_sql::factory();
   * $message = $sql->statusQuery(
   *    'INSERT  INTO abc SET a="ab"',
   *    'Datensatz  erfolgreich eingefügt');
   * </code>
   *
   *  anstatt von
   *
   * <code>
   * $sql = rex_sql::factory();
   * if($sql->setQuery('INSERT INTO abc SET a="ab"'))
   *   $message  = 'Datensatz erfolgreich eingefügt');
   * else
   *   $message  = $sql- >getError();
   * </code>
   */
  /*public*/ function statusQuery($query, $successMessage = null)
  {
    $res = $this->setQuery($query);
    if($successMessage)
    {
      if($res)
        return $successMessage;
      else
        return $this->getError();
    }
    return $res;
  }

  /**
   * Stellt alle Werte auf den Ursprungszustand zurück
   */
  /*public*/ function flush()
  {
    $this->flushValues();
    $this->fieldnames = array ();

    $this->table = '';
    $this->wherevar = '';
    $this->query = '';
    $this->counter = 0;
    $this->rows = 0;
    $this->result = '';
    $this->last_insert_id = '';
    $this->error = '';
    $this->errno = '';
  }

  /**
   * Stellt alle Values, die mit setValue() gesetzt wurden, zurück
   *
   * @see #setValue(), #getValue()
   */
  /*public*/ function flushValues()
  {
    $this->values = array ();
  }


  /**
   * Setzt den Cursor des Resultsets auf die nächst niedrigere Stelle
   */
  /*public*/ function previous()
  {
    $this->counter--;
  }

  /**
   * Setzt den Cursor des Resultsets auf die nächst höhere Stelle
   */
  /*public*/ function next()
  {
    $this->counter++;
  }
  
  /*
   * Prüft ob das Resultset weitere Datensätze enthält
   */
  /*public*/ function hasNext()
  {
    return $this->counter != $this->rows;
  }

  /**
   * Setzt den Cursor des Resultsets zurück zum Anfang
   */
  /*public*/ function reset()
  {
    $this->counter = 0;
  }

  /**
   * Setzt den Cursor des Resultsets aufs Ende
   */
  /*public*/ function last()
  {
    $this->counter = ($this->rows - 1);
  }
  
  /**
   * Gibt die letzte InsertId zurück
   */
  /*public*/ function getLastId()
  {
    return $this->last_insert_id;
  }

  /**
   * Lädt das komplette Resultset in ein Array und gibt dieses zurück und
   * wechselt die DBID falls vorhanden
   *
   * @param string $sql Abfrage
   * @param string $fetch_type Default: MYSQL_ASSOC; weitere: MYSQL_NUM, MYSQL_BOTH
   * @return array
   */
  /*public*/ function getDBArray($sql = '', $fetch_type = MYSQL_ASSOC)
  {
    return $this->_getArray($sql, $fetch_type, 'DBQuery');
  }

  /**
   * Lädt das komplette Resultset in ein Array und gibt dieses zurück
   *
   * @param string $sql Abfrage
   * @param string $fetch_type Default: MYSQL_ASSOC; weitere: MYSQL_NUM, MYSQL_BOTH
   * @return array
   */
  /*public*/ function getArray($sql = '', $fetch_type = MYSQL_ASSOC)
  {
    return $this->_getArray($sql, $fetch_type);
  }

  /**
   * Hilfsfunktion
   *
   * @see getArray()
   * @see getDBArray()
   * @param string $sql Abfrage
   * @param string $fetch_type MYSQL_ASSOC, MYSQL_NUM oder MYSQL_BOTH
   * @param string $qryType void oder DBQuery
   * @return array
   */
  /*private*/ function _getArray($sql, $fetch_type, $qryType = 'default')
  {
    if ($sql != '')
    {
      switch($qryType)
      {
        case 'DBQuery': $this->setDBQuery($sql); break;
        default       : $this->setQuery($sql);
      }
    }

    $data = array();
    while ($row = @ mysql_fetch_array($this->result, $fetch_type))
    {
      $data[] = $row;
    }

    return $data;
  }

  /**
   * Gibt die zuletzt aufgetretene Fehlernummer zurück
   */
  /*public*/ function getErrno()
  {
    return $this->errno;
  }

  /**
   * Gibt den zuletzt aufgetretene Fehlernummer zurück
   */
  /*public*/ function getError()
  {
    return $this->error;
  }

  /**
   * Prüft, ob ein Fehler aufgetreten ist
   */
  /*public*/ function hasError()
  {
    return $this->error != '';
  }

  /**
   * Gibt die letzte Fehlermeldung aus
   */
  /*public*/ function printError($query)
  {
    if ($this->debugsql == true)
    {
      echo '<hr />' . "\n";
      echo 'Query: ' . nl2br(htmlspecialchars($query)) . "<br />\n";

      if (strlen($this->getRows()) > 0)
      {
        echo 'Affected Rows: ' . $this->getRows() . "<br />\n";
      }
      if (strlen($this->getError()) > 0)
      {
        echo 'Error Message: ' . htmlspecialchars($this->getError()) . "<br />\n";
        echo 'Error Code: ' . $this->getErrno() . "<br />\n";
      }
    }
  }

  /**
   * Setzt eine Spalte auf den nächst möglich auto_increment Wert
   * @param $field Name der Spalte
   */
  /*public*/ function setNewId($field)
  {
    // setNewId muss neues sql Objekt verwenden, da sonst bestehende informationen im Objekt überschrieben werden
    $sql = rex_sql::factory();
    if($sql->setQuery('SELECT `' . $field . '` FROM `' . $this->table . '` ORDER BY `' . $field . '` DESC LIMIT 1'))
    {
      if ($sql->getRows() == 0)
        $id = 0;
      else
        $id = $sql->getValue($field);

      $id++;
      $this->setValue($field, $id);

      return $id;
    }

    return false;
  }

  /**
   * Gibt die Spaltennamen des ResultSets zurück
   */
  /*public*/ function getFieldnames()
  {
    if(empty($this->fieldnames))
    {
      for ($i = 0; $i < $this->getFields(); $i++)
      {
        $this->fieldnames[] = mysql_field_name($this->result, $i);
      }
    }
    return $this->fieldnames;
  }

  /**
   * Escaped den übergeben Wert für den DB Query
   *
   * @param $value den zu escapenden Wert
   * @param [$delimiter] Delimiter der verwendet wird, wenn es sich bei $value
   * um einen String handelt
   */
  /*public*/ function escape($value, $delimiter = '')
  {
    // Quote if not a number or a numeric string
    if (!is_numeric($value))
    {
      $value = $delimiter . mysql_real_escape_string($value, $this->identifier) . $delimiter;
    }
    return $value;
  }
  
  /**
   * Erstellt das CREATE TABLE Statement um die Tabelle $table 
   * der Datenbankverbindung $DBID zu erstellen.
   * 
   * @param $table string Name der Tabelle
   * @param $DBID int Id der Datenbankverbindung 
   * @return string CREATE TABLE Sql-Statement zu erstsellung der Tabelle
   */
  /*public static*/ function showCreateTable($table, $DBID=1)
  {
    $sql = rex_sql::factory($DBID);
    $create = reset($sql->getArray("SHOW CREATE TABLE `$table`"));
    $create = $create['Create Table'];
    return $create;  	
  }

  /**
   * Sucht alle Tabellen der Datenbankverbindung $DBID.
   * Falls $tablePrefix gesetzt ist, werden nur dem Prefix entsprechende Tabellen gesucht.
   * 
   * @param $DBID int Id der Datenbankverbindung 
   * @param $tablePrefix string Zu suchender Tabellennamen-Prefix 
   * @return array Ein Array von Tabellennamen
   */
  /*public static*/ function showTables($DBID=1, $tablePrefix=null)
  {
    $qry = 'SHOW TABLES';
    if($tablePrefix != null)
    {
      $tablePrefix = str_replace('_', '\_', $tablePrefix);
    	$qry .= ' LIKE "'.$tablePrefix.'%"';    	
    }

    $sql = rex_sql::factory($DBID);
    $tables = $sql->getArray($qry);
    $tables = array_map('reset', $tables);
    
    return $tables;
  }

  /**
   * Sucht Spalteninformationen der Tabelle $table der Datenbankverbindung $DBID.
   * 
   * @param $table string Name der Tabelle
   * @param $DBID int Id der Datenbankverbindung 
   * @return array Ein Array das die Metadaten enthält
   */
  /*public*/ function showColumns($table, $DBID=1)
  {
    $sql = rex_sql::factory($DBID);
    $sql->setQuery('SHOW COLUMNS FROM '.$table);

    $columns = array();
    for($i = 0; $i < $sql->getRows(); $i++)
    {
      $columns [] = array(
        'name' => $sql->getValue('Field'),
        'type' => $sql->getValue('Type'),
        'null' => $sql->getValue('Null'),
        'key' => $sql->getValue('Key'),
        'default' => $sql->getValue('Default'),
        'extra' => $sql->getValue('Extra')
      );
      $sql->next();
    }

    return $columns;
  }

  /**
   * Gibt die Serverversion zurück.
   * 
   * Die Versionsinformation ist erst bekannt,
   * nachdem der rex_sql Konstruktor einmalig erfolgreich durchlaufen wurde.
   */
  /*public static*/ function getServerVersion()
  {
    global $REX;
    return $REX['MYSQL_VERSION'];
  }
  
  /*public static*/ function factory($DBID=1, $class=null)
  {
    // keine spezielle klasse angegeben -> default klasse verwenden?
    if(!$class)
    {
      // ----- EXTENSION POINT
      $class = rex_register_extension_point('REX_SQL_CLASSNAME', 'rex_sql',
        array(
          'DBID'      => $DBID
        )
      );
    }
    
    return new $class($DBID);
  }

  /**
   * Gibt ein SQL Singelton Objekt zurück
   * 
   * @deprecated since 4.3.0
   */
  /*public*/ function getInstance($DBID=1, $deprecatedSecondParam = null)
  {
  	return rex_sql::factory($DBID);
  }

  /**
   * Gibt den Speicher wieder frei
   */
  /*public*/ function freeResult()
  {
    if(is_resource($this->result))
      mysql_free_result($this->result);
  }

  /**
   * Prueft die uebergebenen Zugangsdaten auf gueltigkeit und legt ggf. die
   * Datenbank an
   */
  /*public static*/ function checkDbConnection($host, $login, $pw, $dbname, $createDb = false)
  {
    global $I18N;

    $err_msg = true;
    $link = @ mysql_connect($host, $login, $pw);
    if (!$link)
    {
      $err_msg = $I18N->msg('setup_021');
    }
    elseif (!@ mysql_select_db($dbname, $link))
    {
      if($createDb)
      {
        mysql_query('CREATE DATABASE `'. $dbname .'`', $link);
        if(mysql_error($link) != '')
        {
          $err_msg = $I18N->msg('setup_022');
        }
      }
      else
      {
        $err_msg = $I18N->msg('setup_022');
      }
    }

    if($link)
    {
      mysql_close($link);
    }
    return $err_msg;
  }

  /**
   * Schließt die Verbindung zum DB Server
   */
  /*public static*/ function disconnect($DBID=1)
  {
    global $REX;

    // Alle Connections schließen
    if($DBID === null)
    {
      foreach($REX['DB'] as $DBID => $DBSettings)
        rex_sql::disconnect($DBID);

      return;
    }

    if(!$REX['DB'][$DBID]['PERSISTENT'] && 
       isset($REX['DB'][$DBID]['IDENTIFIER']) && 
       is_resource($REX['DB'][$DBID]['IDENTIFIER']))
    {
      $db = rex_sql::factory($DBID);

      if(rex_sql::isValid($db))
        mysql_close($db->identifier);
    }
  }

  /*public*/ function addGlobalUpdateFields($user = null)
  {
    global $REX;

    if(!$user) $user = $REX['USER']->getValue('login');

    $this->setValue('updatedate', time());
    $this->setValue('updateuser', $user);
  }

  /*public*/ function addGlobalCreateFields($user = null)
  {
    global $REX;

    if(!$user) $user = $REX['USER']->getValue('login');

    $this->setValue('createdate', time());
    $this->setValue('createuser', $user);
  }

  /*public*/ function isValid($object)
  {
    return is_object($object) && is_a($object, 'rex_sql');
  }
}