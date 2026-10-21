<?php

declare(strict_types=1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'rawMessage' => 'Call to method addDirectory() of deprecated class Redaxo\\Core\\View\\Fragment:
The fragment system will be removed before the 6.0 release',
    'count' => 1,
    'path' => __DIR__ . '/../../../boot/core.php',
];
$ignoreErrors[] = [
    'rawMessage' => 'Call to method addDirectory() of deprecated class Redaxo\\Core\\View\\Fragment:
The fragment system will be removed before the 6.0 release',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/AbstractProject.php',
];
$ignoreErrors[] = [
    'rawMessage' => 'Call to method addDirectory() of deprecated class Redaxo\\Core\\View\\Fragment:
The fragment system will be removed before the 6.0 release',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Addon/Addon.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
