<?php

// For static analysis we do not boot redaxo.
// But to avoid errors about non-existing constants (usually defined while booting redaxo), we define them here.

define('REX_MIN_PHP_VERSION', json_decode(file_get_contents(__DIR__.'/../redaxo/src/core/composer.json'), true)['config']['platform']['php']);
