<?php

declare(strict_types=1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'rawMessage' => 'Access to constant on deprecated class Redaxo\\Core\\View\\Fragment:
The fragment system will be removed before the 6.0 release',
    'count' => 1,
    'path' => __DIR__ . '/../../../rector.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
