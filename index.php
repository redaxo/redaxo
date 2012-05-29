<?php

$REX = array();
$REX['REDAXO'] = false;
$REX['HTDOCS_PATH'] = './';
$REX['BACKEND_FOLDER'] = 'redaxo';

require $REX['BACKEND_FOLDER'] . '/src/core/master.inc.php';

require rex_path::core('index_fe.inc.php');
