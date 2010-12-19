<?php

error_reporting(E_ALL ^ E_DEPRECATED);

// TODO be_style + agk Skin defaul installed
// TODO addons.inc, plugins.inc entsprechend
// TODO nur sprache XY ins release

// php5 noetig, wg simple_xml
if(version_compare(phpversion(), $needed = '5.0.0', '<') == 1)
{
  echo 'Requires PHP >= '. $needed;
  exit();
}

$name = null;
$version = null;
if(isset($argv) && count($argv) > 1)
{
	if(!empty($argv[1]))
	{
		$version = $argv[1];
	}
	if(!empty($argv[2]))
	{
		$name = $argv[2];
	}

  // Start Build-Script
  buildRelease($name, $version);
}
else
{
  echo '
/**
 * Erstellt ein REDAXO Release.
 *
 *
 * Verwendung in der Console:
 *
 *  Erstelles eines Release mit Versionsnummer:
 *  "php release.php 4.2"
 *
 *
 * Vorgehensweise des release-scripts:
 *  - Ordnerstruktur kopieren nach release/redaxo_<Datum>
 *  - Dateien kopieren
 *  - Sprachdateien zu UTF-8 konvertieren
 *  - CVS Ordner loeschen
 *  - master.inc.php anpassen
 *  - functions.inc.php die compat klasse wird einkommentiert
 */
';
}



function buildRelease($name = null, $version = null)
{
  // Ordner in dem das release gespeichert wird
  // ohne "/" am Ende!
  $cfg_path = 'release';

  // Dateien/Verzeichnisse die in keinem Ordner kopiert werden sollen
  $systemFiles = array(
    '.cache',
    '.settings',
    '.svn',
    '.project',
    '.DS_Store'
  );
  // Dateien/Verzeichnisse die nur in bestimmten Ordnern nicht kopiert werden sollen
  $ignoreFiles = array(
    './release.php',
    './release.xml',
    './db-schema.png',
    './db-schema.xml',
    './test',
    './files',
    './bin',
    './redaxo/include/generated',
    './redaxo/include/addons',
    './'. $cfg_path
  );

  if (!$name)
  {
    $name = 'redaxo';
    if(!$version)
      $name .= date('ymd');
    else
      $name .= str_replace('.', '_', $version);
  }

  if($version)
    $version = explode('.', $version);

  $releaseConfigs = getReleaseConfigs();
  $systemAddons = getSystemAddons();
  $systemName = $name;
  foreach($releaseConfigs as $releaseConfig)
  {
    $path = $cfg_path;
    $name = $systemName .'_'. $releaseConfig['name'];

    if(substr($path, -1) != '/')
      $path .= '/';

    if (!is_dir($path))
      mkdir($path);

    $dest = $path . $name;

    if (is_dir($dest))
      trigger_error('release folder already exists!', E_USER_ERROR);
    else
      mkdir($dest);

    echo '>>> BUILD REDAXO release '. $name .'..'."\n";
    echo '> read files'."\n";

    // Ordner und Dateien auslesen
    echo '> copy files'."\n";
    $structure = readFolderStructure('.',
      array_merge(
        $systemFiles,
        $ignoreFiles
      )
    );
    copyFolderStructure($structure, $dest);

    echo '> copy addons'."\n";
    foreach(array_merge($releaseConfig['addons'], $systemAddons) as $addon)
    {
      echo '>> '.$addon."\n";
      $structure = readFolderStructure(
        './redaxo/include/addons/'. $addon,
        $systemFiles
      );
      copyFolderStructure($structure, $dest);
    }

    // Ordner die wir nicht mitkopiert haben anlegen
    // Der generated Ordner enthält sehr viele Daten,
    // das kopieren würde sehr lange dauern und ist unnötig
    mkdir($dest .'/files');
    mkdir($dest .'/redaxo/include/generated');
    mkdir($dest .'/redaxo/include/generated/articles');
    mkdir($dest .'/redaxo/include/generated/templates');
    mkdir($dest .'/redaxo/include/generated/files');

    echo '> fix master.inc.php'."\n";

    // master.inc.php anpassen
    $master = $dest.'/redaxo/include/master.inc.php';
    $h = fopen($master, 'r');
    $cont = fread($h, filesize($master));
    fclose($h);

    $cont = ereg_replace("(REX\['SETUP'\].?\=.?)[^;]*", '\\1true', $cont);
    $cont = ereg_replace("(REX\['SERVER'\].?\=.?)[^;]*", '\\1"redaxo.de"', $cont);
    $cont = ereg_replace("(REX\['SERVERNAME'\].?\=.?)[^;]*", '\\1"REDAXO"', $cont);
    $cont = ereg_replace("(REX\['ERROR_EMAIL'\].?\=.?)[^;]*", '\\1"jan.kristinus@yakamara.de"', $cont);
    $cont = ereg_replace("(REX\['INSTNAME'\].?\=.?\")[^\"]*", "\\1"."rex".date("Ymd")."000000", $cont);
    $cont = ereg_replace("(REX\['LANG'\].?\=.?)[^;]*", '\\1"de_de"', $cont);
    $cont = ereg_replace("(REX\['START_ARTICLE_ID'\].?\=.?)[^;]*", '\\11', $cont);
    $cont = ereg_replace("(REX\['NOTFOUND_ARTICLE_ID'\].?\=.?)[^;]*", '\\11', $cont);
    $cont = ereg_replace("(REX\['MOD_REWRITE'\].?\=.?)[^;]*", '\\1false', $cont);
    $cont = ereg_replace("(REX\['DEFAULT_TEMPLATE_ID'\].?\=.?)[^;]*", '\\10', $cont);

    $cont = ereg_replace("(REX\['DB'\]\['1'\]\['HOST'\].?\=.?)[^;]*", '\\1"localhost"', $cont);
    $cont = ereg_replace("(REX\['DB'\]\['1'\]\['LOGIN'\].?\=.?)[^;]*", '\\1"root"', $cont);
    $cont = ereg_replace("(REX\['DB'\]\['1'\]\['PSW'\].?\=.?)[^;]*", '\\1""', $cont);

    if($version)
    {
      if(empty($version[1]))
        $version[1] = "0";

      if(empty($version[2]))
        $version[2] = "0";

      $cont = ereg_replace("(REX\['DB'\]\['1'\]\['NAME'\].?\=.?)[^;]*", '\\1"redaxo_'. implode('_', $version) .'"', $cont);
      $cont = ereg_replace("(REX\['VERSION'\].?\=.?)[^;]*"     , '\\1"'. $version[0] .'"', $cont);
      $cont = ereg_replace("(REX\['SUBVERSION'\].?\=.?)[^;]*"  , '\\1"'. $version[1] .'"', $cont);
      $cont = ereg_replace("(REX\['MINORVERSION'\].?\=.?)[^;]*", '\\1"'. $version[2] .'"', $cont);
    }
    else
    {
      $cont = ereg_replace("(REX\['DB'\]\['1'\]\['NAME'\].?\=.?)[^;]*", '\\1"redaxo"', $cont);
    }

    $h = fopen($master, 'w+');
    if (fwrite($h, $cont, strlen($cont)) > 0)
      fclose($h);

    echo '> fix functions.inc.php'."\n";

    // functions.inc.php anpassen
    $functions = $dest.'/redaxo/include/functions.inc.php';
    $h = fopen($functions, 'r');
    $cont = fread($h, filesize($functions));
    fclose($h);

    echo '>> activate compatibility API'."\n";

    // compat klasse aktivieren
    $cont = str_replace(
      "// include_once \$REX['INCLUDE_PATH'].'/core/classes/class.compat.inc.php';",
      "include_once \$REX['INCLUDE_PATH'].'/core/classes/class.compat.inc.php';",
      $cont,
      $count
    );

    if($count != 1)
    {
      trigger_error('Error while activating compat class!', E_USER_ERROR);
      exit();
    }

    $h = fopen($functions, 'w+');
    if (fwrite($h, $cont, strlen($cont)) > 0)
      fclose($h);

    echo '> fix addons.inc.php'."\n";

    // addons.inc.php anpassen
    $addons = $dest.'/redaxo/include/addons.inc.php';
    $h = fopen($addons, 'r');
    $cont = fread($h, filesize($addons));
    fclose($h);

    // Addons installieren
    $cont = ereg_replace("(\/\/.---.DYN.*\/\/.---.\/DYN)", "// --- DYN\n\n// --- /DYN", $cont);

    $h = fopen($addons, 'w+');
    if (fwrite($h, $cont, strlen($cont)) > 0)
      fclose($h);

    echo '>>> BUILD "'. $name .'" Finished'."\n\n";
  }
  echo '> FINISHED'."\n";
}

/**
 * Returns the content of the given folder
 *
 * @param $dir Path to the folder
 * @return Array Content of the folder or false on error
 * @author Markus Staab <markus.staab@redaxo.de>
 */
function readFolder($dir)
{
  if (!is_dir($dir))
  {
    trigger_error('Folder "' . $dir . '" is not available or not a directory');
    return false;
  }
  $hdl = opendir($dir);
  $folder = array ();
  while (false !== ($file = readdir($hdl)))
  {
    $folder[] = $file;
  }

  return $folder;
}

/**
 * Returns the content of the given folder.
 * The content will be filtered with the given $fileprefix
 *
 * @param $dir Path to the folder
 * @param $fileprefix Fileprefix to filter
 * @return Array Filtered-content of the folder or false on error
 * @author Markus Staab <markus.staab@redaxo.de>
 */

function readFilteredFolder($dir, $fileprefix)
{
  $filtered = array ();
  $folder = readFolder($dir);

  if (!$folder)
  {
    return false;
  }

  foreach ($folder as $file)
  {
    if (endsWith($file, $fileprefix))
    {
      $filtered[] = $file;
    }
  }

  return $filtered;
}

/**
 * Returns the files of the given folder
 *
 * @param $dir Path to the folder
 * @return Array Files of the folder or false on error
 * @author Markus Staab <markus.staab@redaxo.de>
 */
function readFolderFiles($dir, $except = array ())
{
  $folder = readFolder($dir);
  $files = array ();

  if (!$folder)
  {
    return false;
  }

  foreach ($folder as $file)
  {
    if (is_file($dir . '/' . $file) && !inExcept($dir, $file, $except))
    {
      $files[] = $file;
    }
  }

  return $files;
}

function inExcept($dir, $file, $excepts = array())
{
  foreach($excepts as $except)
  {
    if(strpos($except, '/') === FALSE)
    {
      // handle relative file except
      if($file == $except) return TRUE;
    }
    else
    {
      // handle absolute except
      if(($dir .'/'. $file) == $except) return TRUE;
    }
  }
  return FALSE;
}

/**
 * Returns the subfolders of the given folder
 *
 * @param $dir Path to the folder
 * @param $ignore_dots True if the system-folders "." and ".." should be ignored
 * @return Array Subfolders of the folder or false on error
 * @author Markus Staab <markus.staab@redaxo.de>
 */
function readSubFolders($dir, $ignore_dots = true)
{
  $folder = readFolder($dir);
  $folders = array ();

  if (!$folder)
  {
    return false;
  }

  foreach ($folder as $file)
  {
    if ($ignore_dots && ($file == '.' || $file == '..'))
    {
      continue;
    }
    if (is_dir($dir . '/' . $file))
    {
      $folders[] = $file;
    }
  }

  return $folders;
}

function readFolderStructure($dir, $except = array ())
{
  $result = array ();

  _readFolderStructure($dir, $except, $result);

  uksort($result, 'sortFolderStructure');

  return $result;
}

function _readFolderStructure($dir, $except, & $result)
{
  $files = readFolderFiles($dir, $except);
  $subdirs = readSubFolders($dir);

  if(is_array($subdirs))
  {
    foreach ($subdirs as $key => $subdir)
    {
      if (inExcept($dir, $subdir, $except))
      {
        unset($subdirs[$key]);
        continue;
      }

      _readFolderStructure($dir .'/'. $subdir, $except, $result);
    }
  }

  $result[$dir] = array_merge($files, $subdirs);

  return $result;
}

function sortFolderStructure($path1, $path2)
{
  return strlen($path1) > strlen($path2) ? 1 : -1;
}

function copyFolderStructure($structure, $dest)
{
  // Ordner/Dateien kopieren
  foreach($structure as $path => $content)
  {
    // Zielordnerstruktur anlegen
    $temp_path = '';
    foreach(explode('/', $dest .'/'. $path) as $pathdir)
    {
      if(!is_dir($temp_path . $pathdir .'/'))
      {
        mkdir($temp_path . $pathdir .'/');
      }
      $temp_path .= $pathdir .'/';
    }

    // Dateien kopieren/Ordner anlegen
    foreach($content as $dir)
    {
      if(is_file($path.'/'.$dir))
      {
        copy($path.'/'.$dir, $dest .'/'. $path.'/'.$dir);

        // create iso lang from utf8 if required
        /*if(substr($dir, -10) == '_utf8.lang')
        {
          $isoLang = substr($dir, 0, -10).'.lang';
          if(!file_exists($isoLang))
          {
            echo '> convert file '. $path .'/'. $dir .' to iso'."\n";
            buildIsoLangFile( $dest .'/'. $path.'/'.$dir, $dir);
          }
        }
        // create utf8 lang from iso if required
        else if (substr($dir, -5) == '.lang')
        {
          $utfLang = substr($dir, 0, -5).'_utf8.lang';
          if(!file_exists($utfLang))
          {
            echo '> convert file '. $path .'/'. $dir .' to utf-8'."\n";
            buildUtf8LangFile( $dest .'/'. $path.'/'.$dir, $dir);
          }
        }*/
      }
      elseif(is_dir($path.'/'.$dir))
      {
        mkdir($dest .'/'. $path.'/'.$dir);
      }
    }
  }
}

function langCharset($lang)
{
	$charset_from = 'iso-8859-1';

	// Wenn neue Sprachdateien mit anderen charsets, dann hier fest einbrennen
  if(substr($lang, 0, 5) == 'cs_cz')
	  $charset_from = 'iso-8859-2';
	else if (substr($lang, 0, 5) == 'sr_sr')
	  $charset_from = 'iso-8859-2';
	else if (substr($lang, 0, 5) == 'tr_tr')
	  $charset_from = 'iso-8859-9';

	return $charset_from;
}

function buildIsoLangFile($langFile, $lang)
{
  $charset_to = langCharset($lang);

  $content = '';
  if($hdl = fopen($langFile, 'r'))
  {
    $content = fread($hdl, filesize($langFile));
    fclose($hdl);

    // Charset auf UTF-8 ändern
    $content = preg_replace('/^htmlcharset = (.*)$/m', 'htmlcharset = '. $charset_to, $content);
  }

  $isoFile = str_replace('_utf8.lang', '.lang', $langFile);
  if($hdl = fopen($isoFile, 'w+'))
  {
    fwrite($hdl, iconv('UTF-8', $charset_to, $content));
    fclose($hdl);
  }
}

function buildUtf8LangFile($langFile, $lang)
{
  $charset_from = langCharset($lang);

  $content = '';
  if($hdl = fopen($langFile, 'r'))
  {
    $content = fread($hdl, filesize($langFile));
    fclose($hdl);

    // Charset auf UTF-8 ändern
    $content = preg_replace('/^htmlcharset = (.*)$/m', 'htmlcharset = utf-8', $content);
  }

  $utf8File = str_replace('.lang', '_utf8.lang', $langFile);
  if($hdl = fopen($utf8File, 'w+'))
  {
    fwrite($hdl, iconv($charset_from, 'UTF-8', $content));
    fclose($hdl);
  }
}

function getReleaseConfigs()
{
  $config_file = 'release.xml';
  if(!file_exists($config_file))
  {
    trigger_error('Required config-file not found "'. $config_file .'"', E_USER_ERROR);
    exit();
  }

  $configs = simplexml_load_file($config_file);
  $releases = array();
  foreach($configs as $config)
  {
    $release = array();
    $release['name'] = xmlAttribute($config, 'name');
    $release['addons'] = array();

    if($config->addons)
    {
      foreach($config->addons[0] as $addon)
      {
        $release['addons'][] = xmlAttribute($addon, 'name');
      }
    }
    $releases[] = $release;
  }
  return $releases;
}

function xmlAttribute($xmlElement, $attrName, $default = null){
    $attrs = $xmlElement->attributes();
    return isset($attrs[$attrName]) ? (string) $attrs[$attrName] : $default;
}

function getSystemAddons()
{
  $master = 'redaxo/include/master.inc.php';
  if(!file_exists($master))
  {
    trigger_error('config "'. $master .'" not found!', E_USER_ERROR);
    exit();
  }

  // Warnungen vermeiden
  $REX = array();
  $REX['GG'] = FALSE;
  $REX['REDAXO'] = TRUE;
  $REX['HTDOCS_PATH'] = './';

  require $master;
  return $REX['SYSTEM_ADDONS'];
}