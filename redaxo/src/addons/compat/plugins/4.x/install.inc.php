<?php

include $this->getBasePath('symlinks.inc.php');

foreach ($symlinks as $link => $target)
{
  if (!is_link($link))
    symlink($target, $link);
}

$this->setProperty('install', true);
