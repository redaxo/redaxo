<?php


/**
 * Funktionensammlung für die generierung der Artikel/Templates/Kategorien/Metainfos.. etc.
 * @package redaxo4
 * @version svn:$Id$
 */

// ----------------------------------------- Alles generieren

/**
 * Löscht den vollständigen Artikel-Cache.
 */
function rex_generateAll()
{
  global $REX;

  // ----------------------------------------------------------- generated löschen
  rex_deleteDir(rex_path::generated(), FALSE);

  // ----------------------------------------------------------- generiere clang
  if(($MSG = rex_generateClang()) !== TRUE)
  {
    return $MSG;
  }

  // ----------------------------------------------------------- message
  $MSG = $REX['I18N']->msg('delete_cache_message');

  // ----- EXTENSION POINT
  $MSG = rex_register_extension_point('ALL_GENERATED', $MSG);

  return $MSG;
}





/**
 * Löscht einen Ordner/Datei mit Unterordnern
 *
 * @param $file Zu löschender Ordner/Datei
 * @param $delete_folders Ordner auch löschen? false => nein, true => ja
 *
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_deleteDir($file, $delete_folders = FALSE)
{
  $debug = FALSE;
  $state = TRUE;

  $file = rtrim($file, DIRECTORY_SEPARATOR);

  if (file_exists($file))
  {
    // Fehler unterdrücken, falls keine Berechtigung
    if (@ is_dir($file))
    {
      $handle = opendir($file);
      if (!$handle)
      {
        if($debug)
          echo "Unable to open dir '$file'<br />\n";

        return FALSE;
      }

      while ($filename = readdir($handle))
      {
        if ($filename == '.' || $filename == '..')
        {
          continue;
        }

        if (!rex_deleteDir($file.DIRECTORY_SEPARATOR.$filename, $delete_folders))
        {
          $state = FALSE;
        }
      }
      closedir($handle);

      if ($state !== TRUE)
      {
        return FALSE;
      }


      // Ordner auch löschen?
      if ($delete_folders)
      {
        // Fehler unterdrücken, falls keine Berechtigung
        if (!@ rmdir($file))
        {
          if($debug)
            echo "Unable to delete folder '$file'<br />\n";

          return FALSE;
        }
      }
    }
    else
    {
      // Datei löschen
      // Fehler unterdrücken, falls keine Berechtigung
      if (!@ unlink($file))
      {
        if($debug)
          echo "Unable to delete file '$file'<br />\n";

        return FALSE;
      }
    }
  }
  else
  {
    if($debug)
      echo "file '$file'not found!<br />\n";
    // Datei/Ordner existiert nicht
    return FALSE;
  }

  return TRUE;
}

/**
 * Lösch allen Datei in einem Ordner
 *
 * @param $file Pfad zum Ordner
 *
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_deleteFiles($file)
{
  $debug = FALSE;

  $file = rtrim($file, DIRECTORY_SEPARATOR);

  if (file_exists($file))
  {
    // Fehler unterdrücken, falls keine Berechtigung
    if (@ is_dir($file))
    {
      $handle = opendir($file);
      if (!$handle)
      {
        if($debug)
          echo "Unable to open dir '$file'<br />\n";

        return FALSE;
      }

      while ($filename = readdir($handle))
      {
        if ($filename == '.' || $filename == '..')
        {
          continue;
        }

	      if (!@ unlink($file))
	      {
	        if($debug)
	          echo "Unable to delete file '$file'<br />\n";

	        return FALSE;
	      }

      }
      closedir($handle);
    }
    else
    {
      // Datei löschen
      // Fehler unterdrücken, falls keine Berechtigung
    }
  }
  else
  {
    if($debug)
      echo "file '$file'not found!<br />\n";
    // Datei/Ordner existiert nicht
    return FALSE;
  }

  return TRUE;
}

/**
 * Erstellt einne Ordner
 *
 * @param $dir Zu erstellendes Verzeichnis
 * @param $recursive
 *
 * @return TRUE bei Erfolg, FALSE bei Fehler
 */
function rex_createDir($dir, $recursive = true)
{
  global $REX;

  if(mkdir($dir, $REX['DIRPERM'], $recursive))
  {
    @chmod($dir, $REX['DIRPERM']);
    return true;
  }

  return false;
}

/**
 * Kopiert eine Ordner von $srcdir nach $dstdir
 *
 * @param $srcdir Zu kopierendes Verzeichnis
 * @param $dstdir Zielpfad
 * @param $startdir Pfad ab welchem erst neue Ordner generiert werden
 *
 * @return TRUE bei Erfolg, FALSE bei Fehler
 */
function rex_copyDir($srcdir, $dstdir, $startdir = "")
{
  global $REX;

  $debug = FALSE;
  $state = TRUE;

  if(!is_dir($dstdir))
  {
    $dir = '';
    foreach(explode(DIRECTORY_SEPARATOR, $dstdir) as $dirPart)
    {
      $dir .= $dirPart . DIRECTORY_SEPARATOR;
      if(strpos($startdir,$dir) !== 0 && !is_dir($dir))
      {
        if($debug)
          echo "Create dir '$dir'<br />\n";

        mkdir($dir);
        chmod($dir, $REX['DIRPERM']);
      }
    }
  }

  if($curdir = opendir($srcdir))
  {
    while($file = readdir($curdir))
    {
      if($file != '.' && $file != '..' && $file != '.svn')
      {
        $srcfile = $srcdir . DIRECTORY_SEPARATOR . $file;
        $dstfile = $dstdir . DIRECTORY_SEPARATOR . $file;
        if(is_file($srcfile))
        {
          $isNewer = TRUE;
          if(is_file($dstfile))
          {
            $isNewer = (filemtime($srcfile) - filemtime($dstfile)) > 0;
          }

          if($isNewer)
          {
            if($debug)
              echo "Copying '$srcfile' to '$dstfile'...";
            if(copy($srcfile, $dstfile))
            {
              touch($dstfile, filemtime($srcfile));
              chmod($dstfile, $REX['FILEPERM']);
              if($debug)
                echo "OK<br />\n";
            }
            else
            {
              if($debug)
               echo "Error: File '$srcfile' could not be copied!<br />\n";
              return FALSE;
            }
          }
        }
        else if(is_dir($srcfile))
        {
          $state = rex_copyDir($srcfile, $dstfile, $startdir) && $state;
        }
      }
    }
    closedir($curdir);
  }
  return $state;
}

// ----------------------------------------- CLANG

/**
 * Löscht eine Clang
 *
 * @param $id Zu löschende ClangId
 *
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_deleteCLang($clang)
{
  global $REX;

  if ($clang == 0 || !isset($REX['CLANG'][$clang]))
    return FALSE;

  $clangName = $REX['CLANG'][$clang];
  unset ($REX['CLANG'][$clang]);

  $del = rex_sql::factory();
  $del->setQuery("delete from ".$REX['TABLE_PREFIX']."article where clang='$clang'");
  $del->setQuery("delete from ".$REX['TABLE_PREFIX']."article_slice where clang='$clang'");
  $del->setQuery("delete from ".$REX['TABLE_PREFIX']."clang where id='$clang'");

  // ----- EXTENSION POINT
  rex_register_extension_point('CLANG_DELETED','',
    array (
      'id' => $clang,
      'name' => $clangName,
    )
  );

  rex_generateAll();

  return TRUE;
}

/**
 * Erstellt eine Clang
 *
 * @param $id   Id der Clang
 * @param $name Name der Clang
 *
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_addCLang($id, $name)
{
  global $REX;

  if(isset($REX['CLANG'][$id])) return FALSE;

  $REX['CLANG'][$id] = $name;
  $file = rex_path::src('config/clang.inc.php');
  rex_replace_dynamic_contents($file, "\$REX['CLANG'] = ". var_export($REX['CLANG'], TRUE) .";\n");

  $firstLang = rex_sql::factory();
  $firstLang->setQuery("select * from ".$REX['TABLE_PREFIX']."article where clang='0'");
  $fields = $firstLang->getFieldnames();

  $newLang = rex_sql::factory();
  // $newLang->debugsql = 1;
  while($firstLang->hasNext())
  {
    $newLang->setTable($REX['TABLE_PREFIX']."article");

    foreach($fields as $key => $value)
    {
      if ($value == 'pid')
        echo ''; // nix passiert
      else
        if ($value == 'clang')
          $newLang->setValue('clang', $id);
        else
          if ($value == 'status')
            $newLang->setValue('status', '0'); // Alle neuen Artikel offline
      else
        $newLang->setValue($value, $firstLang->escape($firstLang->getValue($value)));
    }

    $newLang->insert();
    $firstLang->next();
  }
  $firstLang->freeResult();

  $newLang = rex_sql::factory();
  $newLang->setQuery("insert into ".$REX['TABLE_PREFIX']."clang set id='$id',name='$name'");

  // ----- EXTENSION POINT
  rex_register_extension_point('CLANG_ADDED','',array ('id' => $id, 'name' => $name));

  return TRUE;
}

/**
 * Ändert eine Clang
 *
 * @param $id   Id der Clang
 * @param $name Name der Clang
 *
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_editCLang($id, $name)
{
  global $REX;

  if(!isset($REX['CLANG'][$id])) return false;

  $REX['CLANG'][$id] = $name;
  $file = rex_path::src('config/clang.inc.php');
  rex_replace_dynamic_contents($file, "\$REX['CLANG'] = ". var_export($REX['CLANG'], TRUE) .";\n");

  $edit = rex_sql::factory();
  $edit->setQuery("update ".$REX['TABLE_PREFIX']."clang set name='$name' where id='$id'");

  // ----- EXTENSION POINT
  rex_register_extension_point('CLANG_UPDATED','',array ('id' => $id, 'name' => $name));

  return TRUE;
}

/**
 * Schreibt Addoneigenschaften in die Datei include/addons.inc.php
 *
 * @param array Array mit den Namen der Addons aus dem Verzeichnis addons/
 *
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_generateAddons(array $ADDONS)
{
  global $REX;

  $content = "";
  foreach ($ADDONS as $addon)
  {
    if (!rex_ooAddon :: isInstalled($addon))
      rex_ooAddon::setProperty($addon, 'install', 0);

    if (!rex_ooAddon :: isActivated($addon))
      rex_ooAddon::setProperty($addon, 'status', 0);

    foreach(array('install', 'status') as $prop)
    {
      $content .= sprintf(
        "\$REX['ADDON']['%s']['%s'] = '%d';\n",
        $prop,
        $addon,
        rex_ooAddon::getProperty($addon, $prop)
      );
    }
    $content .= "\n";
  }

  // Da dieser Funktion oefter pro request aufgerufen werden kann,
  // hier die caches loeschen
  clearstatcache();

  $file = rex_path::src('config/addons.inc.php');
  if(rex_replace_dynamic_contents($file, $content) === FALSE)
  {
    return 'Datei "'.$file.'" hat keine Schreibrechte';
  }
  return TRUE;
}

/**
 * Schreibt Plugineigenschaften in die Datei include/plugins.inc.php
 *
 * @param array Array mit den Namen der Plugins aus dem Verzeichnis addons/plugins
 *
 * @return TRUE bei Erfolg, sonst eine Fehlermeldung
 */
function rex_generatePlugins(array $PLUGINS)
{
  global $REX;

  $content = "";
  foreach ($PLUGINS as $addon => $_plugins)
  {
    foreach($_plugins as $plugin)
    {
      if (!rex_ooPlugin :: isInstalled($addon, $plugin))
        rex_ooPlugin::setProperty($addon, $plugin, 'install', 0);

      if (!rex_ooPlugin :: isActivated($addon, $plugin))
        rex_ooPlugin::setProperty($addon, $plugin, 'status', 0);

      foreach(array('install', 'status') as $prop)
      {
        $content .= sprintf(
          "\$REX['ADDON']['plugins']['%s']['%s']['%s'] = '%d';\n",
          $addon,
          $prop,
          $plugin,
          rex_ooPlugin::getProperty($addon, $plugin, $prop)
        );
      }
      $content .= "\n";
    }
  }

  // Da dieser Funktion öfter pro request aufgerufen werden kann,
  // hier die caches löschen
  clearstatcache();

  $file = rex_path::src('config/plugins.inc.php');
  if(rex_replace_dynamic_contents($file, $content) === false)
  {
    return 'Datei "'.$file.'" hat keine Schreibrechte';
  }
  return TRUE;
}

/**
 * Schreibt Spracheigenschaften in die Datei include/clang.inc.php
 *
 * @return TRUE bei Erfolg, sonst eine Fehlermeldung
 */
function rex_generateClang()
{
  global $REX;

  $lg = rex_sql::factory();
  $lg->setQuery("select * from ".$REX['TABLE_PREFIX']."clang order by id");

  $REX['CLANG'] = array();
  while($lg->hasNext())
  {
    $REX['CLANG'][$lg->getValue("id")] = $lg->getValue("name");
    $lg->next();
  }

  $file = rex_path::src('config/clang.inc.php');
  if(rex_replace_dynamic_contents($file, "\$REX['CLANG'] = ". var_export($REX['CLANG'], TRUE) .";\n") === FALSE)
  {
    return 'Datei "'.$file.'" hat keine Schreibrechte';
  }
  return TRUE;
}