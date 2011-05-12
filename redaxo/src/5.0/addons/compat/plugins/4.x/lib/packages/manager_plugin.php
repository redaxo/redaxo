<?php

class rex_pluginManagerCompat extends rex_pluginManager
{
  public function install($installDump = TRUE)
  {
    $state = parent::install($installDump);

    // Dateien kopieren
    $files_dir = $this->package->getBasePath('files');
    if($state === TRUE && is_dir($files_dir))
    {
      if(!rex_dir::copy($files_dir, $this->package->getAssetsPath()))
      {
        $state = $this->I18N('install_cant_copy_files');
      }
    }

    return $state;
  }

  public function includeFile($file)
  {
    global $REX;

    $ADDONsic = $REX['ADDON'];
    $REX['ADDON'] = array();

    $this->package->includeFile($file, array('REX_USER', 'REX_LOGIN', 'I18N', 'article_id', 'clang'));

    if(isset($REX['ADDON']) && is_array($REX['ADDON']))
    {
      foreach($REX['ADDON'] as $property => $propertyArray)
      {
        foreach($propertyArray as $pluginName => $value)
        {
          if($pluginName == $this->package->getName())
          {
            $this->package->setProperty($property, $value);
            $ADDONsic['plugins'][$this->package->getAddon()->getName()][$property][$pluginName] = $value;
          }
        }
      }
    }
    $REX['ADDON'] = $ADDONsic;
  }
}