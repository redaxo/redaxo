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
use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
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
        'redaxo/src/addons/debug/',
        'redaxo/src/addons/install/',
        'redaxo/src/addons/project/',
    ])
    ->withSkip([
        FirstClassCallableRector::class => ['redaxo/src/core/boot.php'],
    ])
    ->withParallel()
    ->withPhpVersion(PhpVersion::PHP_83)
    ->withImportNames()
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

    // Upgrade REDAXO 5 to 6
    ->withConfiguredRule(RenameMethodRector::class, [
        new MethodCallRename(rex_managed_media::class, 'getImageWidth', 'getWidth'),
        new MethodCallRename(rex_managed_media::class, 'getImageHeight', 'getHeight'),
    ])
    ->withConfiguredRule(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('rex_getUrl', 3),
    ])
    ->withConfiguredRule(ArgumentRemoverRector::class, [
        new ArgumentRemover(rex_string::class, 'buildQuery', 1, null),
        new ArgumentRemover(rex_url_provider_interface::class, 'getUrl', 1, null),
        new ArgumentRemover(rex_url::class, 'frontendController', 1, null),
        new ArgumentRemover(rex_url::class, 'backendController', 1, null),
        new ArgumentRemover(rex_url::class, 'backendPage', 2, null),
        new ArgumentRemover(rex_url::class, 'currentBackendPage', 1, null),
        new ArgumentRemover(rex_form_base::class, 'getUrl', 1, null),
        new ArgumentRemover(rex_list::class, 'getUrl', 1, null),
        new ArgumentRemover(rex_list::class, 'getParsedUrl', 1, null),
        new ArgumentRemover(rex_structure_element::class, 'getUrl', 1, null),
        new ArgumentRemover(rex_media_manager::class, 'getUrl', 3, null),
    ])
;
