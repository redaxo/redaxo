<?php

declare(strict_types=1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^If condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Http/Response.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
