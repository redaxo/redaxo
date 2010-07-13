<?php

define('REX_A1_IMPORT_ARCHIVE', 1);
define('REX_A1_IMPORT_DB', 2);
define('REX_A1_IMPORT_EVENT_PRE', 3);
define('REX_A1_IMPORT_EVENT_POST', 4);

// Da diese Funktion im Setup direkt eingebunden wird
// hier das I18N Objekt ggf. erstellen
if ($REX['REDAXO'] && !isset($I18N))
{
  global $I18N;
  require_once(dirname(dirname(__FILE__)).'/config.inc.php');
}


/**
 * Importiert den SQL Dump $filename in die Datenbank
 *
 * @param string Pfad + Dateinamen zur SQL-Datei
 *
 * @return array Gibt ein Assoc. Array zurück.
 *               'state' => boolean (Status ob fehler aufgetreten sind)
 *               'message' => Evtl. Status/Fehlermeldung
 */
function rex_a1_import_db($filename)
{
  global $REX, $I18N;

  $return = array ();
  $return['state'] = false;
  $return['message'] = '';

  $msg = '';
  $error = '';

  if ($filename == '' || substr($filename, -4, 4) != ".sql")
  {
    $return['message'] = $I18N->msg('im_export_no_import_file_chosen_or_wrong_version').'<br>';
    return $return;
  }

  $conts = rex_get_file_contents($filename);

  // Versionsstempel prüfen
  // ## Redaxo Database Dump Version x.x
  $version = strpos($conts, '## Redaxo Database Dump Version '.$REX['VERSION']);
  if($version === false)
  {
    $return['message'] = $I18N->msg('im_export_no_valid_import_file').'. [## Redaxo Database Dump Version '.$REX['VERSION'].'] is missing';
    return $return;
  }
  // Versionsstempel entfernen
  $conts = trim(str_replace('## Redaxo Database Dump Version '.$REX['VERSION'], '', $conts));

  // Prefix prüfen
  // ## Prefix xxx_
  if(preg_match('/^## Prefix ([a-zA-Z0-9\_]*)/', $conts, $matches) && isset($matches[1]))
  {
    // prefix entfernen
    $prefix = $matches[1];
    $conts = trim(str_replace('## Prefix '. $prefix, '', $conts));
  }
  else
  {
    // Prefix wurde nicht gefunden
    $return['message'] = $I18N->msg('im_export_no_valid_import_file').'. [## Prefix '. $REX['TABLE_PREFIX'] .'] is missing';
    return $return;
  }


  // Charset prüfen
  // ## charset xxx_
  if(preg_match('/^## charset ([a-zA-Z0-9\_\-]*)/', $conts, $matches) && isset($matches[1]))
  {
    // charset entfernen
    $charset = $matches[1];
    $conts = trim(str_replace('## charset '. $charset, '', $conts));
    
    if($I18N->msg('htmlcharset') != $charset)
    {
      $return['message'] = $I18N->msg('im_export_no_valid_charset').'. '.$I18N->msg('htmlcharset').' != '.$charset;
      return $return;
    }
    
  }

  
  // Prefix im export mit dem der installation angleichen
  if($REX['TABLE_PREFIX'] != $prefix)
  {
    // Hier case-insensitiv ersetzen, damit alle möglich Schreibweisen (TABLE TablE, tAblE,..) ersetzt werden
    // Dies ist wichtig, da auch SQLs innerhalb von Ein/Ausgabe der Module vom rex-admin verwendet werden
    $conts = preg_replace('/(TABLE `?)' . preg_quote($prefix, '/') .'/i', '$1'. $REX['TABLE_PREFIX'], $conts);
    $conts = preg_replace('/(INTO `?)'  . preg_quote($prefix, '/') .'/i', '$1'. $REX['TABLE_PREFIX'], $conts);
    $conts = preg_replace('/(EXISTS `?)'. preg_quote($prefix, '/') .'/i', '$1'. $REX['TABLE_PREFIX'], $conts);
  }

  // ----- EXTENSION POINT
  $filesize = filesize($filename);
  $msg = rex_register_extension_point('A1_BEFORE_DB_IMPORT', $msg,
   array(
     'content' => $conts,
     'filename' => $filename,
     'filesize' => $filesize
   )
  );

  // require import skript to do some userside-magic
  rex_a1_import_skript(str_replace('.sql', '.php', $filename), REX_A1_IMPORT_DB, REX_A1_IMPORT_EVENT_PRE);

  if (!function_exists('PMA_splitSqlFile'))
  {
    include_once ($REX['INCLUDE_PATH'].'/functions/function_rex_addons.inc.php');
  }
  
  // Datei aufteilen
  $lines = array();
  PMA_splitSqlFile($lines, $conts, 0);

  $sql   = rex_sql::factory();
  foreach ($lines as $line) {
    $sql->setQuery($line['query']);

    if($sql->hasError())
    {
      $error .= "\n". $sql->getError();
    }
  }

  if($error != '')
  {
    $return['message'] = trim($error);
    return $return;
  }

  $msg .= $I18N->msg('im_export_database_imported').'. '.$I18N->msg('im_export_entry_count', count($lines)).'<br />';
  unset($lines);

  // prüfen, ob eine user tabelle angelegt wurde
  $tables = rex_sql::showTables();
  $user_table_found = in_array($REX['TABLE_PREFIX'].'user', $tables);

  if (!$user_table_found)
  {
    $create_user_table = '
    CREATE TABLE '. $REX['TABLE_PREFIX'] .'user
     (
       user_id int(11) NOT NULL auto_increment,
       name varchar(255) NOT NULL,
       description text NOT NULL,
       login varchar(50) NOT NULL,
       psw varchar(50) NOT NULL,
       status varchar(5) NOT NULL,
       rights text NOT NULL,
       login_tries tinyint(4) NOT NULL DEFAULT 0,
       createuser varchar(255) NOT NULL,
       updateuser varchar(255) NOT NULL,
       createdate int(11) NOT NULL DEFAULT 0,
       updatedate int(11) NOT NULL DEFAULT 0,
       lasttrydate int(11) NOT NULL DEFAULT 0,
       session_id varchar(255) NOT NULL,
       PRIMARY KEY(user_id)
     ) TYPE=MyISAM;';
    $db = rex_sql::factory();
    $db->setQuery($create_user_table);
    $error = $db->getError();
    if($error != '')
    {
      // evtl vorhergehende meldungen löschen, damit nur der fehler angezeigt wird
      $msg = '';
      $msg .= $error;
    }
  }

  // generated neu erstellen, wenn kein Fehler aufgetreten ist
  if($error == '')
  {
    // ----- EXTENSION POINT
    $msg = rex_register_extension_point('A1_AFTER_DB_IMPORT', $msg,
     array(
       'content' => $conts,
       'filename' => $filename,
       'filesize' => $filesize
     )
    );

    // require import skript to do some userside-magic
  rex_a1_import_skript(str_replace('.sql', '.php', $filename), REX_A1_IMPORT_DB, REX_A1_IMPORT_EVENT_POST);
    
    $msg .= rex_generateAll();
    $return['state'] = true;
  }

  $return['message'] = $msg;

  return $return;
}

/**
 * Importiert das Tar-Archiv $filename in den Ordner /files
 *
 * @param string Pfad + Dateinamen zum Tar-Archiv
 *
 * @return array Gibt ein Assoc. Array zurück.
 *               'state' => boolean (Status ob fehler aufgetreten sind)
 *               'message' => Evtl. Status/Fehlermeldung
 */
function rex_a1_import_files($filename)
{
  global $REX, $I18N;

  $return = array ();
  $return['state'] = false;

  if ($filename == '' || substr($filename, -7, 7) != ".tar.gz")
  {
    $return['message'] = $I18N->msg("im_export_no_import_file_chosen")."<br />";
    return $return;
  }

  // Ordner /files komplett leeren
  rex_deleteFiles($REX['INCLUDE_PATH']."/../../files");

  $tar = new rex_tar;

  // ----- EXTENSION POINT
  $tar = rex_register_extension_point('A1_BEFORE_FILE_IMPORT', $tar);
  
  // require import skript to do some userside-magic
  rex_a1_import_skript(str_replace('.tar.gz', '.php', $filename), REX_A1_IMPORT_ARCHIVE, REX_A1_IMPORT_EVENT_PRE);

  $tar->openTAR($filename);
  if (!$tar->extractTar())
  {
    $msg = $I18N->msg('im_export_problem_when_extracting').'<br />';
    if (count($tar->message) > 0)
    {
      $msg .= $I18N->msg('im_export_create_dirs_manually').'<br />';
      foreach($tar->message as $_message)
      {
        $msg .= rex_absPath($_message).'<br />';
      }
    }
  }
  else
  {
    $msg = $I18N->msg('im_export_file_imported').'<br />';
  }

  // ----- EXTENSION POINT
  $tar = rex_register_extension_point('A1_AFTER_FILE_IMPORT', $tar);

  // require import skript to do some userside-magic
  rex_a1_import_skript(str_replace('.tar.gz', '.php', $filename), REX_A1_IMPORT_ARCHIVE, REX_A1_IMPORT_EVENT_POST);

  $return['state'] = true;
  $return['message'] = $msg;
  return $return;
}

/**
 * Erstellt einen SQL Dump, der die aktuellen Datebankstruktur darstellt.
 * Dieser wird in der Datei $filename gespeichert.
 * 
 * @return boolean TRUE wenn ein Dump erstellt wurde, sonst FALSE
 */
function rex_a1_export_db($filename)
{
  global $REX,$I18N;
  
  $fp = @fopen($filename, "w");
  
  if (!$fp)
  {
    return false;
  }
  
  // Im Frontend gibts kein I18N
  if(!is_object($I18N))
    $I18N = rex_create_lang($REX['LANG']);
  
  $sql        = rex_sql::factory();
  $tables     = rex_sql::showTables(1, $REX['TABLE_PREFIX']);
  
  $nl         = "\n";
  $insertSize = 5000;

  // ----- EXTENSION POINT
  rex_register_extension_point('A1_BEFORE_DB_EXPORT');
  
  // Versionsstempel hinzufügen
  fwrite($fp, '## Redaxo Database Dump Version '.$REX['VERSION'].$nl);
  fwrite($fp, '## Prefix '.$REX['TABLE_PREFIX'].$nl);
  fwrite($fp, '## charset '.$I18N->msg('htmlcharset').$nl.$nl);
//  fwrite($fp, '/*!40110 START TRANSACTION; */'.$nl);

  foreach ($tables as $table)
  {
    if ($table != $REX['TABLE_PREFIX'].'user' // User Tabelle nicht exportieren
        && substr($table, 0 , strlen($REX['TABLE_PREFIX'].$REX['TEMP_PREFIX'])) != $REX['TABLE_PREFIX'].$REX['TEMP_PREFIX']) // Tabellen die mit rex_tmp_ beginnne, werden nicht exportiert!
    {
      //---- export metadata
      $create = rex_sql::showCreateTable($table);
    
      fwrite($fp, "DROP TABLE IF EXISTS `$table`;\n");
      fwrite($fp, "$create;\n");
    
      $fields = $sql->getArray("SHOW FIELDS FROM `$table`");
    
      foreach ($fields as $field)
      {
        if (preg_match('#^(bigint|int|smallint|mediumint|tinyint|timestamp)#i', $field['Type']))
        {
          $field = 'int';
        }
        elseif (preg_match('#^(float|double|decimal)#', $field['Type']))
        {
          $field = 'double';
        }
        elseif (preg_match('#^(char|varchar|text|longtext|mediumtext|tinytext)#', $field['Type']))
        {
          $field = 'string';
        }
        // else ?
      }
      
      //---- export tabledata
      $start = 0;
      $max   = $insertSize;
      
      do
      {
        $sql->freeResult();
        $sql->setQuery("SELECT * FROM `$table` LIMIT $start,$max");
      
        if ($sql->getRows() > 0 && $start == 0)
        {
          fwrite($fp, "\nLOCK TABLES `$table` WRITE;");
          fwrite($fp, "\n/*!40000 ALTER TABLE `$table` DISABLE KEYS */;");
        }
        elseif ($sql->getRows() == 0)
        {
          break;
        }
        
        $start += $max;
        $values = array();
      
        while($sql->hasNext())
        {
          $record = array();
          
          foreach ($fields as $idx => $type)
          {
            $column = $sql->getValue($idx);
            
            switch ($type)
            {
              case 'int':
                $record[] = intval($column);
                break;
              case 'double':
                $record[] = sprintf('%.10F', (double) $column);
                break;
              case 'string':
              default:
                $record[] = $sql->escape($column, "'");
                break;
            }
          }
        
          $values[] = $nl .'  ('.implode(',', $record).')';
          $sql->next();
        }
      
        if (!empty($values))
        {
          $values = implode(',', $values);
          fwrite($fp, "\nINSERT INTO `$table` VALUES $values;");
          unset($values);
        }
      }
      while ($sql->getRows() >= $max);
      
      if ($start > 0)
      {
        fwrite($fp, "\n/*!40000 ALTER TABLE `$table` ENABLE KEYS */;");
        fwrite($fp, "\nUNLOCK TABLES;\n\n");
      }
    }
  }

  fclose($fp);
  
  
  $hasContent = true;
  
  // Den Dateiinhalt geben wir nur dann weiter, wenn es unbedingt notwendig ist.
  if (rex_extension_is_registered('A1_AFTER_DB_EXPORT'))
  {
    $content    = rex_get_file_contents($filename);
    $hashBefore = md5($content);
    // ----- EXTENSION POINT
    $content    = rex_register_extension_point('A1_AFTER_DB_EXPORT', $content);
    $hashAfter  = md5($content);
    
    if ($hashAfter != $hashBefore)
    {
      rex_put_file_contents($filename, $content);
      $hasContent = !empty($content);
      unset($content);
    }
  }

  return $hasContent;
}

/**
 * Exportiert alle Ordner $folders aus dem Verzeichnis /files
 *
 * @param array Array von Ordnernamen, die exportiert werden sollen
 * @param string Pfad + Dateiname, wo das Tar File erstellt werden soll
 *
 * @access public
 * @return string Inhalt des Tar-Archives als String
 */
function rex_a1_export_files($folders)
{
  global $REX;

  $tar = new rex_tar;

  // ----- EXTENSION POINT
  $tar = rex_register_extension_point('A1_BEFORE_FILE_EXPORT', $tar);

  foreach ($folders as $key => $item)
  {
    // Hier HTDOCS statt INLCUDE PATH, da INLCUDE PATH absolut ist!
    _rex_a1_add_folder_to_tar($tar, $REX['HTDOCS_PATH']."redaxo/include/../../", $key);
  }

  // ----- EXTENSION POINT
  $tar = rex_register_extension_point('A1_AFTER_FILE_EXPORT', $tar);

  return $tar->toTar(null, true);
}

/**
 * Fügt einem Tar-Archiv ein Ordner von Dateien hinzu
 * @access protected
 */
function _rex_a1_add_folder_to_tar(& $tar, $path, $dir)
{
  global $REX;

  $handle = opendir($path.$dir);
  $isMediafolder = realpath($path.$dir) == $REX['MEDIAFOLDER'];
  while (false !== ($file = readdir($handle)))
  {
    // Alles exportieren, außer ... 
    // - addons verzeichnis im mediafolder (wird bei addoninstallation wiedererstellt)
    // - svn infos
    // - tmp prefix Dateien
    
    if($file == '.' || $file == '..' || $file == '.svn')
      continue;
    
    if(substr($file, 0, strlen($REX['TEMP_PREFIX'])) == $REX['TEMP_PREFIX'])
      continue;
      
    if($isMediafolder && $file == 'addons')
      continue;
      
    if (is_dir($path.$dir."/".$file))
    {
      _rex_a1_add_folder_to_tar($tar, $path.$dir."/", $file);
    }
    else
    {
      $tar->addFile($path.$dir."/".$file, true);
    }
  }
  closedir($handle);
}

function rex_a1_import_skript($filename, $importType, $eventType)
{
  // damit $REX im Skript verfuegbar ist
  global $REX;

  if(file_exists($filename))
  {
    require($filename);
  }
}