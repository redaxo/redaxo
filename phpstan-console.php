<?php

unset($REX);
$REX['REDAXO'] = true;
$REX['HTDOCS_PATH'] = './';
$REX['BACKEND_FOLDER'] = 'redaxo';

require __DIR__.'/redaxo/src/core/console.php';
return rex::getConsole();
