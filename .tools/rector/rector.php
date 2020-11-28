<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Rector\Core\ValueObject\PhpVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();

    // Define what rule sets will be applied
    $parameters->set(Option::SETS, [
        // SetList::DEAD_CODE,
    ]);

    // this list will grow over time.
    // to make sure we can review every transformation and not introduce unseen bugs
    $parameters->set(Option::PATHS, [
        'redaxo/src/core/lib/',
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

    // todo
    // $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_7_3);

    // get services (needed for register a single rule)
    $services = $containerConfigurator->services();

    // we will grow this rector list step by step.
    // after some basic rectors have been enabled we can finally enable whole-sets (when diffs get stable and reviewable)
    $services->set(Rector\SOLID\Rector\If_\ChangeAndIfToEarlyReturnRector::class);
};
