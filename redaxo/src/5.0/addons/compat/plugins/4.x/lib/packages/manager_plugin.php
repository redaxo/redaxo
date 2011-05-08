<?php

class rex_pluginManagerCompat extends rex_pluginManager
{
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