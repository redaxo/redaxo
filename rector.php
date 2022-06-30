<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector;
use Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector;
use Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector;
use Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector;
use Rector\Php80\Rector\Identical\StrEndsWithRector;
use Rector\Php80\Rector\Identical\StrStartsWithRector;
use Rector\Php80\Rector\NotIdentical\StrContainsRector;
use Redaxo\Rector\Rule\UnderscoreToCamelCasePropertyNameRector;
use Redaxo\Rector\Rule\UnderscoreToCamelCaseVariableNameRector;
use Redaxo\Rector\Util\UnderscoreCamelCaseConflictingNameGuard;
use Redaxo\Rector\Util\UnderscoreCamelCaseExpectedNameResolver;
use Redaxo\Rector\Util\UnderscoreCamelCasePropertyRenamer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

require_once __DIR__.'/.tools/rector/autoload.php';

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->bootstrapFiles([
        __DIR__.'/.tools/constants.php',
    ]);

    // this list will grow over time.
    // to make sure we can review every transformation and not introduce unseen bugs
    $rectorConfig->paths([
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

    $rectorConfig->skip([
        'redaxo/src/core/vendor',
        'redaxo/src/addons/backup/vendor',
        'redaxo/src/addons/be_style/vendor',
        'redaxo/src/addons/debug/vendor',
        'redaxo/src/addons/phpmailer/vendor',
    ]);

    $rectorConfig->parallel();

    $rectorConfig->phpVersion(PhpVersion::PHP_80);

    // get services (needed for register a single rule)
    $services = $rectorConfig->services();

    // we will grow this rector list step by step.
    // after some basic rectors have been enabled we can finally enable whole-sets (when diffs get stable and reviewable)
    // $services->set(Rector\SOLID\Rector\If_\ChangeAndIfToEarlyReturnRector::class);
    $services->set(CombinedAssignRector::class);
    $services->set(SimplifyBoolIdenticalTrueRector::class);
    $services->set(SimplifyConditionsRector::class);
    $services->set(SimplifyDeMorganBinaryRector::class);
    $services->set(SimplifyForeachToCoalescingRector::class);
    $services->set(SimplifyIfReturnBoolRector::class);
    $services->set(StrContainsRector::class);
    $services->set(StrEndsWithRector::class);
    $services->set(StrStartsWithRector::class);
    $services->set(TernaryToNullCoalescingRector::class);

    // Util services for own rules
    $services->set(UnderscoreCamelCaseConflictingNameGuard::class)->autowire();
    $services->set(UnderscoreCamelCaseExpectedNameResolver::class)->autowire();
    $services->set(UnderscoreCamelCasePropertyRenamer::class)->autowire();

    // Own rules
    $services->set(UnderscoreToCamelCasePropertyNameRector::class);
    $services->set(UnderscoreToCamelCaseVariableNameRector::class);
    $services->set(UnnecessaryTernaryExpressionRector::class);
};
