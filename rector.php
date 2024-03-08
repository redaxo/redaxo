<?php

declare(strict_types=1);

use PhpParser\Node\Expr;
use PhpParser\Node\Name;
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
use Rector\Transform\Rector\FuncCall\FuncCallToStaticCallRector;
use Rector\Transform\Rector\New_\NewToStaticCallRector;
use Rector\Transform\ValueObject\ConstFetchToClassConstFetch;
use Rector\Transform\ValueObject\FuncCallToStaticCall;
use Rector\Transform\ValueObject\NewToStaticCall;
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
        rex_dir::class => Redaxo\Core\Filesystem\Dir::class,
        rex_file::class => Redaxo\Core\Filesystem\File::class,
        rex_finder::class => Redaxo\Core\Filesystem\Finder::class,
        rex_form_base::class => Redaxo\Core\Form\AbstractForm::class,
        rex_form::class => Redaxo\Core\Form\Form::class,
        rex_config_form::class => Redaxo\Core\Form\ConfigForm::class,
        rex_form_element::class => Redaxo\Core\Form\Field\BaseField::class,
        rex_form_options_element::class => Redaxo\Core\Form\Field\AbstractOptionField::class,
        rex_form_checkbox_element::class => Redaxo\Core\Form\Field\CheckboxField::class,
        rex_form_radio_element::class => Redaxo\Core\Form\Field\RadioField::class,
        rex_form_container_element::class => Redaxo\Core\Form\Field\ContainerField::class,
        rex_form_control_element::class => Redaxo\Core\Form\Field\ControlField::class,
        rex_form_select_element::class => Redaxo\Core\Form\Field\SelectField::class,
        rex_form_prio_element::class => Redaxo\Core\Form\Field\PriorityField::class,
        rex_form_perm_select_element::class => Redaxo\Core\Form\Field\PermissionSelectField::class,
        rex_i18n::class => Redaxo\Core\Translation\I18n::class,
        rex_path::class => Redaxo\Core\Filesystem\Path::class,
        rex_path_default_provider::class => Redaxo\Core\Filesystem\DefaultPathProvider::class,
        rex_sql::class => Redaxo\Core\Database\Sql::class,
        rex_sql_column::class => Redaxo\Core\Database\Column::class,
        rex_sql_foreign_key::class => Redaxo\Core\Database\ForeignKey::class,
        rex_sql_index::class => Redaxo\Core\Database\Index::class,
        rex_sql_schema_dumper::class => Redaxo\Core\Database\SchemaDumper::class,
        rex_sql_table::class => Redaxo\Core\Database\Table::class,
        rex_sql_util::class => Redaxo\Core\Database\Util::class,
        rex_validator::class => Redaxo\Core\Validator\Validator::class,
        rex_validation_rule::class => Redaxo\Core\Validator\ValidationRule::class,
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
    ->withConfiguredRule(NewToStaticCallRector::class, [
        new NewToStaticCall(rex_backend_password_policy::class, rex_backend_password_policy::class, 'factory'),
    ])
    ->withConfiguredRule(FuncCallToStaticCallRector::class, [
        new FuncCallToStaticCall('rex_mediapool_filename', rex_mediapool::class, 'filename'),
        new FuncCallToStaticCall('rex_mediapool_mediaIsInUse', rex_mediapool::class, 'mediaIsInUse'),
        new FuncCallToStaticCall('rex_mediapool_isAllowedMediaType', rex_mediapool::class, 'isAllowedExtension'),
        new FuncCallToStaticCall('rex_mediapool_isAllowedMimeType', rex_mediapool::class, 'isAllowedMimeType'),
        new FuncCallToStaticCall('rex_mediapool_getMediaTypeWhitelist', rex_mediapool::class, 'getAllowedExtensions'),
        new FuncCallToStaticCall('rex_mediapool_getMediaTypeBlacklist', rex_mediapool::class, 'getBlockedExtensions'),

        // additional adjustments necessary afterward, see https://github.com/redaxo/redaxo/pull/5918/files
        new FuncCallToStaticCall('rex_mediapool_saveMedia', rex_mediapool::class, 'addMedia'), // different params
        new FuncCallToStaticCall('rex_mediapool_updateMedia', rex_mediapool::class, 'updateMedia'), // different params
        new FuncCallToStaticCall('rex_mediapool_syncFile', rex_mediapool::class, 'addMedia'), // different params
        new FuncCallToStaticCall('rex_mediapool_deleteMedia', rex_mediapool::class, 'deleteMedia'), // different return value
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
        new ArgumentRemover(Redaxo\Core\Form\AbstractForm::class, 'getUrl', 1, null),
        new ArgumentRemover(rex_list::class, 'getUrl', 1, null),
        new ArgumentRemover(rex_list::class, 'getParsedUrl', 1, null),
        new ArgumentRemover(rex_structure_element::class, 'getUrl', 1, null),
        new ArgumentRemover(rex_media_manager::class, 'getUrl', 3, null),

        new ArgumentRemover(rex_markdown::class, 'parse', 1, [true]),
        new ArgumentRemover(rex_markdown::class, 'parseWithToc', 3, [true]),
    ])
    ->withConfiguredRule(ReplaceArgumentDefaultValueRector::class, [
        new ReplaceArgumentDefaultValue(rex_extension::class, 'register', 0, 'STRUCTURE_CONTENT_SLICE_ADDED', 'SLICE_ADDED'),
        new ReplaceArgumentDefaultValue(rex_extension::class, 'register', 0, 'STRUCTURE_CONTENT_SLICE_UPDATED', 'SLICE_UPDATED'),
        new ReplaceArgumentDefaultValue(rex_extension::class, 'register', 0, 'STRUCTURE_CONTENT_SLICE_DELETED', 'SLICE_DELETED'),

        new ReplaceArgumentDefaultValue(rex_markdown::class, 'parse', 1, false, $options = [
            new Expr\ArrayItem(new Expr\ConstFetch(new Name('false')), new Expr\ClassConstFetch(new Name(rex_markdown::class), 'SOFT_LINE_BREAKS')),
        ]),
        new ReplaceArgumentDefaultValue(rex_markdown::class, 'parseWithToc', 3, false, $options),
    ])
    ->withConfiguredRule(ConstFetchToClassConstFetchRector::class, [
        new ConstFetchToClassConstFetch('REX_FORM_ERROR_VIOLATE_UNIQUE_KEY', Redaxo\Core\Form\Form::class, 'ERROR_VIOLATE_UNIQUE_KEY'),

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
