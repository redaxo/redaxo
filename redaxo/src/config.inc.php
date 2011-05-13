<?php

$REX['VERSION_FOLDER'] = "5.0"; // Versionfolder

require realpath($REX['HTDOCS_PATH'] .'/redaxo/src/'. $REX['VERSION_FOLDER'] .'/core/lib/path.php');
rex_path::init($REX['HTDOCS_PATH'], $REX['VERSION_FOLDER']);

if($REX['REDAXO'])
	include rex_path::core('index_be.inc.php');
else
	include rex_path::core('index_fe.inc.php');