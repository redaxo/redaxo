<?php

$addon = rex_addon::get('structure');

// use path relative to __DIR__ to get correct path in update temp dir
$addon->includeFile(__DIR__ . '/install.php');
