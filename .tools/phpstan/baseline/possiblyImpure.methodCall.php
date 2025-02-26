<?php

declare(strict_types=1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Possibly impure call to method stdClass\\:\\:__toString\\(\\) in pure function rex_escape\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/core/functions/function_rex_escape.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
