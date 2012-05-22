<?php

/**
* Compat class for addon manager
*
* Realizes compatibility for $REX['ADDON'] and old "files" folders
*
* @author gharlan
*/
class rex_plugin_manager_compat extends rex_plugin_manager
{
  public function install($installDump = TRUE)
  {
    $state = parent::install($installDump);

    // Dateien kopieren
    $files_dir = $this->package->getBasePath('files');
    if ($state === TRUE && is_dir($files_dir))
    {
      if (!rex_dir::copy($files_dir, $this->package->getAssetsPath('', rex_path::ABSOLUTE)))
      {
        $state = $this->I18N('install_cant_copy_files');
      }
    }

    return $state;
  }

  static public function includeFile(rex_package $package, $file)
  {
    global $REX, $ADDONsic;

    $transform = false;
    if (in_array($file, array('config.inc.php', 'install.inc.php', 'uninstall.inc.php')))
    {
      $ADDONsic = isset($REX['ADDON']) ? $REX['ADDON'] : array();
      $REX['ADDON'] = array();
      $transform = true;
    }

    $compatPackage = new rex_package_compat($package);
    $compatPackage->includeFile($file);

    $addonName = $package->getAddon()->getName();
    if ($transform)
    {
      $array = isset($REX['ADDON']) ? $REX['ADDON'] : array();
      $REX['ADDON'] = $ADDONsic;
    }
    else
    {
      $array = isset($REX['ADDON']['plugins'][$addonName]) ? $REX['ADDON']['plugins'][$addonName] : array();
    }
    if (isset($array) && is_array($array))
    {
      foreach ($array as $property => $propertyArray)
      {
        foreach ($propertyArray as $pluginName => $value)
        {
          if ($pluginName == $package->getName())
          {
            $package->setProperty($property, $value);
            $REX['ADDON']['plugins'][$addonName][$property][$pluginName] = $value;
          }
        }
      }
    }
  }
}
