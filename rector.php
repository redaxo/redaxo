<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector;
use Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector;
use Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector;
use Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\NullsafeMethodCall\CleanupUnneededNullsafeOperatorRector;
use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Rector\Config\RectorConfig;
use Rector\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector;
use Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector;
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;
use Rector\Php80\Rector\Identical\StrEndsWithRector;
use Rector\Php80\Rector\Identical\StrStartsWithRector;
use Rector\Php80\Rector\NotIdentical\StrContainsRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\ValueObject\PhpVersion;
use Redaxo\Rector\Rule\UnderscoreToCamelCasePropertyNameRector;
use Redaxo\Rector\Rule\UnderscoreToCamelCaseVariableNameRector;
use Redaxo\Rector\Util\UnderscoreCamelCaseConflictingNameGuard;
use Redaxo\Rector\Util\UnderscoreCamelCaseExpectedNameResolver;
use Redaxo\Rector\Util\UnderscoreCamelCasePropertyRenamer;

require_once __DIR__ . '/.tools/rector/autoload.php';

return RectorConfig::configure()
    ->withBootstrapFiles([
        __DIR__ . '/.tools/constants.php',
    ])
    ->withPaths([
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
    ])
    ->withSkip([
        'redaxo/src/core/vendor',
        'redaxo/src/addons/backup/vendor',
        'redaxo/src/addons/be_style/vendor',
        'redaxo/src/addons/debug/vendor',
        'redaxo/src/addons/phpmailer/vendor',

        FirstClassCallableRector::class => ['redaxo/src/core/boot.php'],
    ])
    ->withParallel()
    ->withPhpVersion(PhpVersion::PHP_81)
    ->registerService(UnderscoreCamelCaseConflictingNameGuard::class)
    ->registerService(UnderscoreCamelCaseExpectedNameResolver::class)
    ->registerService(UnderscoreCamelCasePropertyRenamer::class)
    ->withRules([
        ChangeSwitchToMatchRector::class,
        CleanupUnneededNullsafeOperatorRector::class,
        CombinedAssignRector::class,
        FirstClassCallableRector::class,
        IfIssetToCoalescingRector::class,
        InlineConstructorDefaultToPropertyRector::class,
        RemoveUnusedVariableInCatchRector::class,
        SimplifyBoolIdenticalTrueRector::class,
        SimplifyConditionsRector::class,
        SimplifyDeMorganBinaryRector::class,
        SimplifyForeachToCoalescingRector::class,
        SimplifyIfReturnBoolRector::class,
        SimplifyRegexPatternRector::class,
        SingleInArrayToCompareRector::class,
        StrContainsRector::class,
        StrEndsWithRector::class,
        StrStartsWithRector::class,
        TernaryToNullCoalescingRector::class,
        UnnecessaryTernaryExpressionRector::class,

        // Own rules
        UnderscoreToCamelCasePropertyNameRector::class,
        UnderscoreToCamelCaseVariableNameRector::class,
    ])
;
