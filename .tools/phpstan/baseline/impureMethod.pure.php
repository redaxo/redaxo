<?php declare(strict_types = 1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Method rex_sql\\:\\:getRows\\(\\) is marked as impure but does not have any side effects\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/core/lib/sql/sql.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
