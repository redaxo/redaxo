<?php

$emptyDir = $this->getBasePath('emptydir');
$emptyFile = $this->getBasePath('emptydir/emptyfile');

$symlinks = array(
  rex_path::src('layout') => $emptyDir,
  rex_path::src('layout/top.php') => $emptyFile,
  rex_path::src('layout/bottom.php') => $emptyFile,
  rex_path::base('files') => rex_path::media('', rex_path::ABSOLUTE),
  rex_path::base('files/addons') => rex_path::assets('addons', rex_path::ABSOLUTE)
);