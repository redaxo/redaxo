<?php

chdir(dirname(__DIR__).'/redaxo');

unset($REX);
$REX['REDAXO'] = true;
$REX['HTDOCS_PATH'] = '../';
$REX['BACKEND_FOLDER'] = 'redaxo';
$REX['LOAD_PAGE'] = false;

require dirname(__DIR__).'/redaxo/src/core/boot.php';
require dirname(__DIR__).'/redaxo/src/core/packages.php';
