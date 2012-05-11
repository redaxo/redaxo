<?php

class rex_setup_importer
{
  public static function updateFromPrevious()
  {
    // ----- vorhandenen seite updaten
    $err_msg = '';
    
    $import_sql = rex_path::core('install/update4_x_to_5_0.sql');
    if($err_msg == '')
      $err_msg .= self::rex_setup_import($import_sql);
    
    // Aktuelle Daten updaten wenn utf8, da falsch in v4.2.1 abgelegt wurde.
    /*if (rex_lang_is_utf8())
     {
    rex_setup_setUtf8();
    }*/
    
    if($err_msg == '')
      $err_msg .= self::rex_setup_addons();
    
    return $err_msg;
  }
  
  public static function loadExistingImport($import_name)
  {
    // ----- vorhandenen Export importieren
    $err_msg = '';
    
    if($import_name == '')
    {
      $err_msg .= '<p>'.rex_i18n::msg('setup_03701').'</p>';
    }
    else
    {
      $import_sql = getImportDir().'/'.$import_name.'.sql';
      $import_archiv = getImportDir().'/'.$import_name.'.tar.gz';
    
      // Nur hier zuerst die Addons installieren
      // Da sonst Daten aus dem eingespielten Export
      // Überschrieben würden
      if($err_msg == '')
        $err_msg .= self::rex_setup_addons(true, false);
      if($err_msg == '')
        $err_msg .= self::rex_setup_import($import_sql, $import_archiv);
    }
    
    return $err_msg;    
  }
  
  public static function databaseAlreadyExists()
  {
    // ----- db schon vorhanden, nichts tun
    return self::rex_setup_addons(false, false);
  }
  
  public static function overrideExisting()
  {
    // ----- volle Datenbank, alte DB löschen / drop
    $err_msg = '';
    
    $import_sql = rex_path::core('install/redaxo5_0.sql');
    
    $db = rex_sql::factory();
    foreach(self::getRequiredTables() as $table)
      $db->setQuery('DROP TABLE IF EXISTS `'. $table .'`');
    
    if($err_msg == '')
      $err_msg .= self::rex_setup_import($import_sql);
    
    if($err_msg == '')
      $err_msg .= self::rex_setup_addons(true);

    return $err_msg;
  }
  
  public static function prepareEmptyDb()
  {
    // ----- leere Datenbank neu einrichten
    $err_msg = '';
    $import_sql = rex_path::core('install/redaxo5_0.sql');
    
    if($err_msg == '')
      $err_msg .= self::rex_setup_import($import_sql);
    
    $err_msg .= self::rex_setup_addons();
    
    return $err_msg;
  }
  
  public static function verifyDbSchema()
  {
    $err_msg = '';
    
    // Prüfen, welche Tabellen bereits vorhanden sind
    $existingTables = array();
    foreach(rex_sql::showTables() as $tblname)
    {
      if (substr($tblname, 0, strlen(rex::getTablePrefix())) == rex::getTablePrefix())
      {
        $existingTables[] = $tblname;
      }
    }
    
    foreach(array_diff(self::getRequiredTables(), $existingTables) as $missingTable)
    {
      $err_msg .= rex_i18n::msg('setup_031', $missingTable)."<br />";
    }
    return $err_msg;
  }
  
  private static function getRequiredTables()
  {
    return array (
        rex::getTablePrefix() .'clang',
        rex::getTablePrefix() .'user',
        rex::getTablePrefix() .'config'
    );
  }
  
  private static function rex_setup_import($import_sql, $import_archiv = null)
  {
    global $export_addon_dir;
  
    $err_msg = '';
  
    if (!is_dir($export_addon_dir))
    {
      $err_msg .= rex_i18n::msg('setup_03703').'<br />';
    }
    else
    {
      if (file_exists($import_sql) && ($import_archiv === null || $import_archiv !== null && file_exists($import_archiv)))
      {
        rex_i18n::addDirectory(rex_path::addon('import_export', 'lang/'));
  
        // DB Import
        $state_db = rex_a1_import_db($import_sql);
        if ($state_db['state'] === false)
        {
          $err_msg .= nl2br($state_db['message']) .'<br />';
        }
  
        // Archiv optional importieren
        if ($state_db['state'] === true && $import_archiv !== null)
        {
          $state_archiv = rex_a1_import_files($import_archiv);
          if ($state_archiv['state'] === false)
          {
            $err_msg .= $state_archiv['message'].'<br />';
          }
        }
      }
      else
      {
        $err_msg .= rex_i18n::msg('setup_03702').'<br />';
      }
    }
  
    return $err_msg;
  }
  
  // -------------------------- System AddOns prüfen
  private static function rex_setup_addons($uninstallBefore = false, $installDump = true)
  {
    $addonErr = '';
    rex_package_manager::synchronizeWithFileSystem();
  
    if($uninstallBefore)
    {
      foreach(array_reverse(rex::getProperty('system_addons')) as $packageRepresentation)
      {
        $package = rex_package::get($packageRepresentation);
        $manager = rex_package_manager::factory($package);
        $state = $manager->uninstall($installDump);
        // echo "uninstall ". $packageRepresentation ."<br />";
  
        if($state !== true)
          $addonErr .= '<li>'. $package->getPackageId() .'<ul><li>'. $manager->getMessage() .'</li></ul></li>';
      }
    }
    foreach(rex::getProperty('system_addons') as $packageRepresentation)
    {
      $state = true;
      $package = rex_package::get($packageRepresentation);
      $manager = rex_package_manager::factory($package);
  
      if($state === true && !$package->isInstalled())
      {
        // echo "install ". $packageRepresentation."<br />";
        $state = $manager->install($installDump);
      }
  
      if($state !== true)
        $addonErr .= '<li>'. $package->getPackageId() .'<ul><li>'. $manager->getMessage() .'</li></ul></li>';
  
      if($state === true && !$package->isActivated())
      {
        // echo "activate ". $packageRepresentation."<br />";
        $state = $manager->activate();
  
        if($state !== true)
          $addonErr .= '<li>'. $package->getPackageId() .'<ul><li>'. $manager->getMessage() .'</li></ul></li>';
      }
    }
  
    if($addonErr != '')
    {
      $addonErr = '<ul class="rex-ul1">
      <li>
      <h3 class="rex-hl3">'. rex_i18n::msg('setup_011', '<span class="rex-error">', '</span>') .'</h3>
      <ul>'. $addonErr .'</ul>
      </li>
      </ul>';
    }
  
    return $addonErr;
  }
  
  /*function rex_setup_setUtf8()
   {
  global $REX;
  $gt = rex_sql::factory();
  $gt->setQuery("show tables");
  foreach($gt->getArray() as $t) {
  $table = $t["Tables_in_".$REX['DB']['1']['NAME']];
  $gc = rex_sql::factory();
  $gc->setQuery("show columns from $table");
  if(substr($table,0,strlen(rex::getTablePrefix())) == rex::getTablePrefix()) {
  $columns = Array();
  $pri = "";
  foreach($gc->getArray() as $c) {
  $columns[] = $c["Field"];
  if ($pri == "" && $c["Key"] == "PRI") {
  $pri = $c["Field"];
  }
  }
  if ($pri != "") {
  $gr = rex_sql::factory();
  $gr->setQuery("select * from $table");
  foreach($gr->getArray() as $r) {
  reset($columns);
  $privalue = $r[$pri];
  $uv = rex_sql::factory();
  $uv->setTable($table);
  $uv->setWhere(array($pri => $privalue));
  foreach($columns as $key => $column) {
  if ($pri!=$column) {
  $value = $r[$column];
  $newvalue = utf8_decode($value);
  $uv->setValue($column,$newvalue);
  }
  }
  $uv->update();
  }
  }
  }
  }
  }*/
}