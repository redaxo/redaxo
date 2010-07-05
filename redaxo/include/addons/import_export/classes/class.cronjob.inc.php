<?php

class rex_cronjob_export extends rex_cronjob
{ 
  /*public*/ function execute()
  {
    global $REX;
    
    include_once $REX['INCLUDE_PATH'] .'/addons/import_export/functions/function_import_export.inc.php';
    include_once $REX['INCLUDE_PATH'] .'/addons/import_export/functions/function_import_folder.inc.php';
    
    $file = 'rex_'. $REX['VERSION'] .'_'. date("Ymd");
    $dir = getImportDir() .'/';
    $ext = '.sql';
    if (file_exists($dir . $file . $ext))
    {
      $i = 1;
      while (file_exists($dir . $file .'_'. $i . $ext)) $i++;
      $file = $file .'_'. $i;
    }

    if (rex_a1_export_db($dir . $file . $ext))
    {
      $this->setMessage($file . $ext . ' created');
      return true;
    }
    return false;
  }
  
  /*public*/ function getTypeName()
  {
    global $I18N;
    return $I18N->msg('im_export_database_export');
  }
}