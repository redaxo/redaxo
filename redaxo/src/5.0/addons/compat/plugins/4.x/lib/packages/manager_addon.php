<?php

class rex_addonManagerCompat extends rex_addonManager
{
  public function includeFile($file)
  {
    global $REX;

    $this->package->includeFile($file, array('REX_USER', 'REX_LOGIN', 'I18N', 'article_id', 'clang'));

    if(isset($REX['ADDON']) && is_array($REX['ADDON']))
    {
      foreach($REX['ADDON'] as $property => $propertyArray)
      {
        foreach($propertyArray as $addonName => $value)
        {
          if($addonName == $this->package->getName())
          {
            $this->package->setProperty($property, $value);
          }
        }
      }
    }
  }
}