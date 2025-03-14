<?php

declare(strict_types=1);

// total 2 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Impure static variable in pure function rex_escape\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../../redaxo/src/core/functions/function_rex_escape.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
