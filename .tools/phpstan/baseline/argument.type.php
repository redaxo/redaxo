<?php

declare(strict_types=1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'rawMessage' => 'Parameter #5 $status of class Redaxo\\Core\\Language\\Language constructor expects bool, int|string|null given.',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Language/Language.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
