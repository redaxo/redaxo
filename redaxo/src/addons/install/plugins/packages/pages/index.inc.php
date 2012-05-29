<?php

$subsubpage = rex_request('subsubpage', 'string') ?: 'update';

require __DIR__ . DIRECTORY_SEPARATOR . $subsubpage . '.inc.php';
