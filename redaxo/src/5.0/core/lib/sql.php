<?php

/**
 * Klasse zur Verbindung und Interatkion mit der Datenbank
 * @version svn:$Id$
 */
// see http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
class rex_sql extends rex_factory implements Iterator
{
  public
    $debugsql, // debug schalter
    $counter; // pointer

  protected
    $values, // Werte von setValue
    $rawValues, // Werte von setRawValue
    $fieldnames, // Spalten im ResultSet
    $rawFieldnames,
    $tablenames, // Tabelle im ResultSet
    $lastRow, // Wert der zuletzt gefetchten zeile
    $table, // Tabelle setzen
    $wherevar, // WHERE Bediengung
    $whereParams, // WHERE parameter array
    $rows, // anzahl der treffer
    $stmt, // ResultSet
    $query, // Die Abfrage
    $params, // Die Abfrage-Parameter
    $DBID; // ID der Verbindung

  private static
    $pdo = array(); // array von datenbankverbindungen

  protected function __construct($DBID = 1)
  {
    $this->debugsql = false;
    $this->flush();
    $this->selectDB($DBID);
  }

  /**f
   * Stellt die Verbindung zur Datenbank her
   */
  protected function selectDB($DBID)
  {
    $this->DBID = $DBID;

    try
    {
      if(!isset(self::$pdo[$DBID]))
      {
        $dbconfig = rex::getProperty('db');
        $conn = self::createConnection(
          $dbconfig[$DBID]['host'],
          $dbconfig[$DBID]['name'],
          $dbconfig[$DBID]['login'],
          $dbconfig[$DBID]['password'],
          $dbconfig[$DBID]['persistent']
        );
        self::$pdo[$DBID] = $conn;

        // ggf. Strict Mode abschalten
        $this->setQuery('SET SQL_MODE=""');
        // set encoding
        $this->setQuery('SET NAMES utf8');
        $this->setQuery('SET CHARACTER SET utf8');
      }

    }
    catch(PDOException $e)
    {
      echo "<font style='color:red; font-family:verdana,arial; font-size:11px;'>Class SQL 1.1 | Database down. | Please contact <a href=mailto:" . rex::getProperty('error_email') . ">" . rex::getProperty('error_email') . "</a>\n | Thank you!\n</font>";
      exit;
    }
  }

  static protected function createConnection($host, $database, $login, $password, $persistent = false)
  {
    $dsn = 'mysql:host='. $host .';dbname='. $database;
    $options = array(
      PDO::ATTR_PERSISTENT => (boolean) $persistent,
      PDO::ATTR_FETCH_TABLE_NAMES => true,
//      PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,
//      PDO::ATTR_EMULATE_PREPARES => true,
    );

    return new PDO($dsn, $login, $password, $options);
  }

  /**
   * Gibt die DatenbankId der Abfrage (SQL) zurueck,
   * oder false wenn die Abfrage keine DBID enthaelt
   *
   * @param $query Abfrage
   */
  static protected function getQueryDBID($qry)
  {
    $qry = trim($qry);

    if(preg_match('/\(DB([1-9]){1}\)/i', $qry, $matches))
      return $matches[1];

    return false;
  }

  /**
   * Entfernt die DBID aus einer Abfrage (SQL) und gibt die DBID zurueck falls
   * vorhanden, sonst false
   *
   * @param $query Abfrage
   */
  static protected function stripQueryDBID(&$qry)
  {
    $qry = trim($qry);

    if(($qryDBID = self::getQueryDBID($qry)) !== false)
      $qry = substr($qry, 6);

    return $qryDBID;
  }

  /**
   * Gibt den Typ der Abfrage (SQL) zurueck,
   * oder false wenn die Abfrage keinen Typ enthaelt
   *
   * Moegliche Typen:
   * - SELECT
   * - SHOW
   * - UPDATE
   * - INSERT
   * - DELETE
   * - REPLACE
   *
   * @param $query Abfrage
   */
  static public function getQueryType($qry)
  {
    $qry = trim($qry);
    // DBID aus dem Query herausschneiden, falls vorhanden
    self::stripQueryDBID($qry);

    if(preg_match('/^(SELECT|SHOW|UPDATE|INSERT|DELETE|REPLACE|CREATE)/i', $qry, $matches))
      return strtoupper($matches[1]);

    return false;
  }

  /**
   * Setzt eine Abfrage (SQL) ab, wechselt die DBID falls vorhanden
   *
   * @param $query The sql-query
   * @param $params An optional array of statement parameter
   * 
   * @throws rex_sql_exception on errors
   */
  public function setDBQuery($qry, $params = array())
  {
    // save origin connection-id
    $oldDBID = $this->DBID;

    // change connection-id but only for this one query
    if(($qryDBID = self::stripQueryDBID($qry)) !== false)
      $this->selectDB($qryDBID);

    $result = $this->setQuery($qry, $params);

    // restore connection-id
    $this->DBID = $oldDBID;

    return $result;
  }

  /**
   * Setzt Debugmodus an/aus
   *
   * @param $debug Debug TRUE/FALSE
   *
   * @return rex_sql the current rex_sql object
   */
  public function setDebug($debug = TRUE)
  {
	  $this->debugsql = $debug;

	  return $this;
  }

  /**
   * Prepares a PDOStatement
   *
   * @param string $qry A query string with placeholders
   *
   * @return PDOStatement The prepared statement
   */
  public function prepareQuery($qry)
  {
    return ($this->stmt = self::$pdo[$this->DBID]->prepare($qry));
  }

  /**
   * Executes the prepared statement with the given input parameters
   * @param array $params Array of input parameters
   *
   * @return boolean True on success, False on error
   */
  public function execute($params = array())
  {
    $success = $this->stmt->execute($params);
    $this->rows = $this->stmt->rowCount();
    $this->counter = 0;
    $this->lastRow = array();
    return $success;
  }

  /**
   * Executes the given sql-query.
   *
   * If parameters will be provided, a prepared statement will be executed.
   *
   * @param $query string The sql-query
   * @param $params array An optional array of statement parameter
   * 
   * @throws rex_sql_exception on errors
   */
  public function setQuery($qry, $params = array())
  {
    // Alle Werte zuruecksetzen
    $this->flush();
    $this->query = $qry;
    $this->params = $params;

    if(!empty($params))
    {
      if(!is_array($params))
      {
        throw new rex_sql_exception('expecting $params to be an array, "'. gettype($params) .'" given!');
      }
      $this->stmt = self::$pdo[$this->DBID]->prepare(trim($qry));
      if($this->stmt)
      {
        if(!$this->execute($params))
        {
          throw new rex_sql_exception('Error occured while executing statement "'. $qry .'" using params '. json_encode($params) .'!');
        }
      }
      else
      {
        throw new rex_sql_exception('Error occured while preparing statement "'. $qry .'"!');
      }
    }
    else
    {
      $this->stmt = self::$pdo[$this->DBID]->query(trim($qry));
    }

    if($this->stmt !== false)
    {
      $this->rows = $this->stmt->rowCount();
    }
    else
    {
      $this->rows = 0;
    }

    $hasError = $this->hasError();
    if ($this->debugsql)
    {
      $this->printError($qry, $params);
    }
    else if ($hasError)
    {
      throw new rex_sql_exception($this->getError());
    }

    // Compat
    return true;
  }

  /**
   * Setzt den Tabellennamen
   *
   * @param $table Tabellenname
   * @return rex_sql the current rex_sql object
   */
  public function setTable($table)
  {
    $this->table = $table;

    return $this;
  }

  /**
   * Sets the raw value of a column
   *
   * @param string $colName Name of the column
   * @param string $value The raw value
   * @return rex_sql the current rex_sql object
   */
  public function setRawValue($colName, $value)
  {
    $this->rawValues[$colName] = $value;
    unset($this->values[$colName]);

    return $this;
  }

  /**
   * Set the value of a column
   *
   * @param $colName Name of the column
   * @param $value The value
   * @return rex_sql the current rex_sql object
   */
  public function setValue($colName, $value)
  {
    $this->values[$colName] = $value;
    unset($this->rawValues[$colName]);

    return $this;
  }

  /**
   * Setzt ein Array von Werten zugleich
   *
   * @param $valueArray Ein Array von Werten
   * @param $wert Wert
   * @return rex_sql the current rex_sql object
   */
  public function setValues(array $valueArray)
  {
    foreach($valueArray as $name => $value)
    {
      $this->setValue($name, $value);
    }

    return $this;
  }

  /**
   * Returns whether values are set inside this rex_sql object
   *
   * @return boolean True if value isset and not null, otherwise False
   */
  public function hasValues()
  {
    return !empty($this->values);
  }

  /**
   * Prueft den Wert einer Spalte der aktuellen Zeile ob ein Wert enthalten ist
   * @param $feld Spaltenname des zu pruefenden Feldes
   * @param $prop Wert, der enthalten sein soll
   */
  protected function isValueOf($feld, $prop)
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
   *
   * example 1:
   *    $sql->setWhere(array('id' => 3, 'field' => '')); // results in id = 3 AND field = ''
   *    $sql->setWhere(array(array('id' => 3, 'field' => ''))); // results in id = 3 OR field = ''
   *
   * example 2:
   *    $sql->setWhere('myid = :id OR anotherfield = :field', array('id' => 3, 'field' => ''));
   *
   * example 3 (deprecated):
   *    $sql->setWhere('myid="35" OR abc="zdf"');
   *
   * @return rex_sql the current rex_sql object
   */
  public function setWhere($where, $whereParams = NULL)
  {
    if(is_array($where))
    {
      $this->wherevar = 'WHERE '. $this->buildWhereArg($where);
      $this->whereParams = $where;
    }
    else if(is_string($where) && is_array($whereParams))
    {
      $this->wherevar = 'WHERE '. $where;
      $this->whereParams = $whereParams;
    }
    else if(is_string($where))
    {
      $trace = debug_backtrace();
      $loc = $trace[0];
      trigger_error('you have to take care to provide escaped values for your where-string in file "'. $loc['file'] .'" on line '. $loc['line'] .'!', E_USER_WARNING);

      $this->wherevar = 'WHERE '. $where;
      $this->whereParams = array();
    }
    else
    {
      throw new rex_sql_exception('expecting $where to be an array, "'. gettype($where) .'" given!');
    }

    return $this;
  }

  /**
   * Concats the given array to a sql condition using bound parameters.
   * AND/OR opartors are alternated depending on $level
   *
   * @param array $arrFields
   * @param int $level
   */
  private function buildWhereArg(array $arrFields, $level = 0)
  {
    $op = '';
    if($level % 2 == 1)
    {
      $op = ' OR ';
    }
    else
    {
      $op = ' AND ';
    }

    $qry = '';
    foreach($arrFields as $fld_name => $value)
    {
      $arg = '';
      if(is_array($value))
      {
        $arg = '('. $this->buildWhereArg($value, $level+1) .')';
      }
      else
      {
        $arg = '`' .$fld_name . '` = :'. $fld_name;
      }

      if ($qry != '')
      {
        $qry .= $op;
      }
      $qry .= $arg;
    }
    return $qry;
  }

  /**
   * Gibt den Wert einer Spalte im ResultSet zurueck
   * @param $value Name der Spalte
   * @param [$row] Zeile aus dem ResultSet
   */
  public function getValue($feldname)
  {
    if(empty($feldname))
    {
      throw new rex_sql_exception('parameter fieldname must not be empty!');
    }

    // fast fail,... value already set manually?
    if(isset($this->values[$feldname]))
      return $this->values[$feldname];

    // check if there is an table alias defined
    // if not, try to guess the tablename
    if(strpos($feldname, '.') === false)
    {
      $tables = $this->getTablenames();
      foreach($tables as $table)
      {
        if(in_array($table .'.'. $feldname, $this->rawFieldnames))
        {
          return $this->fetchValue($table .'.'. $feldname);
        }
      }
    }

    return $this->fetchValue($feldname);
  }

  protected function fetchValue($feldname)
  {
    if(isset($this->values[$feldname]))
      return $this->values[$feldname];

    if(empty($this->lastRow))
    {
      // no row fetched, but also no query was executed before
      if($this->stmt == null)
      {
        return null;
      }
      $this->lastRow = $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    $res = false;
    // isset doesn't work here, because values may also be null
    if(is_array($this->lastRow) && array_key_exists($feldname, $this->lastRow))
    {
      $res = $this->lastRow[$feldname];
    }
    else
    {
      $sendWarnings = (error_reporting() & E_WARNING) == E_WARNING;

      if($sendWarnings && function_exists('debug_backtrace'))
      {
        $trace = debug_backtrace();
        $loc = $trace[1];
        echo '<b>Warning</b>:  rex_sql->getValue('. $feldname .'): Initial error found in file <b>'. $loc['file'] .'</b> on line <b>'. $loc['line'] .'</b><br />';
        exit();
      }
    }

    return $res;
  }

  /**
   * Gibt den Wert der aktuellen Zeile im ResultSet zurueck und
   * bewegt den internen Zeiger auf die naechste Zeile
   */
  public function getRow($fetch_type = PDO::FETCH_ASSOC)
  {
    if(!$this->lastRow)
    {
      $this->lastRow = $this->stmt->fetch($fetch_type);
    }
    return $this->lastRow;
  }

  /**
   * Prueft, ob eine Spalte im Resultset vorhanden ist
   * @param $value Name der Spalte
   */
  public function hasValue($feldname)
  {
    // fast fail,... value already set manually?
    if(isset($this->values[$feldname]))
      return true;
        
    if(strpos($feldname, '.') !== false)
    {
      $parts = explode('.', $feldname);
      return in_array($parts[0], $this->getTablenames()) && in_array($parts[1], $this->getFieldnames());
    }
    return in_array($feldname, $this->getFieldnames());
  }

  /**
   * Prueft, ob das Feld mit dem Namen $feldname Null ist.
   *
   * Falls das Feld nicht vorhanden ist,
   * wird Null zurueckgegeben, sonst True/False
   */
  public function isNull($feldname)
  {
    if($this->hasValue($feldname))
      return $this->getValue($feldname) === null;

    return null;
  }

  /**
   * Gibt die Anzahl der Zeilen zurueck
   */
  public function getRows()
  {
    return $this->rows;
  }

  /**
   * Gibt die Zeilennummer zurueck, auf der sich gerade der
   * interne Zaehler befindet
   *
   * @deprecated since version 4.3.0
   */
  public function getCounter()
  {
    return $this->counter;
  }

  /**
   * Gibt die Anzahl der Felder/Spalten zurueck
   */
  public function getFields()
  {
    return $this->stmt->columnCount();
  }

  /**
   * Baut den SET bestandteil mit der
   * verfuegbaren values zusammen und gibt diesen zurueck
   *
   * @see setValue
   */
  protected function buildPreparedValues()
  {
    $qry = '';
    if (is_array($this->values))
    {
      foreach ($this->values as $fld_name => $value)
      {
        if ($qry != '')
        {
          $qry .= ', ';
        }

        $qry .= '`'. $fld_name . '` = :'. $fld_name;
      }
    }
    if(is_array($this->rawValues))
    {
      foreach($this->rawValues as $fld_name => $value)
      {
        if ($qry != '')
        {
          $qry .= ', ';
        }

        $qry .= '`'. $fld_name . '` = '. $value;
      }
    }

    if(trim($qry) == '')
    {
      // FIXME
      trigger_error('no values given to buildPreparedValues for update(), insert() or replace()', E_USER_WARNING);
    }

    return $qry;
  }

  public function getWhere()
  {
    // we have an custom where criteria, so we don't need to build one automatically
    if($this->wherevar != '')
    {
      return $this->wherevar;
    }

    return '';
  }

  /**
   * Setzt eine Select-Anweisung auf die angegebene Tabelle
   * mit den WHERE Parametern ab
   *
   * @see #setTable()
   * @see #setWhere()
   */
  public function select($fields)
  {
    return $this->setQuery(
      'SELECT '. $fields .' FROM `' . $this->table . '` '. $this->getWhere(),
      $this->whereParams
    );
  }

  /**
   * Setzt eine Update-Anweisung auf die angegebene Tabelle
   * mit den angegebenen Werten und WHERE Parametern ab
   *
   * @see #setTable()
   * @see #setValue()
   * @see #setWhere()
   */
  public function update()
  {
    return $this->setQuery(
      'UPDATE `' . $this->table . '` SET ' . $this->buildPreparedValues() .' '. $this->getWhere(),
      array_merge($this->values, $this->whereParams)
    );
  }

  /**
   * Setzt eine Insert-Anweisung auf die angegebene Tabelle
   * mit den angegebenen Werten ab
   *
   * @see #setTable()
   * @see #setValue()
   */
  public function insert()
  {
    // hold a copies of the query fields for later debug out (the class property will be reverted in setQuery())
    $tableName = $this->table;
    $values = $this->values;

    $res = $this->setQuery(
      'INSERT INTO `' . $this->table . '` SET ' . $this->buildPreparedValues(),
      $this->values
    );

    // provide debug infos, if insert is considered successfull, but no rows were inserted.
    // this happens when you violate against a NOTNULL constraint
    if($res && $this->getRows() == 0)
    {
      trigger_error('Error while inserting into table "'. $tableName .'" with values '. print_r($values, true) .'! Check your null/not-null constraints!', E_USER_ERROR);
    }

    return $res;
  }

  /**
   * Setzt eine Replace-Anweisung auf die angegebene Tabelle
   * mit den angegebenen Werten ab
   *
   * @see #setTable()
   * @see #setValue()
   * @see #setWhere()
   */
  public function replace()
  {
    return $this->setQuery(
      'REPLACE INTO `' . $this->table . '` SET ' . $this->buildPreparedValues() .' '. $this->getWhere(),
      array_merge($this->values, $this->whereParams)
    );
  }

  /**
   * Setzt eine Delete-Anweisung auf die angegebene Tabelle
   * mit den angegebenen WHERE Parametern ab
   *
   * @see #setTable()
   * @see #setWhere()
   */
  public function delete()
  {
    return $this->setQuery(
      'DELETE FROM `' . $this->table . '` '. $this->getWhere(),
      $this->whereParams
    );
  }

//   /**
//    * Setzt den Query $query ab.
//    *
//    * Wenn die Variable $successMessage gefuellt ist, dann wird diese bei
//    * erfolgreichem absetzen von $query zurueckgegeben, sonst die MySQL
//    * Fehlermeldung
//    *
//    * Wenn die Variable $successMessage nicht gefuellt ist, verhaelt sich diese
//    * Methode genauso wie setQuery()
//    *
//    * Beispiel:
//    *
//    * <code>
//    * $sql = rex_sql::factory();
//    * $message = $sql->statusQuery(
//    *    'INSERT  INTO abc SET a="ab"',
//    *    'Datensatz  erfolgreich eingefuegt');
//    * </code>
//    *
//    *  anstatt von
//    *
//    * <code>
//    * $sql = rex_sql::factory();
//    * if($sql->setQuery('INSERT INTO abc SET a="ab"'))
//    *   $message  = 'Datensatz erfolgreich eingefuegt');
//    * else
//    *   $message  = $sql- >getError();
//    * </code>
//    */
//   public function statusQuery($query, $successMessage = null)
//   {
//     $res = $this->setQuery($query);
//     if($successMessage)
//     {
//       if($res)
//         return $successMessage;
//       else
//         return $this->getError();
//     }
//     return $res;
//   }

//   public function preparedStatusQuery($query, $params, $successMessage = null)
//   {
//     $res = $this->setQuery($query, $params);
//     if($successMessage)
//     {
//       if($res)
//         return $successMessage;
//       else
//         return $this->getError();
//     }
//     return $res;
//   }

  /**
   * Stellt alle Werte auf den Ursprungszustand zurueck
   *
   * @return rex_sql the current rex_sql object
   */
  private function flush()
  {
    $this->values = array ();
    $this->rawValues = array();
    $this->whereParams = array ();
    $this->lastRow = array();
    $this->fieldnames = NULL;
    $this->rawFieldnames = NULL;
    $this->tablenames = NULL;

    $this->table = '';
    $this->wherevar = '';
    $this->counter = 0;
    $this->rows = 0;

    return $this;
  }

  /**
   * Stellt alle Values, die mit setValue() gesetzt wurden, zurueck
   *
   * @see #setValue(), #getValue()
   * @return rex_sql the current rex_sql object
   */
  public function flushValues()
  {
    $this->values = array ();
    $this->rawValues = array();

    return $this;
  }

  /*
   * Prueft ob das Resultset weitere Datensaetze enthaelt
   */
  public function hasNext()
  {
    return $this->counter != $this->rows;
  }

  /**
   * Setzt den Cursor des Resultsets zurueck zum Anfang
   *
   * @return rex_sql the current rex_sql object
   */
  public function reset()
  {
    // re-execute the statement
    if($this->stmt && $this->counter != 0)
    {
      $this->execute($this->params);
      $this->counter = 0;
    }

    return $this;
  }

  /**
   * Gibt die letzte InsertId zurueck
   */
  public function getLastId()
  {
    return self::$pdo[$this->DBID]->lastInsertId();
  }

  /**
   * Laedt das komplette Resultset in ein Array und gibt dieses zurueck und
   * wechselt die DBID falls vorhanden
   *
   * @param $query The sql-query
   * @param $params An optional array of statement parameter
   * 
   * @return array
   * 
   * @throws rex_sql_exception on errors
   */
  public function getDBArray($qry = null, $params = array())
  {
    if(!$qry)
    {
      $qry = $this->query;
      $params = $this->params;
    }
    
    self::$pdo[$this->DBID]->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, false);
    $this->setDBQuery($qry, $params); 
    self::$pdo[$this->DBID]->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, true);

    return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Laedt das komplette Resultset in ein Array und gibt dieses zurueck
   *
   * @param $query string The sql-query
   * @param $params array An optional array of statement parameter
   * 
   * @return array
   * 
   * @throws rex_sql_exception on errors
   */
  public function getArray($qry = null, $params = array())
  {
    if($qry && $qry != $this->query)
    {
      $this->setQuery($qry, $params);
    }

    // store old state
    $fetchTableNames = self::$pdo[$this->DBID]->getAttribute(PDO::ATTR_FETCH_TABLE_NAMES);
    self::$pdo[$this->DBID]->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, false);
    $array = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    // restore
    self::$pdo[$this->DBID]->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, $fetchTableNames);
    
    return $array;
  }

  /**
   * Gibt die zuletzt aufgetretene Fehlernummer zurueck
   */
  public function getErrno()
  {
    return (int) self::$pdo[$this->DBID]->errorCode();
  }

  /**
   * Gibt den zuletzt aufgetretene Fehler zurueck
   */
  public function getError()
  {
    $errorInfos = self::$pdo[$this->DBID]->errorInfo();
    // idx0   SQLSTATE error code (a five characters alphanumeric identifier defined in the ANSI SQL standard).
    // idx1   Driver-specific error code.
    // idx2   Driver-specific error message.
    return $errorInfos[2];
  }

  /**
   * Prueft, ob ein Fehler aufgetreten ist
   */
  public function hasError()
  {
    return $this->getErrno() != 0;
  }

  /**
   * Gibt die letzte Fehlermeldung aus
   */
  protected function printError($qry, $params)
  {
    echo '<hr />' . "\n";
    echo 'Query: ' . nl2br(htmlspecialchars($qry)) . "<br />\n";

    if(!empty($params))
      echo 'Params: ' . htmlspecialchars(print_r($params, true)) . "<br />\n";

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

  /**
   * Setzt eine Spalte auf den naechst moeglich auto_increment Wert
   * @param $field Name der Spalte
   */
  public function setNewId($field, $start_id = 0)
  {
    // setNewId muss neues sql Objekt verwenden, da sonst bestehende informationen im Objekt ueberschrieben werden
    $sql = rex_sql::factory();
    // TODO use prepared statement
    $sql->setQuery('SELECT `' . $field . '` FROM `' . $this->table . '` ORDER BY `' . $field . '` DESC LIMIT 1');
    if ($sql->getRows() == 0)
    {
      $id = $start_id;
    }else
    {
      $id = $sql->getValue($field);
    }
    $id++;
    $this->setValue($field, $id);

    return $id;
  }

  /**
   * Gibt die Spaltennamen des ResultSets zurueck
   */
  public function getFieldnames()
  {
    $this->fetchMeta();
    return $this->fieldnames;
  }

  public function getTablenames()
  {
    $this->fetchMeta();
    return $this->tablenames;
  }

  private function fetchMeta()
  {
    if($this->fieldnames === NULL)
    {
      $this->rawFieldnames = array();
      $this->fieldnames = array();
      $this->tablenames = array();

      for ($i = 0; $i < $this->getFields(); $i++)
      {
        $metadata = $this->stmt->getColumnMeta($i);

        // strip table-name from column
        $this->fieldnames[] = substr($metadata['name'], strlen($metadata['table'].'.'));
        $this->rawFieldnames[] = $metadata['name'];

        if(!in_array($metadata['table'], $this->tablenames))
        {
          $this->tablenames[] = $metadata['table'];
        }
      }
    }
  }

  /**
   * Escaped den uebergeben Wert fuer den DB Query
   *
   * @param $value den zu escapenden Wert
   */
  public function escape($value)
  {
    return self::$pdo[$this->DBID]->quote($value);
  }

  /**
   * Gibt ein SQL Singelton Objekt zurueck
   *
   * @deprecated since 4.3.0
   */
  static public function getInstance($DBID=1, $deprecatedSecondParam = null)
  {
    return static::factory($DBID);
  }

  /**
   * @param string $user the name of the user who created the dataset. Defaults to the current user.
   *
   * @return rex_sql the current rex_sql object
   */
  public function addGlobalUpdateFields($user = null)
  {
    if(!$user) $user = rex::getUser()->getValue('login');

    $this->setValue('updatedate', time());
    $this->setValue('updateuser', $user);

    return $this;
  }

  /**
   * @param string $user the name of the user who updated the dataset. Defaults to the current user.
   *
   * @return rex_sql the current rex_sql object
   */
  public function addGlobalCreateFields($user = null)
  {
    if(!$user) $user = rex::getUser()->getValue('login');

    $this->setValue('createdate', time());
    $this->setValue('createuser', $user);

    return $this;
  }


  // ----------------- iterator interface

  /**
   * @see http://www.php.net/manual/en/iterator.rewind.php
   */
  function rewind()
  {
    $this->reset();
  }

  /**
   * @see http://www.php.net/manual/en/iterator.current.php
   */
  function current()
  {
    return $this;
  }

  /**
   * @see http://www.php.net/manual/en/iterator.key.php
   */
  function key()
  {
    return $this->counter;
  }

  /**
   * @see http://www.php.net/manual/en/iterator.next.php
   */
  function next()
  {
    $this->counter++;
    $this->lastRow = $this->stmt->fetch();
  }

  /**
   * @see http://www.php.net/manual/en/iterator.valid.php
   */
  function valid()
  {
    return $this->hasNext();
  }
  // ----------------- /iterator interface

  /**
   * Erstellt das CREATE TABLE Statement um die Tabelle $table
   * der Datenbankverbindung $DBID zu erstellen.
   *
   * @param $table string Name der Tabelle
   * @param $DBID int Id der Datenbankverbindung
   * @return string CREATE TABLE Sql-Statement zu erstsellung der Tabelle
   */
  static public function showCreateTable($table, $DBID=1)
  {
    $sql = self::factory($DBID);
    $array = $sql->getArray('SHOW CREATE TABLE `'.$table.'`');
    return $array['Create Table'];
  }

  /**
   * Sucht alle Tabellen der Datenbankverbindung $DBID.
   * Falls $tablePrefix gesetzt ist, werden nur dem Prefix entsprechende Tabellen gesucht.
   *
   * @param $DBID int Id der Datenbankverbindung
   * @param $tablePrefix string Zu suchender Tabellennamen-Prefix
   * @return array Ein Array von Tabellennamen
   */
  static public function showTables($DBID=1, $tablePrefix=null)
  {
    $qry = 'SHOW TABLES';
    if($tablePrefix != null)
    {
      // replace LIKE wildcards
      $tablePrefix = str_replace(array('_', '%'), array('\_', '\%'), $tablePrefix);
      $qry .= ' LIKE "'.$tablePrefix.'%"';
    }

    $sql = self::factory($DBID);
    $tables = $sql->getArray($qry);
    $tables = array_map('reset', $tables);

    return $tables;
  }

  /**
   * Sucht Spalteninformationen der Tabelle $table der Datenbankverbindung $DBID.
   *
   * Beispiel fuer den Rueckgabewert:
   *
   * Array (
   *  [0] => Array (
   *    [name] => pid
   *    [type] => int(11)
   *    [null] => NO
   *    [key] => PRI
   *    [default] =>
   *    [extra] => auto_increment
   *  )
   *  [1] => Array (
   *    [name] => id
   *    [type] => int(11)
   *    [null] => NO
   *    [key] => MUL
   *    [default] =>
   *    [extra] =>
   *  )
   * )
   *
   * @param $table string Name der Tabelle
   * @param $DBID int Id der Datenbankverbindung
   * @return array Ein mehrdimensionales Array das die Metadaten enthaelt
   */
  static public function showColumns($table, $DBID=1)
  {
    $sql = self::factory($DBID);
    $sql->setQuery('SHOW COLUMNS FROM `'. $table .'`');

    $columns = array();
    foreach($sql as $col)
    {
      $columns [] = array(
        'name' => $col->getValue('Field'),
        'type' => $col->getValue('Type'),
        'null' => $col->getValue('Null'),
        'key' => $col->getValue('Key'),
        'default' => $col->getValue('Default'),
        'extra' => $col->getValue('Extra')
      );
    }

    return $columns;
  }

  /**
   * Gibt die Serverversion zurueck.
   *
   * Die Versionsinformation ist erst bekannt,
   * nachdem der rex_sql Konstruktor einmalig erfolgreich durchlaufen wurde.
   */
  static public function getServerVersion($DBID = 1)
  {
    if(!isset(self::$pdo[$DBID]))
    {
      // create connection if necessary
      $dummy = rex_sql::factory($DBID);
    }
    return self::$pdo[$DBID]->getAttribute(PDO::ATTR_SERVER_VERSION);
  }

  /**
   * Creates a rex_sql instance
   *
   * @param integer $DBID
   * @return rex_sql Returns a rex_sql instance
   */
  static public function factory($DBID=1)
  {
    $class = self::getFactoryClass();
    return new $class($DBID);
  }

  /**
   * Prueft die uebergebenen Zugangsdaten auf gueltigkeit und legt ggf. die
   * Datenbank an
   */
  static public function checkDbConnection($host, $login, $pw, $dbname, $createDb = false)
  {
    $err_msg = true;

    try
    {
      $conn = self::createConnection(
        $host,
        $dbname,
        $login,
        $pw
      );
    }
    catch (PDOException $e)
    {
      if(strpos($e->getMessage(), 'SQLSTATE[42000]') !== false)
      {
        if($createDb)
        {
          try {
            // use the "mysql" db for the connection
            $conn = self::createConnection(
              $host,
              'mysql',
              $login,
              $pw
            );
            if($conn->exec('CREATE DATABASE '. $dbname) !== 1)
            {
              // unable to create db
              $err_msg = rex_i18n::msg('setup_021');
            }
          }
          catch (PDOException $e)
          {
            // unable to find database
            $err_msg = rex_i18n::msg('setup_022');
          }
        }
        else
        {
          // unable to find database
          $err_msg = rex_i18n::msg('setup_022');
        }
      }
      else if(strpos($e->getMessage(), 'SQLSTATE[28000]') !== false)
      {
        // unable to connect
        $err_msg = rex_i18n::msg('setup_021');
      }
      else
      {
        // we didn't expected this error, so rethrow it to show it to the admin/end-user
        throw $e;
      }
    }

    // close the connection
    $conn = null;

    return  $err_msg;
  }

  static public function isValid($object)
  {
    return is_object($object) && is_a($object, 'rex_sql');
  }
}