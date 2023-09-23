<?php
/**
 * boot redaxo and load packages
 * necessary to use \rexstan\RexStanUserConfig::save()
 */
unset($REX);
$REX['REDAXO'] = true;
$REX['HTDOCS_PATH'] = './';
$REX['BACKEND_FOLDER'] = 'redaxo';
$REX['LOAD_PAGE'] = false;

require './redaxo/src/core/boot.php';
require './redaxo/src/core/packages.php';

$extensions = [
    '../../../../redaxo/src/addons/rexstan/config/rex-superglobals.neon',
    '../../../../redaxo/src/addons/rexstan/vendor/phpstan/phpstan/conf/bleedingEdge.neon',
    '../../../../redaxo/src/addons/rexstan/vendor/phpstan/phpstan-strict-rules/rules.neon',
    '../../../../redaxo/src/addons/rexstan/vendor/phpstan/phpstan-deprecation-rules/rules.neon',
    '../../../../redaxo/src/addons/rexstan/config/phpstan-phpunit.neon',
    '../../../../redaxo/src/addons/rexstan/config/phpstan-dba.neon',
    '../../../../redaxo/src/addons/rexstan/config/cognitive-complexity.neon',
    '../../../../redaxo/src/addons/rexstan/config/code-complexity.neon',
    '../../../../redaxo/src/addons/rexstan/config/dead-code.neon'
];

$paths = [
    'redaxo/src/core',
    'redaxo/src/addons/backup',
    'redaxo/src/addons/be_style',
    'redaxo/src/addons/cronjob',
    'redaxo/src/addons/debug',
    'redaxo/src/addons/install',
    'redaxo/src/addons/media_manager',
    'redaxo/src/addons/mediapool',
    'redaxo/src/addons/metainfo',
    'redaxo/src/addons/phpmailer',
    'redaxo/src/addons/project',
    'redaxo/src/addons/structure',
    'redaxo/src/addons/users',
];

// creates a basic phpstan config file with all extensions
\rexstan\RexStanUserConfig::save(6, $paths, $extensions, 80115);
