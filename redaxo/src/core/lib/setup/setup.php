<?php

class rex_setup
{
  const MIN_PHP_VERSION = '5.3.0';
  const MIN_MYSQL_VERSION = '5.0';

  private static $MIN_PHP_EXTENSIONS = array('session', 'pdo', 'pcre');

  /**
   * very basic setup steps, so everything is in place for our browser-based setup wizard.
   *
   * @param string $skinAddon
   * @param string $skinPlugin
   */
  public static function init($skinAddon = 'be_style', $skinPlugin = 'redaxo')
  {
    // initial purge all generated files
    rex_deleteCache();

    // copy alle media files of the current rex-version into redaxo_media
    rex_dir::copy(rex_path::core('assets'), rex_path::assets('', rex_path::ABSOLUTE));

    // copy skins files/assets
    rex_dir::copy(rex_path::plugin($skinAddon, $skinPlugin, 'assets'), rex_path::pluginAssets($skinAddon, $skinPlugin, '', rex_path::ABSOLUTE));
  }

  /**
   * checks environment related conditions
   *
   * @return array An array of error messages
   */
  public static function checkEnvironment()
  {
    $errors = array();

    // -------------------------- VERSIONSCHECK
    if (version_compare(phpversion(), self::MIN_PHP_VERSION, '<') == 1)
    {
      $errors[] = rex_i18n::msg('setup_010', phpversion(), self::MIN_PHP_VERSION);
    }

    // -------------------------- EXTENSION CHECK
    foreach(self::$MIN_PHP_EXTENSIONS as $extension)
    {
      if(!extension_loaded($extension))
        $errors[] = rex_i18n::msg('setup_010_1', $extension);
    }

    return $errors;
  }

  /**
   * checks permissions of all required filesystem resources
   *
   * @return array An array of error messages
   */
  public static function checkFilesystem()
  {
    $export_addon_dir = rex_path::addon('import_export');
    require_once $export_addon_dir.'/functions/function_folder.inc.php';
    require_once $export_addon_dir.'/functions/function_import_folder.inc.php';

    // -------------------------- SCHREIBRECHTE
    $WRITEABLES = array (
        rex_path::media('', rex_path::ABSOLUTE),
        rex_path::media('_readme.txt', rex_path::ABSOLUTE),
        rex_path::assets('', rex_path::ABSOLUTE),
        rex_path::assets('_readme.txt', rex_path::ABSOLUTE),
        rex_path::cache(),
        rex_path::data(),
        rex_path::data('config.yml'),
        getImportDir()
    );

    foreach(rex::getProperty('system_addons') as $system_addon)
    {
      $WRITEABLES[] = rex_path::addon($system_addon);
    }

    $res = array();
    foreach($WRITEABLES as $item)
    {
      // Fehler unterdrücken, falls keine Berechtigung
      if(@is_dir($item))
      {
        if(!@is_writable($item . '/.'))
        {
          $res['setup_012'][] = $item;
        }
      }
      // Fehler unterdrücken, falls keine Berechtigung
      elseif(@is_file($item))
      {
        if(!@is_writable($item))
        {
          $res['setup_014'][] = $item;
        }
      }
      else
      {
        $res['setup_015'][] = $item;
      }
    }

    return $res;
  }

  /**
   * Checks the version of the connected database server.
   *
   * @param $config array of databaes configs
   * @param $createDb boolean Should the database be created, if it not exists.
   */
  public static function checkDb($config, $createDb)
  {
    $err = rex_sql::checkDbConnection($config['db'][1]['host'], $config['db'][1]['login'], $config['db'][1]['password'], $config['db'][1]['name'], $createDb);
    if($err !== true)
    {
      return $err;
    }

    $serverVersion = rex_sql::getServerVersion();
    if (rex_string::compareVersions($serverVersion, self::MIN_MYSQL_VERSION, '<') == 1)
    {
      return rex_i18n::msg('setup_022_1', $serverVersion, self::MIN_MYSQL_VERSION);
    }
    return '';
  }
}
