<?php

/**
 * Returns true if $string starts with $start
 * 
 * @param $string String Searchstring
 * @param $start String Prefix to search for
 * @author Markus Staab <staab@public-4u.de>
 */
if (!function_exists('startsWith'))
{
   function startsWith($string, $start)
   {
      return strstr($string, $start) == $string;
   }
}

/**
 * Returns true if $string ends with $end
 * 
 * @param $string String Searchstring
 * @param $start String Suffix to search for
 * @author Markus Staab <staab@public-4u.de>
 */
if (!function_exists('endsWith'))
{
   function endsWith($string, $end)
   {
      return (substr($string, strlen($string) - strlen($end)) == $end);
   }
}

/**
 * Returns the truncated $string
 * 
 * @param $string String Searchstring
 * @param $start String Suffix to search for
 * @author Markus Staab <staab@public-4u.de>
 */
if (!function_exists('truncate'))
{
   function truncate($string, $length = 80, $etc = '...', $break_words = false)
   {
      if ($length == 0)
         return '';

      if (strlen($string) > $length)
      {
         $length -= strlen($etc);
         if (!$break_words)
            $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length +1));

         return substr($string, 0, $length).$etc;
      }
      else
         return $string;
   }
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
if (!function_exists('PMA_splitSqlFile'))
{
   function PMA_splitSqlFile(& $ret, $sql, $release)
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
/**
 * Reads a file and split all statements in it.
 * 
 * @param $file String Path to the SQL-dump-file
 * @author Markus Staab <staab@public-4u.de>
 */
if (!function_exists('readSqlDump'))
{
   function readSqlDump($file)
   {
      if (is_file($file) && is_readable($file))
      {
         $ret = array ();
         $sqlsplit = '';
         $fileContent = file_get_contents($file);
         PMA_splitSqlFile($sqlsplit, $fileContent, '');

         foreach ($sqlsplit as $qry)
         {
            $ret[] = $qry['query'];
         }

         return $ret;
      }

      return false;
   }
}