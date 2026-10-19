<?php

declare(strict_types=1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'rawMessage' => 'Parameter #1 $options of method Redaxo\\Core\\Form\\Field\\AbstractOptionField::addOptions() expects array<array{0: string, 1?: int|string}|string>, list<array<int, bool|float|int|string|null>> given.',
    'count' => 2,
    'path' => __DIR__ . '/../../../src/Form/Field/AbstractOptionField.php',
];
$ignoreErrors[] = [
    'rawMessage' => 'Parameter #5 $status of class Redaxo\\Core\\Language\\Language constructor expects bool, int|string|null given.',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Language/Language.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
