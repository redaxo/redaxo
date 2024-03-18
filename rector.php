<?php

declare(strict_types=1);

use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Rector\CodeQuality\Rector as CodeQuality;
use Rector\CodingStyle\Rector as CodingStyle;
use Rector\Config\RectorConfig;
use Rector\Php55\Rector as Php55;
use Rector\Php70\Rector as Php70;
use Rector\Php74\Rector as Php74;
use Rector\Php80\Rector as Php80;
use Rector\Php81\Rector as Php81;
use Rector\Php82\Rector as Php82;
use Rector\Privatization\Rector as Privatization;
use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Rector\Transform\Rector\ConstFetch\ConstFetchToClassConstFetchRector;
use Rector\Transform\Rector\FuncCall\FuncCallToStaticCallRector;
use Rector\Transform\Rector\New_\NewToStaticCallRector;
use Rector\Transform\ValueObject\ConstFetchToClassConstFetch;
use Rector\Transform\ValueObject\FuncCallToStaticCall;
use Rector\Transform\ValueObject\NewToStaticCall;
use Rector\TypeDeclaration\Rector as TypeDeclaration;
use Rector\ValueObject\PhpVersion;
use Redaxo\Core\Core;
use Redaxo\Core\Cronjob;
use Redaxo\Core\Database;
use Redaxo\Core\Filesystem;
use Redaxo\Core\Form;
use Redaxo\Core\Log;
use Redaxo\Core\Translation;
use Redaxo\Core\Util;
use Redaxo\Core\Validator;
use Redaxo\Rector\Rule as RedaxoRule;

return RectorConfig::configure()
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
    ->withPreparedSets(typeDeclarations: false, privatization: true)
    // ->withPhpSets()
    ->withImportNames()
    ->withRules([
        CodeQuality\Assign\CombinedAssignRector::class,
        CodeQuality\BooleanNot\SimplifyDeMorganBinaryRector::class,
        CodeQuality\Class_\InlineConstructorDefaultToPropertyRector::class,
        CodeQuality\Class_\StaticToSelfStaticMethodCallOnFinalClassRector::class,
        CodeQuality\ClassConstFetch\ConvertStaticPrivateConstantToSelfRector::class,
        CodeQuality\Foreach_\SimplifyForeachToCoalescingRector::class,
        CodeQuality\FuncCall\SimplifyRegexPatternRector::class,
        CodeQuality\FuncCall\SingleInArrayToCompareRector::class,
        CodeQuality\Identical\SimplifyBoolIdenticalTrueRector::class,
        CodeQuality\Identical\SimplifyConditionsRector::class,
        CodeQuality\If_\SimplifyIfReturnBoolRector::class,
        CodeQuality\NullsafeMethodCall\CleanupUnneededNullsafeOperatorRector::class,
        CodeQuality\Ternary\UnnecessaryTernaryExpressionRector::class,
        CodingStyle\ClassConst\RemoveFinalFromConstRector::class,
        // CodingStyle\String_\SymplifyQuoteEscapeRector::class,
        Php55\ClassConstFetch\StaticToSelfOnFinalClassRector::class,
        Php70\StmtsAwareInterface\IfIssetToCoalescingRector::class,
        Php70\Ternary\TernaryToNullCoalescingRector::class,
        Php80\Catch_\RemoveUnusedVariableInCatchRector::class,
        Php80\Identical\StrEndsWithRector::class,
        Php80\Identical\StrStartsWithRector::class,
        Php80\NotIdentical\StrContainsRector::class,
        Php80\Switch_\ChangeSwitchToMatchRector::class,
        Php81\Array_\FirstClassCallableRector::class,
        // Php81\Property\ReadOnlyPropertyRector::class,
        // Php82\Class_\ReadOnlyClassRector::class,
        Privatization\Class_\FinalizeTestCaseClassRector::class,

        // Own rules
        RedaxoRule\UnderscoreToCamelCasePropertyNameRector::class,
        RedaxoRule\UnderscoreToCamelCaseVariableNameRector::class,
    ])
    ->withSkip([
        Php74\Closure\ClosureToArrowFunctionRector::class,
        Php81\FuncCall\NullToStrictStringFuncCallArgRector::class,
        TypeDeclaration\ArrowFunction\AddArrowFunctionReturnTypeRector::class,
        TypeDeclaration\Closure\AddClosureVoidReturnTypeWhereNoReturnRector::class,
    ])

    // Upgrade REDAXO 5 to 6
    ->withConfiguredRule(RenameClassRector::class, [
        'rex' => Core::class,
        'rex_package_interface' => rex_addon_interface::class,
        'rex_null_package' => rex_null_addon::class,
        'rex_package' => rex_addon::class,
        'rex_package_manager' => rex_addon_manager::class,
        'rex_cronjob_form' => Cronjob\Form\CronjobForm::class,
        'rex_cronjob_form_interval_element' => Cronjob\Form\IntervalField::class,
        'rex_dir' => Filesystem\Dir::class,
        'rex_editor' => Util\Editor::class,
        'rex_file' => Filesystem\File::class,
        'rex_finder' => Filesystem\Finder::class,
        'rex_form_base' => Form\AbstractForm::class,
        'rex_form' => Form\Form::class,
        'rex_config_form' => Form\ConfigForm::class,
        'rex_form_element' => Form\Field\BaseField::class,
        'rex_form_options_element' => Form\Field\AbstractOptionField::class,
        'rex_form_checkbox_element' => Form\Field\CheckboxField::class,
        'rex_form_radio_element' => Form\Field\RadioField::class,
        'rex_form_container_element' => Form\Field\ContainerField::class,
        'rex_form_control_element' => Form\Field\ControlField::class,
        'rex_form_select_element' => Form\Field\SelectField::class,
        'rex_form_prio_element' => Form\Field\PriorityField::class,
        'rex_form_perm_select_element' => Form\Field\PermissionSelectField::class,
        'rex_form_raw_element' => Form\Field\RawField::class,
        'rex_form_widget_linkmap_element' => Form\Field\ArticleField::class,
        'rex_form_widget_media_element' => Form\Field\MediaField::class,
        'rex_formatter' => Util\Formatter::class,
        'rex_i18n' => Translation\I18n::class,
        'rex_log_entry' => Log\LogEntry::class,
        'rex_log_file' => Log\LogFile::class,
        'rex_logger' => Log\Logger::class,
        'rex_markdown' => Util\Markdown::class,
        'rex_pager' => Util\Pager::class,
        'rex_parsedown' => Util\Parsedown::class,
        'rex_path' => Filesystem\Path::class,
        'rex_path_default_provider' => Filesystem\DefaultPathProvider::class,
        'rex_sortable_iterator' => Util\SortableIterator::class,
        'rex_sql' => Database\Sql::class,
        'rex_sql_column' => Database\Column::class,
        'rex_sql_foreign_key' => Database\ForeignKey::class,
        'rex_sql_index' => Database\Index::class,
        'rex_sql_schema_dumper' => Database\SchemaDumper::class,
        'rex_sql_table' => Database\Table::class,
        'rex_sql_util' => Database\Util::class,
        'rex_stream' => Util\Stream::class,
        'rex_string' => Util\Str::class,
        'rex_timer' => Util\Timer::class,
        'rex_type' => Util\Type::class,
        'rex_url' => Filesystem\Url::class,
        'rex_validator' => Validator\Validator::class,
        'rex_validation_rule' => Validator\ValidationRule::class,
        'rex_version' => Util\Version::class,
    ])
    ->withConfiguredRule(ArgumentAdderRector::class, [
        new ArgumentAdder(Form\AbstractForm::class, 'addLinklistField', 1, 'value', null),
        new ArgumentAdder(Form\AbstractForm::class, 'addLinklistField', 2, 'arguments', ['multiple' => true]),
        new ArgumentAdder(Form\AbstractForm::class, 'addMedialistField', 1, 'value', null),
        new ArgumentAdder(Form\AbstractForm::class, 'addMedialistField', 2, 'arguments', ['multiple' => true]),
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

        new MethodCallRename(Form\AbstractForm::class, 'addLinklistField', 'addArticleField'),
        new MethodCallRename(Form\AbstractForm::class, 'addLinkmapField', 'addArticleField'),
        new MethodCallRename(Form\AbstractForm::class, 'addMedialistField', 'addMediaField'),
    ])
    ->withConfiguredRule(RenameStaticMethodRector::class, [
        new RenameStaticMethod(Core::class, 'getVersionHash', Util\Version::class, 'gitHash'),
        new RenameStaticMethod(Util\Str::class, 'versionSplit', Util\Version::class, 'split'),
        new RenameStaticMethod(Util\Str::class, 'versionCompare', Util\Version::class, 'compare'),
    ])
    ->withConfiguredRule(NewToStaticCallRector::class, [
        new NewToStaticCall(rex_backend_password_policy::class, rex_backend_password_policy::class, 'factory'),
        new NewToStaticCall(Log\LogFile::class, Log\LogFile::class, 'factory'),
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
        new ArgumentRemover(Util\Str::class, 'buildQuery', 1, null),
        new ArgumentRemover(rex_url_provider_interface::class, 'getUrl', 1, null),
        new ArgumentRemover(Filesystem\Url::class, 'frontendController', 1, null),
        new ArgumentRemover(Filesystem\Url::class, 'backendController', 1, null),
        new ArgumentRemover(Filesystem\Url::class, 'backendPage', 2, null),
        new ArgumentRemover(Filesystem\Url::class, 'currentBackendPage', 1, null),
        new ArgumentRemover(Form\AbstractForm::class, 'getUrl', 1, null),
        new ArgumentRemover(rex_list::class, 'getUrl', 1, null),
        new ArgumentRemover(rex_list::class, 'getParsedUrl', 1, null),
        new ArgumentRemover(rex_structure_element::class, 'getUrl', 1, null),
        new ArgumentRemover(rex_media_manager::class, 'getUrl', 3, null),

        new ArgumentRemover(Util\Markdown::class, 'parse', 1, [true]),
        new ArgumentRemover(Util\Markdown::class, 'parseWithToc', 3, [true]),
    ])
    ->withConfiguredRule(ReplaceArgumentDefaultValueRector::class, [
        new ReplaceArgumentDefaultValue(rex_extension::class, 'register', 0, 'STRUCTURE_CONTENT_SLICE_ADDED', 'SLICE_ADDED'),
        new ReplaceArgumentDefaultValue(rex_extension::class, 'register', 0, 'STRUCTURE_CONTENT_SLICE_UPDATED', 'SLICE_UPDATED'),
        new ReplaceArgumentDefaultValue(rex_extension::class, 'register', 0, 'STRUCTURE_CONTENT_SLICE_DELETED', 'SLICE_DELETED'),

        new ReplaceArgumentDefaultValue(Util\Markdown::class, 'parse', 1, false, $options = [
            new Expr\ArrayItem(new Expr\ConstFetch(new Name('false')), new Expr\ClassConstFetch(new Name(Util\Markdown::class), 'SOFT_LINE_BREAKS')),
        ]),
        new ReplaceArgumentDefaultValue(Util\Markdown::class, 'parseWithToc', 3, false, $options),
    ])
    ->withConfiguredRule(ConstFetchToClassConstFetchRector::class, [
        new ConstFetchToClassConstFetch('REX_FORM_ERROR_VIOLATE_UNIQUE_KEY', Form\Form::class, 'ERROR_VIOLATE_UNIQUE_KEY'),

        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_TEXT', rex_metainfo_table_manager::class, 'FIELD_TEXT'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_TEXTAREA', rex_metainfo_table_manager::class, 'FIELD_TEXTAREA'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_SELECT', rex_metainfo_table_manager::class, 'FIELD_SELECT'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_RADIO', rex_metainfo_table_manager::class, 'FIELD_RADIO'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_CHECKBOX', rex_metainfo_table_manager::class, 'FIELD_CHECKBOX'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_REX_MEDIA_WIDGET', rex_metainfo_table_manager::class, 'FIELD_REX_MEDIA_WIDGET'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_REX_MEDIALIST_WIDGET', rex_metainfo_table_manager::class, 'FIELD_REX_MEDIA_WIDGET'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_REX_LINK_WIDGET', rex_metainfo_table_manager::class, 'FIELD_REX_LINK_WIDGET'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_REX_LINKLIST_WIDGET', rex_metainfo_table_manager::class, 'FIELD_REX_LINK_WIDGET'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_DATE', rex_metainfo_table_manager::class, 'FIELD_DATE'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_DATETIME', rex_metainfo_table_manager::class, 'FIELD_DATETIME'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_LEGEND', rex_metainfo_table_manager::class, 'FIELD_LEGEND'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_TIME', rex_metainfo_table_manager::class, 'FIELD_TIME'),
        new ConstFetchToClassConstFetch('REX_METAINFO_FIELD_COUNT', rex_metainfo_table_manager::class, 'FIELD_COUNT'),
    ])
    ->withConfiguredRule(RenameClassConstFetchRector::class, [
        new RenameClassConstFetch(rex_metainfo_table_manager::class, 'FIELD_REX_MEDIALIST_WIDGET', 'FIELD_REX_MEDIA_WIDGET'),
        new RenameClassConstFetch(rex_metainfo_table_manager::class, 'FIELD_REX_LINKLIST_WIDGET', 'FIELD_REX_LINK_WIDGET'),
        new RenameClassConstFetch(rex_metainfo_default_type::class, 'REX_MEDIALIST_WIDGET', 'REX_MEDIA_WIDGET'),
        new RenameClassConstFetch(rex_metainfo_default_type::class, 'REX_LINKLIST_WIDGET', 'REX_LINK_WIDGET'),
    ])
;
