<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();

    // Define what rule sets will be applied
    $parameters->set(Option::SETS, [
        SetList::EARLY_RETURN,
    ]);

    $parameters->set(OPTION::OPTION_AUTOLOAD_FILE, [
        __DIR__.'/../constants.php',
    ]);

    // this list will grow over time.
    // to make sure we can review every transformation and not introduce unseen bugs
    $parameters->set(Option::PATHS, [
        // restrict to core and core addons, ignore other locally installed addons
        'redaxo/src/core/lib/',
        'redaxo/src/addons/backup/lib/',
        'redaxo/src/addons/be_style/lib/',
        'redaxo/src/addons/cronjob/lib/',
        'redaxo/src/addons/debug/lib/',
        'redaxo/src/addons/install/lib/',
        'redaxo/src/addons/media_manager/lib/',
        'redaxo/src/addons/mediapool/lib/',
        'redaxo/src/addons/metainfo/lib/',
        'redaxo/src/addons/phpmailer/lib/',
        'redaxo/src/addons/project/lib/',
        'redaxo/src/addons/structure/lib/',
        'redaxo/src/addons/users/lib/',
    ]);

    $parameters->set(Option::SKIP, [
        // skip because of phpdocs which get mangled https://github.com/rectorphp/rector/issues/4691
        'redaxo/src/core/lib/fragment.php',
        'redaxo/src/core/lib/list.php',
        'redaxo/src/core/lib/packages/manager.php',
        'redaxo/src/core/lib/sql/sql.php',
        'redaxo/src/core/lib/var/var.php',
        'redaxo/src/core/lib/util/version.php',
    ]);

    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_7_3);

    // get services (needed for register a single rule)
    $services = $containerConfigurator->services();

    // we will grow this rector list step by step.
    // after some basic rectors have been enabled we can finally enable whole-sets (when diffs get stable and reviewable)
    // $services->set(Rector\SOLID\Rector\If_\ChangeAndIfToEarlyReturnRector::class);
};
