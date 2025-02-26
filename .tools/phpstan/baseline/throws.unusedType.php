<?php declare(strict_types = 1);

// total 2 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Method rex_socket\\:\\:doDelete\\(\\) has rex_socket_exception in PHPDoc @throws tag but it\'s not thrown\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/core/lib/util/socket/socket.php',
];
$ignoreErrors[] = [
    'message' => '#^Method rex_socket\\:\\:doGet\\(\\) has rex_socket_exception in PHPDoc @throws tag but it\'s not thrown\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../redaxo/src/core/lib/util/socket/socket.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
