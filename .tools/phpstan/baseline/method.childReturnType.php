<?php

declare(strict_types=1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Return type \\(void\\) of method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\LanguageHandler\\:\\:buildFilterCondition\\(\\) should be compatible with return type \\(string\\) of method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\AbstractHandler\\:\\:buildFilterCondition\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/LanguageHandler.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
