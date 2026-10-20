<?php

declare(strict_types=1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'rawMessage' => 'Method Redaxo\\Core\\Form\\Field\\PriorityField::organizePriorities() has parameter $ep with generic class Redaxo\\Core\\ExtensionPoint\\ExtensionPoint but does not specify its types: T',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/Field/PriorityField.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
