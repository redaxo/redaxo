<?php

/**
 * Class to execute a sql dump.
 *
 * @package redaxo\core\sql
 */
class rex_sql_util
{
    /**
     * Allgemeine funktion die eine Datenbankspalte fortlaufend durchnummeriert.
     * Dies ist z.B. nützlich beim Umgang mit einer Prioritäts-Spalte.
     *
     * @param string $tableName       Name der Datenbanktabelle
     * @param string $priorColumnName Name der Spalte in der Tabelle, in der die Priorität (Integer) gespeichert wird
     * @param string $whereCondition  Where-Bedingung zur Einschränkung des ResultSets
     * @param string $orderBy         Sortierung des ResultSets
     * @param int    $startBy         Startpriorität
     */
    public static function organizePriorities($tableName, $priorColumnName, $whereCondition = '', $orderBy = '', $startBy = 1)
    {
        // Datenbankvariable initialisieren
        $qry = 'SET @count=' . ($startBy - 1);
        $sql = rex_sql::factory();
        $sql->setQuery($qry);

        // Spalte updaten
        $qry = 'UPDATE ' . $tableName . ' SET ' . $priorColumnName . ' = ( SELECT @count := @count +1 )';

        if ('' != $whereCondition) {
            $qry .= ' WHERE ' . $whereCondition;
        }

        if ('' != $orderBy) {
            $qry .= ' ORDER BY ' . $orderBy;
        }

        $sql->setQuery($qry);
    }

    /**
     * Importiert die gegebene SQL-Datei in die Datenbank.
     *
     * @param string $file
     * @param bool   $debug
     *
     * @throws rex_sql_exception
     *
     * @return bool true bei Erfolg
     */
    public static function importDump($file, $debug = false)
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
            throw new rex_sql_exception($error, null, $sql);
        }

        return true;
    }

    /**
     * @return string
     */
    private static function prepareQuery($qry)
    {
        // rex::getUser() gibts im Setup nicht
        $user = rex::getUser() ? rex::getUser()->getValue('login') : '';

        $qry = str_replace('%USER%', $user, $qry);
        $qry = str_replace('%TIME%', time(), $qry);
        $qry = str_replace('%TABLE_PREFIX%', rex::getTablePrefix(), $qry);
        $qry = str_replace('%TEMP_PREFIX%', rex::getTempPrefix(), $qry);

        return $qry;
    }

    /**
     * Reads a file and split all statements in it.
     *
     * @param string $file Path to the SQL-dump-file
     *
     * @return array
     */
    private static function readSqlDump($file)
    {
        if (is_file($file) && is_readable($file)) {
            $ret = [];
            $sqlsplit = [];
            $fileContent = file_get_contents($file);
            self::splitSqlFile($sqlsplit, $fileContent, '');

            if (is_array($sqlsplit)) {
                foreach ($sqlsplit as $qry) {
                    $ret[] = $qry['query'];
                }
            }

            return $ret;
        }

        throw new rex_exception('File "'.$file.'" could not be read.');
    }

    // Taken from phpmyadmin (read_dump.lib.php: PMA_splitSqlFile)

    /**
     * Removes comment lines and splits up large sql files into individual queries.
     *
     * Last revision: September 23, 2001 - gandon
     *
     * @param array  $ret     the splitted sql commands
     * @param string $sql     the sql commands
     * @param int    $release the MySQL release number (because certains php3 versions
     *                        can't get the value of a constant from within a function)
     *
     * @return bool always true
     */
    public static function splitSqlFile(&$ret, $sql, $release)
    {
        // do not trim, see bug #1030644
        //$sql          = trim($sql);
        $sql = rtrim($sql, "\n\r");
        $sql_len = strlen($sql);
        $string_start = '';
        $in_string = false;
        $nothing = true;
        $time0 = time();

        for ($i = 0; $i < $sql_len; ++$i) {
            $char = $sql[$i];

            // We are in a string, check for not escaped end of strings except for
            // backquotes that can't be escaped
            if ($in_string) {
                for (;;) {
                    /** @psalm-suppress LoopInvalidation */
                    $i = strpos($sql, $string_start, $i);
                    // No end of string found -> add the current substring to the
                    // returned array
                    if (!$i) {
                        $ret[] = $sql;
                        return true;
                    }
                    // Backquotes or no backslashes before quotes: it's indeed the
                    // end of the string -> exit the loop
                    if ('`' == $string_start || '\\' != $sql[$i - 1]) {
                        $string_start = '';
                        $in_string = false;
                        break;
                    }
                    // one or more Backslashes before the presumed end of string...

                    // ... first checks for escaped backslashes
                    $j = 2;
                    $escaped_backslash = false;
                    while ($i - $j > 0 && '\\' == $sql[$i - $j]) {
                        $escaped_backslash = !$escaped_backslash;
                        ++$j;
                    }
                    // ... if escaped backslashes: it's really the end of the
                    // string -> exit the loop
                    if ($escaped_backslash) {
                        $string_start = '';
                        $in_string = false;
                        break;
                    }
                    // ... else loop

                    ++$i;

                    // end if...elseif...else
                } // end for
            } // end if (in string)

            // lets skip comments (/*, -- and #)
            elseif (('-' == $char && $sql_len > $i + 2 && '-' == $sql[$i + 1] && $sql[$i + 2] <= ' ') || '#' == $char || ('/' == $char && $sql_len > $i + 1 && '*' == $sql[$i + 1])) {
                /** @psalm-suppress LoopInvalidation */
                $i = strpos($sql, '/' == $char ? '*/' : "\n", $i);
                // didn't we hit end of string?
                if (false === $i) {
                    break;
                }
                if ('/' == $char) {
                    ++$i;
                }
            }

            // We are not in a string, first check for delimiter...
            elseif (';' == $char) {
                // if delimiter found, add the parsed part to the returned array
                $ret[] = ['query' => substr($sql, 0, $i), 'empty' => $nothing];
                $nothing = true;
                $sql = ltrim(substr($sql, min($i + 1, $sql_len)));
                $sql_len = strlen($sql);
                if ($sql_len) {
                    /** @psalm-suppress LoopInvalidation */
                    $i = -1;
                } else {
                    // The submited statement(s) end(s) here
                    return true;
                }
            } // end else if (is delimiter)

            // ... then check for start of a string,...
            elseif (('"' == $char) || ('\'' == $char) || ('`' == $char)) {
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
        if (!empty($sql) && preg_match('@[^[:space:]]+@', $sql)) {
            $ret[] = ['query' => $sql, 'empty' => $nothing];
        }

        return true;
    }
}
