<?php

declare(strict_types=1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'rawMessage' => 'Method Redaxo\\Core\\Form\\Field\\ContainerField::addField() has parameter $value with no type specified.',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/Field/ContainerField.php',
];
$ignoreErrors[] = [
    'rawMessage' => 'Method Redaxo\\Core\\Form\\Field\\ContainerField::addGroupedField() has parameter $value with no type specified.',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/Field/ContainerField.php',
];
$ignoreErrors[] = [
    'rawMessage' => 'Method Redaxo\\Core\\Form\\Field\\ControlField::submitted() has parameter $element with no type specified.',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/Field/ControlField.php',
];
$ignoreErrors[] = [
    'rawMessage' => 'Method Redaxo\\Core\\Form\\Field\\PriorityField::setWhereCondition() has parameter $whereCondition with no type specified.',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/Field/PriorityField.php',
];
$ignoreErrors[] = [
    'rawMessage' => 'Method Redaxo\\Core\\Form\\Field\\SelectField::setSeparator() has parameter $separator with no type specified.',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/Field/SelectField.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
