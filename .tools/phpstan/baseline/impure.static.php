<?php

declare(strict_types=1);

// total 2 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Impure static variable in pure function Redaxo\\\\Core\\\\View\\\\escape\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/View/escape.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
