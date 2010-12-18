<?php

// the standard.js tries to call this file directly (security check)
if (isset($REX))
{

  $REX['VERSION_FOLDER'] = "5.0"; // Versionfolder
  $REX['SRC_PATH'] = realpath($REX['HTDOCS_PATH'] .'/redaxo/include/'. $REX['VERSION_FOLDER']);

  if($REX['REDAXO'])
  	include($REX['SRC_PATH'] .'/core/index_be.inc.php');
  else
  	include($REX['SRC_PATH'] .'/core/index_fe.inc.php');

}