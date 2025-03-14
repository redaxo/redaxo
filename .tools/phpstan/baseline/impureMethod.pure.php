<?php

declare(strict_types=1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Database\\\\Sql\\:\\:getRows\\(\\) is marked as impure but does not have any side effects\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Database/Sql.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
