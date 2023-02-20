<?php

spl_autoload_register(static function (string $class): void {
    $prefix = 'Redaxo\\Rector\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $path = substr($class, strlen($prefix));
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $path).'.php';

    require __DIR__.DIRECTORY_SEPARATOR.$path;
});
