<?php

declare(strict_types=1);

use Rector\Arguments\Rector\MethodCall\RemoveMethodCallParamRector;
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
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersion;
use Redaxo\Rector\Rule\UnderscoreToCamelCasePropertyNameRector;
use Redaxo\Rector\Rule\UnderscoreToCamelCaseVariableNameRector;
use Redaxo\Rector\Util\UnderscoreCamelCaseConflictingNameGuard;
use Redaxo\Rector\Util\UnderscoreCamelCaseExpectedNameResolver;
use Redaxo\Rector\Util\UnderscoreCamelCasePropertyRenamer;

require_once __DIR__ . '/.tools/rector/autoload.php';

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->bootstrapFiles([
        __DIR__ . '/.tools/constants.php',
    ]);

    // this list will grow over time.
    // to make sure we can review every transformation and not introduce unseen bugs
    $rectorConfig->paths([
        // restrict to core and core addons, ignore other locally installed addons
        'redaxo/src/core/',
        'redaxo/src/addons/debug/',
        'redaxo/src/addons/install/',
        'redaxo/src/addons/project/',
    ]);

    $rectorConfig->skip([
        FirstClassCallableRector::class => ['redaxo/src/core/boot.php'],
    ]);

    $rectorConfig->parallel();

    $rectorConfig->phpVersion(PhpVersion::PHP_83);

    // we will grow this rector list step by step.
    // after some basic rectors have been enabled we can finally enable whole-sets (when diffs get stable and reviewable)
    $rectorConfig->rule(ChangeSwitchToMatchRector::class);
    $rectorConfig->rule(CleanupUnneededNullsafeOperatorRector::class);
    $rectorConfig->rule(CombinedAssignRector::class);
    $rectorConfig->rule(FirstClassCallableRector::class);
    $rectorConfig->rule(IfIssetToCoalescingRector::class);
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);
    $rectorConfig->rule(RemoveUnusedVariableInCatchRector::class);
    $rectorConfig->rule(SimplifyBoolIdenticalTrueRector::class);
    $rectorConfig->rule(SimplifyConditionsRector::class);
    $rectorConfig->rule(SimplifyDeMorganBinaryRector::class);
    $rectorConfig->rule(SimplifyForeachToCoalescingRector::class);
    $rectorConfig->rule(SimplifyIfReturnBoolRector::class);
    $rectorConfig->rule(SimplifyRegexPatternRector::class);
    $rectorConfig->rule(SingleInArrayToCompareRector::class);
    $rectorConfig->rule(StrContainsRector::class);
    $rectorConfig->rule(StrEndsWithRector::class);
    $rectorConfig->rule(StrStartsWithRector::class);
    $rectorConfig->rule(TernaryToNullCoalescingRector::class);

    // Util services for own rules;
    $rectorConfig->singleton(UnderscoreCamelCaseConflictingNameGuard::class);
    $rectorConfig->singleton(UnderscoreCamelCaseExpectedNameResolver::class);
    $rectorConfig->singleton(UnderscoreCamelCasePropertyRenamer::class);

    // Own rules
    $rectorConfig->rule(UnderscoreToCamelCasePropertyNameRector::class);
    $rectorConfig->rule(UnderscoreToCamelCaseVariableNameRector::class);
    $rectorConfig->rule(UnnecessaryTernaryExpressionRector::class);

    // Upgrade REDAXO 5 to 6
    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        new RemoveFuncCallArg('rex_getUrl', 3),
    ]);
    $rectorConfig->ruleWithConfiguration(ArgumentRemoverRector::class, [
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
    ]);
};
