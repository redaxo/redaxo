<?php declare(strict_types = 1);

// total 2 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array\\<non\\-falsy\\-string, int\\|non\\-falsy\\-string\\> will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/addons/mediapool/lib/service_media.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with array will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/core/lib/sql/util.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
