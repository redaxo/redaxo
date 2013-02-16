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
   * @param string $tableName Name der Datenbanktabelle
   * @param $priorColumnName Name der Spalte in der Tabelle, in der die Priorität (Integer) gespeichert wird
   * @param $whereCondition Where-Bedingung zur Einschränkung des ResultSets
   * @param $orderBy Sortierung des ResultSets
   * @param $id_field Name des Primaerschluessels der Tabelle
   * @param $startBy Startpriorität
   */
  static public function organizePriorities($tableName, $priorColumnName, $whereCondition = '', $orderBy = '', $id_field = 'id', $startBy = 1)
  {
    // Datenbankvariable initialisieren
    $qry = 'SET @count=' . ($startBy - 1);
    $sql = rex_sql::factory();
    $sql->setQuery($qry);

    // Spalte updaten
    $qry = 'UPDATE ' . $tableName . ' SET ' . $priorColumnName . ' = ( SELECT @count := @count +1 )';

    if ($whereCondition != '')
      $qry .= ' WHERE ' . $whereCondition;

    if ($orderBy != '')
      $qry .= ' ORDER BY ' . $orderBy;

    $sql->setQuery($qry);
  }

  /**
   * Importiert die gegebene SQL-Datei in die Datenbank
   *
   * @param string  $file
   * @param boolean $debug
   * @throws rex_sql_exception
   * @return boolean true bei Erfolg
   */
  static public function importDump($file, $debug = false)
  {
    $sql = rex_sql::factory();
    $sql->setDebug($debug);
    $error = '';

    foreach (self::readSqlDump($file) as $query) {
      try {
        $sql->setQuery(self::prepareQuery($query));
      } catch (rex_sql_exception $e) {
        $error .= $e->getMessage() . "\n<br />";
      }
    }
    if ($error) {
      throw new rex_sql_exception($error);
    }

    return true;
  }

  static private function prepareQuery($qry)
  {
    // rex::getUser() gibts im Setup nicht
    if (rex::getUser())
      $qry = str_replace('%USER%', rex::getUser()->getValue('login'), $qry);

    $qry = str_replace('%TIME%', time(), $qry);
    $qry = str_replace('%TABLE_PREFIX%', rex::getTablePrefix(), $qry);
    $qry = str_replace('%TEMP_PREFIX%', rex::getTempPrefix(), $qry);

    return $qry;
  }

  /**
   * Reads a file and split all statements in it.
   *
   * @param string $file Path to the SQL-dump-file
   * @return array
   */
  static private function readSqlDump($file)
  {
    if (is_file($file) && is_readable($file)) {
      $ret = array();
      $sqlsplit = '';
      $fileContent = file_get_contents($file);
      self::splitSqlFile($sqlsplit, $fileContent, '');

      if (is_array($sqlsplit)) {
        foreach ($sqlsplit as $qry) {
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
    $in_string = false;
    $nothing = true;
    $time0 = time();

    for ($i = 0; $i < $sql_len; ++ $i) {
      $char = $sql[$i];

      // We are in a string, check for not escaped end of strings except for
      // backquotes that can't be escaped
      if ($in_string) {
        for (;; ) {
          $i = strpos($sql, $string_start, $i);
          // No end of string found -> add the current substring to the
          // returned array
          if (!$i) {
            $ret[] = $sql;
            return true;
          }
          // Backquotes or no backslashes before quotes: it's indeed the
          // end of the string -> exit the loop
          elseif ($string_start == '`' || $sql[$i - 1] != '\\') {
              $string_start = '';
              $in_string = false;
              break;
            }
          // one or more Backslashes before the presumed end of string...
          else {
            // ... first checks for escaped backslashes
            $j = 2;
            $escaped_backslash = false;
            while ($i - $j > 0 && $sql[$i - $j] == '\\') {
              $escaped_backslash = !$escaped_backslash;
              $j ++;
            }
            // ... if escaped backslashes: it's really the end of the
            // string -> exit the loop
            if ($escaped_backslash) {
              $string_start = '';
              $in_string = false;
              break;
            }
            // ... else loop
            else {
              $i ++;
            }
          } // end if...elseif...else
        } // end for
      } // end if (in string)

      // lets skip comments (/*, -- and #)
      elseif (($char == '-' && $sql_len > $i + 2 && $sql[$i + 1] == '-' && $sql[$i + 2] <= ' ') || $char == '#' || ($char == '/' && $sql_len > $i + 1 && $sql[$i + 1] == '*')) {
          $i = strpos($sql, $char == '/' ? '*/' : "\n", $i);
          // didn't we hit end of string?
          if ($i === false) {
            break;
          }
          if ($char == '/')
            $i ++;
        }

      // We are not in a string, first check for delimiter...
      elseif ($char == ';') {
          // if delimiter found, add the parsed part to the returned array
          $ret[] = array('query' => substr($sql, 0, $i), 'empty' => $nothing);
          $nothing = true;
          $sql = ltrim(substr($sql, min($i + 1, $sql_len)));
          $sql_len = strlen($sql);
          if ($sql_len) {
            $i = -1;
          } else {
            // The submited statement(s) end(s) here
            return true;
          }
        } // end else if (is delimiter)

      // ... then check for start of a string,...
      elseif (($char == '"') || ($char == '\'') || ($char == '`')) {
          $in_string = true;
          $nothing = false;
          $string_start = $char;
        } // end else if (is start of string)

      elseif ($nothing) {
        $nothing = false;
      }

      // loic1: send a fake header each 30 sec. to bypass browser timeout
      $time1 = time();
      if ($time1 >= $time0 + 30) {
        $time0 = $time1;
        header('X-pmaPing: Pong');
      } // end if
    } // end for

    // add any rest to the returned array
    if (!empty ($sql) && preg_match('@[^[:space:]]+@', $sql)) {
      $ret[] = array('query' => $sql, 'empty' => $nothing);
    }

    return true;
  } // end of the 'PMA_splitSqlFile()' function
}
