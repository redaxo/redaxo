<?php

declare(strict_types=1);

// total 14 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Form\\\\Field\\\\PriorityField\\:\\:organizePriorities\\(\\) has parameter \\$ep with generic class Redaxo\\\\Core\\\\ExtensionPoint\\\\ExtensionPoint but does not specify its types\\: T$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Form/Field/PriorityField.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\MediaManager\\:\\:mediaIsInUse\\(\\) has parameter \\$ep with generic class Redaxo\\\\Core\\\\ExtensionPoint\\\\ExtensionPoint but does not specify its types\\: T$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MediaManager/MediaManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MediaManager\\\\MediaManager\\:\\:mediaUpdated\\(\\) has parameter \\$ep with generic class Redaxo\\\\Core\\\\ExtensionPoint\\\\ExtensionPoint but does not specify its types\\: T$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MediaManager/MediaManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\AbstractHandler\\:\\:extendForm\\(\\) has parameter \\$ep with generic class Redaxo\\\\Core\\\\ExtensionPoint\\\\ExtensionPoint but does not specify its types\\: T$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/AbstractHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\ArticleHandler\\:\\:extendForm\\(\\) has parameter \\$ep with generic class Redaxo\\\\Core\\\\ExtensionPoint\\\\ExtensionPoint but does not specify its types\\: T$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/ArticleHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\CategoryHandler\\:\\:extendForm\\(\\) has parameter \\$ep with generic class Redaxo\\\\Core\\\\ExtensionPoint\\\\ExtensionPoint but does not specify its types\\: T$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/CategoryHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\CategoryHandler\\:\\:renderToggleButton\\(\\) has parameter \\$ep with generic class Redaxo\\\\Core\\\\ExtensionPoint\\\\ExtensionPoint but does not specify its types\\: T$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/CategoryHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\LanguageHandler\\:\\:extendForm\\(\\) has parameter \\$ep with generic class Redaxo\\\\Core\\\\ExtensionPoint\\\\ExtensionPoint but does not specify its types\\: T$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/LanguageHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\LanguageHandler\\:\\:renderToggleButton\\(\\) has parameter \\$ep with generic class Redaxo\\\\Core\\\\ExtensionPoint\\\\ExtensionPoint but does not specify its types\\: T$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/LanguageHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\MediaHandler\\:\\:extendForm\\(\\) has parameter \\$ep with generic class Redaxo\\\\Core\\\\ExtensionPoint\\\\ExtensionPoint but does not specify its types\\: T$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/MediaHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\Handler\\\\MediaHandler\\:\\:isMediaInUse\\(\\) has parameter \\$ep with generic class Redaxo\\\\Core\\\\ExtensionPoint\\\\ExtensionPoint but does not specify its types\\: T$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/Handler/MediaHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\MetaInfo\\:\\:cleanup\\(\\) has parameter \\$epOrParams with generic class Redaxo\\\\Core\\\\ExtensionPoint\\\\ExtensionPoint but does not specify its types\\: T$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/MetaInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\MetaInfo\\\\MetaInfo\\:\\:extensionHandler\\(\\) has parameter \\$ep with generic class Redaxo\\\\Core\\\\ExtensionPoint\\\\ExtensionPoint but does not specify its types\\: T$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/MetaInfo/MetaInfo.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Redaxo\\\\Core\\\\Security\\\\UserRole\\:\\:removeOrReplaceItem\\(\\) has parameter \\$ep with generic class Redaxo\\\\Core\\\\ExtensionPoint\\\\ExtensionPoint but does not specify its types\\: T$#',
    'count' => 1,
    'path' => __DIR__ . '/../../../src/Security/UserRole.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
