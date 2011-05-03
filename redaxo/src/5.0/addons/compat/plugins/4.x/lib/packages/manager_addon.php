<?php

class rex_addonManagerCompat extends rex_addonManager
{
  public function includeFile($file)
  {
    global $REX;
    $this->package->includeFile($file, array('REX_USER', 'REX_LOGIN', 'I18N', 'article_id', 'clang'));
  }
}