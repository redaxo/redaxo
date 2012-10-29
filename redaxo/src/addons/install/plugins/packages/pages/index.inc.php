<?php

$subsubpage = rex_be_controller::getCurrentPagePart(2) ?: 'update';

require __DIR__ . DIRECTORY_SEPARATOR . $subsubpage . '.inc.php';
