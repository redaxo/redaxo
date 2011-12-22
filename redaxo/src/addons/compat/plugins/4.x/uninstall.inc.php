<?php

include $this->getBasePath('symlinks.inc.php');

foreach(array_reverse($symlinks) as $link => $target)
{
  if(is_link($link))
    unlink($link);
}

$this->setProperty('install', false);