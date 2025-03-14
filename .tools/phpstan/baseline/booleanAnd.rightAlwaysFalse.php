<?php

declare(strict_types=1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Right side of && is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/Select/CategorySelect.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
