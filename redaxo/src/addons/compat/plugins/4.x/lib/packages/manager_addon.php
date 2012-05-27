<?php

/**
 * Compat class for addon manager
 *
 * Realizes compatibility for $REX['ADDON'] and old "files" folders
 *
 * @author gharlan
 */
class rex_addon_manager_compat extends rex_addon_manager
{
  public function install($installDump = true)
  {
    $state = parent::install($installDump);

    // Dateien kopieren
    $files_dir = $this->package->getBasePath('files');
    if ($state === true && is_dir($files_dir)) {
      if (!rex_dir::copy($files_dir, $this->package->getAssetsPath('', rex_path::ABSOLUTE))) {
        $state = $this->I18N('install_cant_copy_files');
      }
    }

    return $state;
  }

  static public function includeFile(rex_package $package, $file)
  {
    global $REX;

    $compatPackage = new rex_package_compat($package);
    $compatPackage->includeFile($file);

    if (isset($REX['ADDON']) && is_array($REX['ADDON'])) {
      foreach ($REX['ADDON'] as $property => $propertyArray) {
        foreach ($propertyArray as $addonName => $value) {
          if ($addonName == $package->getName()) {
            $package->setProperty($property, $value);
          }
        }
      }
      /**
       * @deprecated 4.2
       */
      if (isset($REX['ADDON'][$package->getName()]['SUBPAGES'])) {
        $package->setProperty('pages', $REX['ADDON'][$package->getName()]['SUBPAGES']);
      }
    }
  }
}
