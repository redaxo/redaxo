<?php

$REX = array();
$REX['REDAXO'] = true;
$REX['HTDOCS_PATH'] = '../';
$REX['BACKEND_FOLDER'] = 'redaxo';

include 'src/core/master.inc.php';

if(rex::isBackend())
{
  require rex_path::core('index_be.inc.php');
}
else
{
  require rex_path::core('index_fe.inc.php');
}