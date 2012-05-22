<?php

/**
 * Class to execute a sql dump
 *
 * @package redaxo5
 */
class rex_sql_util
{
  /**
  * Allgemeine funktion die eine Datenbankspalte fortlaufend durchnummeriert.
  * Dies ist z.B. nützlich beim Umgang mit einer Prioritäts-Spalte
  *
  * @param $tableName String Name der Datenbanktabelle
  * @param $priorColumnName Name der Spalte in der Tabelle, in der die Priorität (Integer) gespeichert wird
  * @param $whereCondition Where-Bedingung zur Einschränkung des ResultSets
  * @param $orderBy Sortierung des ResultSets
  * @param $id_field Name des Primaerschluessels der Tabelle
  * @param $startBy Startpriorität
  */
  static public function organizePriorities($tableName, $priorColumnName, $whereCondition = '', $orderBy = '', $id_field='id', $startBy = 1)
  {
    // Datenbankvariable initialisieren
    $qry = 'SET @count='. ($startBy - 1);
    $sql = rex_sql::factory();
    $sql->setQuery($qry);

    // Spalte updaten
    $qry = 'UPDATE '. $tableName .' SET '. $priorColumnName .' = ( SELECT @count := @count +1 )';

    if($whereCondition != '')
    $qry .= ' WHERE '. $whereCondition;

    if($orderBy != '')
    $qry .= ' ORDER BY '. $orderBy;

    $sql->setQuery($qry);
  }

  /**
   * Importiert die gegebene SQL-Datei in die Datenbank
   *
   * @return true bei Erfolg, sonst eine Fehlermeldung
   */
  static public function importDump($file, $debug = false)
  {
    $sql = rex_sql::factory();
    $sql->debugsql = $debug;
    $error = '';

    foreach (self::readSqlDump($file) as $query)
    {
      try {
        $sql->setQuery(self::prepareQuery($query));
      } catch (rex_sql_exception $e) {
        $error .= $e->getMessage()."\n<br />";
      }
    }

    return $error == '' ? true : $error;
  }

  static private function prepareQuery($qry)
  {
    // rex::getUser() gibts im Setup nicht
    if(rex::getUser())
      $qry = str_replace('%USER%', rex::getUser()->getValue('login'), $qry);

    $qry = str_replace('%TIME%', time(), $qry);
    $qry = str_replace('%TABLE_PREFIX%', rex::getTablePrefix(), $qry);
    $qry = str_replace('%TEMP_PREFIX%', rex::getTempPrefix(), $qry);

    return $qry;
  }

  /**
   * Reads a file and split all statements in it.
   *
   * @param $file String Path to the SQL-dump-file
   */
  static private function readSqlDump($file)
  {
    if (is_file($file) && is_readable($file))
    {
      $ret = array ();
      $sqlsplit = '';
      $fileContent = file_get_contents($file);
      self::splitSqlFile($sqlsplit, $fileContent, '');

      if (is_array($sqlsplit))
      {
        foreach ($sqlsplit as $qry)
        {
          $ret[] = $qry['query'];
        }
      }

      return $ret;
    }

    return false;
  }

  /**
   * Removes comment lines and splits up large sql files into individual queries
   *
   * Last revision: September 23, 2001 - gandon
   *
   * @param   array    the splitted sql commands
   * @param   string   the sql commands
   * @param   integer  the MySQL release number (because certains php3 versions
   *                   can't get the value of a constant from within a function)
   *
   * @return  boolean  always true
   *
   * @access  public
   */
  // Taken from phpmyadmin (read_dump.lib.php: PMA_splitSqlFile)
  static public function splitSqlFile(& $ret, $sql, $release)
  {
    // do not trim, see bug #1030644
    //$sql          = trim($sql);
    $sql = rtrim($sql, "\n\r");
    $sql_len = strlen($sql);
    $char = '';
    $string_start = '';
    $in_string = FALSE;
    $nothing = TRUE;
    $time0 = time();

    for ($i = 0; $i < $sql_len; ++ $i)
    {
      $char = $sql[$i];

      // We are in a string, check for not escaped end of strings except for
      // backquotes that can't be escaped
      if ($in_string)
      {
        for (;;)
        {
          $i = strpos($sql, $string_start, $i);
          // No end of string found -> add the current substring to the
          // returned array
          if (!$i)
          {
            $ret[] = $sql;
            return TRUE;
          }
          // Backquotes or no backslashes before quotes: it's indeed the
          // end of the string -> exit the loop
          else
            if ($string_start == '`' || $sql[$i -1] != '\\')
            {
              $string_start = '';
              $in_string = FALSE;
              break;
            }
          // one or more Backslashes before the presumed end of string...
          else
          {
            // ... first checks for escaped backslashes
            $j = 2;
            $escaped_backslash = FALSE;
            while ($i - $j > 0 && $sql[$i - $j] == '\\')
            {
              $escaped_backslash = !$escaped_backslash;
              $j ++;
            }
            // ... if escaped backslashes: it's really the end of the
            // string -> exit the loop
            if ($escaped_backslash)
            {
              $string_start = '';
              $in_string = FALSE;
              break;
            }
            // ... else loop
            else
            {
              $i ++;
            }
          } // end if...elseif...else
        } // end for
      } // end if (in string)

      // lets skip comments (/*, -- and #)
      else
        if (($char == '-' && $sql_len > $i +2 && $sql[$i +1] == '-' && $sql[$i +2] <= ' ') || $char == '#' || ($char == '/' && $sql_len > $i +1 && $sql[$i +1] == '*'))
        {
          $i = strpos($sql, $char == '/' ? '*/' : "\n", $i);
          // didn't we hit end of string?
          if ($i === FALSE)
          {
            break;
          }
          if ($char == '/')
            $i ++;
        }

      // We are not in a string, first check for delimiter...
      else
        if ($char == ';')
        {
          // if delimiter found, add the parsed part to the returned array
          $ret[] = array ('query' => substr($sql, 0, $i), 'empty' => $nothing);
          $nothing = TRUE;
          $sql = ltrim(substr($sql, min($i +1, $sql_len)));
          $sql_len = strlen($sql);
          if ($sql_len)
          {
            $i = -1;
          }
          else
          {
            // The submited statement(s) end(s) here
            return TRUE;
          }
        } // end else if (is delimiter)

      // ... then check for start of a string,...
      else
        if (($char == '"') || ($char == '\'') || ($char == '`'))
        {
          $in_string = TRUE;
          $nothing = FALSE;
          $string_start = $char;
        } // end else if (is start of string)

      elseif ($nothing)
      {
        $nothing = FALSE;
      }

      // loic1: send a fake header each 30 sec. to bypass browser timeout
      $time1 = time();
      if ($time1 >= $time0 +30)
      {
        $time0 = $time1;
        header('X-pmaPing: Pong');
      } // end if
    } // end for

    // add any rest to the returned array
    if (!empty ($sql) && preg_match('@[^[:space:]]+@', $sql))
    {
      $ret[] = array ('query' => $sql, 'empty' => $nothing);
    }

    return TRUE;
  } // end of the 'PMA_splitSqlFile()' function
}
