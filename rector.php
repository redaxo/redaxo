<?php

declare(strict_types=1);

use Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
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
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Rector\Transform\Rector\ConstFetch\ConstFetchToClassConstFetchRector;
use Rector\Transform\ValueObject\ConstFetchToClassConstFetch;
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
        'src/',
        'tests/',
        'redaxo/src/core/',
        'redaxo/src/addons/debug/',
        'redaxo/src/addons/install/',
        'redaxo/src/addons/project/',
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
    ->withConfiguredRule(RenameClassRector::class, [
        rex::class => Redaxo\Core\Core::class,
        rex_package_interface::class => rex_addon_interface::class,
        rex_null_package::class => rex_null_addon::class,
        rex_package::class => rex_addon::class,
        rex_package_manager::class => rex_addon_manager::class,
        rex_sql::class => Redaxo\Core\Database\Sql::class,
    ])
    ->withConfiguredRule(RenameMethodRector::class, [
        new MethodCallRename(rex_addon::class, 'getRegisteredPackages', 'getRegisteredAddons'),
        new MethodCallRename(rex_addon::class, 'getInstalledPackages', 'getInstalledAddons'),
        new MethodCallRename(rex_addon::class, 'getAvailablePackages', 'getAvailableAddons'),
        new MethodCallRename(rex_addon::class, 'getSetupPackages', 'getSetupAddons'),
        new MethodCallRename(rex_addon::class, 'getSystemPackages', 'getSystemAddons'),
        new MethodCallRename(rex_password_policy::class, 'getRule', 'getDescription'),
        new MethodCallRename(rex_article_content_base::class, 'getClang', 'getClangId'),
        new MethodCallRename(rex_article_slice::class, 'getClang', 'getClangId'),
        new MethodCallRename(rex_structure_element::class, 'getClang', 'getClangId'),
        new MethodCallRename(rex_managed_media::class, 'getImageWidth', 'getWidth'),
        new MethodCallRename(rex_managed_media::class, 'getImageHeight', 'getHeight'),
        new MethodCallRename(rex_mailer::class, 'setLog', 'setArchive'),
    ])
    ->withConfiguredRule(RenameStaticMethodRector::class, [
        new RenameStaticMethod(Redaxo\Core\Core::class, 'getVersionHash', rex_version::class, 'gitHash'),
        new RenameStaticMethod(rex_string::class, 'versionSplit', rex_version::class, 'split'),
        new RenameStaticMethod(rex_string::class, 'versionCompare', rex_version::class, 'compare'),
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
    ->withConfiguredRule(ReplaceArgumentDefaultValueRector::class, [
        new ReplaceArgumentDefaultValue(rex_extension::class, 'register', 0, 'STRUCTURE_CONTENT_SLICE_ADDED', 'SLICE_ADDED'),
        new ReplaceArgumentDefaultValue(rex_extension::class, 'register', 0, 'STRUCTURE_CONTENT_SLICE_UPDATED', 'SLICE_UPDATED'),
        new ReplaceArgumentDefaultValue(rex_extension::class, 'register', 0, 'STRUCTURE_CONTENT_SLICE_DELETED', 'SLICE_DELETED'),
    ])
    ->withConfiguredRule(ConstFetchToClassConstFetchRector::class, [
        new ConstFetchToClassConstFetch('REX_FORM_ERROR_VIOLATE_UNIQUE_KEY', rex_form::class, 'ERROR_VIOLATE_UNIQUE_KEY'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_TEXT', rex_metainfo_table_manager::class, 'FIELD_TEXT'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_TEXTAREA', rex_metainfo_table_manager::class, 'FIELD_TEXTAREA'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_SELECT', rex_metainfo_table_manager::class, 'FIELD_SELECT'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_RADIO', rex_metainfo_table_manager::class, 'FIELD_RADIO'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_CHECKBOX', rex_metainfo_table_manager::class, 'FIELD_CHECKBOX'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_REX_MEDIA_WIDGET', rex_metainfo_table_manager::class, 'FIELD_REX_MEDIA_WIDGET'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_REX_MEDIALIST_WIDGET', rex_metainfo_table_manager::class, 'FIELD_REX_MEDIALIST_WIDGET'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_REX_LINK_WIDGET', rex_metainfo_table_manager::class, 'FIELD_REX_LINK_WIDGET'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_REX_LINKLIST_WIDGET', rex_metainfo_table_manager::class, 'FIELD_REX_LINKLIST_WIDGET'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_DATE', rex_metainfo_table_manager::class, 'FIELD_DATE'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_DATETIME', rex_metainfo_table_manager::class, 'FIELD_DATETIME'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_LEGEND', rex_metainfo_table_manager::class, 'FIELD_LEGEND'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_TIME', rex_metainfo_table_manager::class, 'FIELD_TIME'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_COUNT', rex_metainfo_table_manager::class, 'FIELD_COUNT'),
    ])
;
