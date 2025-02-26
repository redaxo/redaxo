<?php declare(strict_types = 1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Unsafe call to private method rex_sql_table\\:\\:baseClearInstance\\(\\) through static\\:\\:\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/core/lib/sql/table.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
