<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Naming\Rector\Property\UnderscoreToCamelCasePropertyNameRector;
use Rector\Naming\Rector\Variable\UnderscoreToCamelCaseVariableNameRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();

    // Define what rule sets will be applied
    $parameters->set(Option::SETS, [
        // SetList::EARLY_RETURN,
    ]);

    $parameters->set(OPTION::OPTION_AUTOLOAD_FILE, [
        __DIR__.'/../constants.php',
    ]);

    // this list will grow over time.
    // to make sure we can review every transformation and not introduce unseen bugs
    $parameters->set(Option::PATHS, [
        // restrict to core and core addons, ignore other locally installed addons
        'redaxo/src/core/',
        'redaxo/src/addons/backup/',
        'redaxo/src/addons/be_style/',
        'redaxo/src/addons/cronjob/',
        'redaxo/src/addons/debug/',
        'redaxo/src/addons/install/',
        'redaxo/src/addons/media_manager/',
        'redaxo/src/addons/mediapool/',
        'redaxo/src/addons/metainfo/',
        'redaxo/src/addons/phpmailer/',
        'redaxo/src/addons/project/',
        'redaxo/src/addons/structure/',
        'redaxo/src/addons/users/',
    ]);

    $parameters->set(Option::SKIP, [
        // skip because of phpdocs which get mangled https://github.com/rectorphp/rector/issues/4691
        // 'redaxo/src/core/lib/fragment.php',
        // 'redaxo/src/core/lib/list.php',
        // 'redaxo/src/core/lib/packages/manager.php',
        // 'redaxo/src/core/lib/sql/sql.php',
        // 'redaxo/src/core/lib/var/var.php',
        // 'redaxo/src/core/lib/util/version.php',
        'redaxo/src/core/vendor',
        'redaxo/src/addons/backup/vendor',
        'redaxo/src/addons/be_style/vendor',
        'redaxo/src/addons/phpmailer/vendor',
    ]);

    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_73);

    $parameters->set(Option::ENABLE_CACHE, true);

    // get services (needed for register a single rule)
    $services = $containerConfigurator->services();

    // we will grow this rector list step by step.
    // after some basic rectors have been enabled we can finally enable whole-sets (when diffs get stable and reviewable)
    // $services->set(Rector\SOLID\Rector\If_\ChangeAndIfToEarlyReturnRector::class);
    $services->set(UnderscoreToCamelCasePropertyNameRector::class);
    $services->set(UnderscoreToCamelCaseVariableNameRector::class);
};
