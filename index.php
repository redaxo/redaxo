<?php

unset($REX);
$REX['REDAXO'] = false;
$REX['HTDOCS_PATH'] = './';
$REX['BACKEND_FOLDER'] = 'redaxowewer';
$REX['LOAD_PAGE'] = true;

require $REX['BACKEND_FOLDER'] . '/src/core/boot.php';
