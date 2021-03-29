<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php80\Rector\Identical\StrEndsWithRector;
use Rector\Php80\Rector\Identical\StrStartsWithRector;
use Rector\Php80\Rector\NotIdentical\StrContainsRector;
use Rector\Set\ValueObject\SetList;
use Redaxo\Rector\UnderscoreCamelCaseConflictingNameGuard;
use Redaxo\Rector\UnderscoreCamelCaseExpectedNameResolver;
use Redaxo\Rector\UnderscoreCamelCasePropertyRenamer;
use Redaxo\Rector\UnderscoreToCamelCasePropertyNameRector;
use Redaxo\Rector\UnderscoreToCamelCaseVariableNameRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();

    // Define what rule sets will be applied
    $parameters->set(Option::SETS, [
        // SetList::EARLY_RETURN,
    ]);

    $parameters->set(OPTION::BOOTSTRAP_FILES, [
        __DIR__.'/.tools/constants.php',
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
        'redaxo/src/core/vendor',
        'redaxo/src/addons/backup/vendor',
        'redaxo/src/addons/be_style/vendor',
        'redaxo/src/addons/debug/vendor',
        'redaxo/src/addons/phpmailer/vendor',
    ]);

    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_73);

    $parameters->set(Option::ENABLE_CACHE, true);

    // get services (needed for register a single rule)
    $services = $containerConfigurator->services();

    // we will grow this rector list step by step.
    // after some basic rectors have been enabled we can finally enable whole-sets (when diffs get stable and reviewable)
    // $services->set(Rector\SOLID\Rector\If_\ChangeAndIfToEarlyReturnRector::class);
    $services->set(StrContainsRector::class);
    $services->set(StrEndsWithRector::class);
    $services->set(StrStartsWithRector::class);

    require_once __DIR__.'/.tools/rector/UnderscoreCamelCaseConflictingNameGuard.php';
    require_once __DIR__.'/.tools/rector/UnderscoreCamelCaseExpectedNameResolver.php';
    require_once __DIR__.'/.tools/rector/UnderscoreCamelCasePropertyRenamer.php';
    require_once __DIR__.'/.tools/rector/UnderscoreToCamelCasePropertyNameRector.php';
    require_once __DIR__.'/.tools/rector/UnderscoreToCamelCaseVariableNameRector.php';

    $services->set(UnderscoreCamelCaseConflictingNameGuard::class)->autowire();
    $services->set(UnderscoreCamelCaseExpectedNameResolver::class)->autowire();
    $services->set(UnderscoreCamelCasePropertyRenamer::class)->autowire();

    $services->set(UnderscoreToCamelCasePropertyNameRector::class);
    $services->set(UnderscoreToCamelCaseVariableNameRector::class);
};
