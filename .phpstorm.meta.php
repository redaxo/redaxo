<?php

namespace PHPSTORM_META;

// https://blog.jetbrains.com/phpstorm/2019/02/new-phpstorm-meta-php-features/

override(
    \Redaxo\Core\Core::getProperty(0),
    map([
        'console' => \Redaxo\Core\Console\Application::class,
        'login' => \Redaxo\Core\Security\BackendLogin::class,
        'timer' => \Redaxo\Core\Util\Timer::class,
        'user' => \Redaxo\Core\Security\User::class,
    ])
);

expectedReturnValues(\Redaxo\Core\Core::getEnvironment(), 'frontend', 'backend', 'console');

expectedArguments(\rex_extension::register(), 2, \rex_extension::EARLY, \rex_extension::NORMAL, \rex_extension::LATE);

expectedArguments(\Redaxo\Core\Filesystem\Finder::sort(), 0, \Redaxo\Core\Util\SortableIterator::KEYS, \Redaxo\Core\Util\SortableIterator::VALUES);
expectedArguments(\Redaxo\Core\Util\SortableIterator::__construct(), 1, \Redaxo\Core\Util\SortableIterator::KEYS, \Redaxo\Core\Util\SortableIterator::VALUES);

expectedArguments(\Redaxo\Core\Form\Form::factory(), 3, 'get', 'post');

registerArgumentsSet('form_field_type', 'text', 'textarea', 'checkbox', 'radio', 'select', 'media', 'article', 'hidden', 'readonly', 'readonlytext', 'control');
expectedArguments(\Redaxo\Core\Form\Form::createInput(), 0, argumentsSet('form_field_type'));
expectedArguments(\Redaxo\Core\Form\Field\ContainerField::addField(), 0, argumentsSet('form_field_type'));
expectedArguments(\Redaxo\Core\Form\Field\ContainerField::addGroupedField(), 1, argumentsSet('form_field_type'));

registerArgumentsSet('formatter_type', 'date', 'strftime', 'intlDateTime', 'intlDate', 'intlTime', 'number', 'bytes', 'sprintf', 'nl2br', 'truncate', 'widont', 'version', 'url', 'email', 'custom');
expectedArguments(\Redaxo\Core\Util\Formatter::format(), 1, argumentsSet('formatter_type'));
expectedArguments(\Redaxo\Core\View\DataList::setColumnFormat(), 1, argumentsSet('formatter_type'));

registerArgumentsSet('intl_format', \IntlDateFormatter::FULL, \IntlDateFormatter::LONG, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT);
expectedArguments(\Redaxo\Core\Util\Formatter::intlDateTime(), 1, argumentsSet('intl_format'));
expectedArguments(\Redaxo\Core\Util\Formatter::intDate(), 1, argumentsSet('intl_format'));
expectedArguments(\Redaxo\Core\Util\Formatter::intlTime(), 1, argumentsSet('intl_format'));

registerArgumentsSet('locale', 'de_de', 'en_gb', 'es_es', 'it_it', 'nl_nl', 'pt_br', 'sv_se');
expectedArguments(\Redaxo\Core\Translation\I18n::setLocale(), 0, argumentsSet('locale'));
expectedReturnValues(\Redaxo\Core\Translation\I18n::getLocale(), argumentsSet('locale'));
expectedReturnValues(\Redaxo\Core\Translation\I18n::getLanguage(), 'de', 'en', 'es', 'it', 'nl', 'pt', 'sv');
expectedArguments(\Redaxo\Core\Translation\I18n::msgInLocale(), 1, argumentsSet('locale'));
expectedArguments(\Redaxo\Core\Translation\I18n::rawMsgInLocale(), 1, argumentsSet('locale'));

expectedArguments(\Redaxo\Core\View\DataList::setColumnSortable(), 1, 'asc', 'desc');

expectedArguments(\Redaxo\Core\Security\Permission::register(), 2, \Redaxo\Core\Security\Permission::GENERAL, \Redaxo\Core\Security\Permission::OPTIONS, \Redaxo\Core\Security\Permission::EXTRAS);
expectedArguments(\Redaxo\Core\Security\Permission::getAll(), 0, \Redaxo\Core\Security\Permission::GENERAL, \Redaxo\Core\Security\Permission::OPTIONS, \Redaxo\Core\Security\Permission::EXTRAS);

registerArgumentsSet('status_code', \rex_response::HTTP_OK, \rex_response::HTTP_PARTIAL_CONTENT, \rex_response::HTTP_MOVED_PERMANENTLY, \rex_response::HTTP_NOT_MODIFIED, \rex_response::HTTP_MOVED_TEMPORARILY, \rex_response::HTTP_NOT_FOUND, \rex_response::HTTP_FORBIDDEN, \rex_response::HTTP_UNAUTHORIZED, \rex_response::HTTP_RANGE_NOT_SATISFIABLE, \rex_response::HTTP_INTERNAL_ERROR, \rex_response::HTTP_SERVICE_UNAVAILABLE);
expectedArguments(\rex_response::setStatus(), 0, argumentsSet('status_code'));
expectedReturnValues(\rex_response::getStatus(), argumentsSet('status_code'));

expectedArguments(\rex_response::preload(), 1, 'audio', 'document', 'embed', 'fetch', 'font', 'image', 'object', 'script', 'style', 'track', 'worker', 'video');

expectedArguments(\rex_response::sendFile(), 2, 'inline', 'attachment');
expectedArguments(\rex_response::sendResource(), 4, 'inline', 'attachment');

expectedArguments(\Redaxo\Core\HttpClient\Request::factory(), 1, 80, 443);
expectedArguments(\Redaxo\Core\HttpClient\ProxyRequest::setDestination(), 1, 80, 443);

expectedReturnValues(\Redaxo\Core\Database\Sql::getDbType(), \Redaxo\Core\Database\Sql::MYSQL, \Redaxo\Core\Database\Sql::MARIADB);

registerArgumentsSet('column_type', 'int(10) unsigned', 'int(11)', 'tinyint(1)', 'date', 'datetime', 'time', 'varchar(255)', 'varchar(191)', 'text', 'longtext');
expectedArguments(\Redaxo\Core\Database\Column::__construct(), 1, argumentsSet('column_type'));
expectedArguments(\Redaxo\Core\Database\Column::setType(), 0, argumentsSet('column_extra'));
expectedReturnValues(\Redaxo\Core\Database\Column::getType(), argumentsSet('column_extra'));

registerArgumentsSet('column_extra', 'auto_increment', 'CURRENT_TIMESTAMP');
expectedArguments(\Redaxo\Core\Database\Column::__construct(), 4, argumentsSet('column_extra'));
expectedArguments(\Redaxo\Core\Database\Column::setExtra(), 0, argumentsSet('column_extra'));
expectedReturnValues(\Redaxo\Core\Database\Column::getExtra(), argumentsSet('column_extra'));

registerArgumentsSet('foreign_key_on_clause', \Redaxo\Core\Database\ForeignKey::CASCADE, \Redaxo\Core\Database\ForeignKey::RESTRICT, \Redaxo\Core\Database\ForeignKey::NO_ACTION, \Redaxo\Core\Database\ForeignKey::SET_NULL);
expectedArguments(\Redaxo\Core\Database\ForeignKey::__construct(), 3, argumentsSet('foreign_key_on_clause'));
expectedArguments(\Redaxo\Core\Database\ForeignKey::__construct(), 4, argumentsSet('foreign_key_on_clause'));
expectedArguments(\Redaxo\Core\Database\ForeignKey::setOnUpdate(), 0, argumentsSet('foreign_key_on_clause'));
expectedArguments(\Redaxo\Core\Database\ForeignKey::setOnDelete(), 0, argumentsSet('foreign_key_on_clause'));
expectedReturnValues(\Redaxo\Core\Database\ForeignKey::getOnUpdate(), argumentsSet('foreign_key_on_clause'));
expectedReturnValues(\Redaxo\Core\Database\ForeignKey::getOnDelete(), argumentsSet('foreign_key_on_clause'));

registerArgumentsSet('index_type', \Redaxo\Core\Database\Index::INDEX, \Redaxo\Core\Database\Index::UNIQUE, \Redaxo\Core\Database\Index::FULLTEXT);
expectedArguments(\Redaxo\Core\Database\Index::__construct(), 2, argumentsSet('index_type'));
expectedArguments(\Redaxo\Core\Database\Index::setType(), 0, argumentsSet('index_type'));
expectedReturnValues(\Redaxo\Core\Database\Index::getType(), argumentsSet('index_type'));

expectedArguments(\Redaxo\Core\Util\Version::compare(), 2, '<', '<=', '>', '>=', '==', '!=');

expectedArguments(\Redaxo\Core\Util\Timer::getDelta(), 0, \Redaxo\Core\Util\Timer::SEC, \Redaxo\Core\Util\Timer::MILLISEC, \Redaxo\Core\Util\Timer::MICROSEC);
expectedArguments(\Redaxo\Core\Util\Timer::getFormattedDelta(), 0, \Redaxo\Core\Util\Timer::SEC, \Redaxo\Core\Util\Timer::MILLISEC, \Redaxo\Core\Util\Timer::MICROSEC);

registerArgumentsSet('cast_type', 'bool', 'int', 'float', 'string', 'object', 'array');
expectedArguments(\Redaxo\Core\Util\Type::cast(), 1, argumentsSet('cast_type'));
expectedArguments(\rex_get(), 1, argumentsSet('cast_type'));
expectedArguments(\rex_post(), 1, argumentsSet('cast_type'));
expectedArguments(\rex_request(), 1, argumentsSet('cast_type'));
expectedArguments(\rex_server(), 1, argumentsSet('cast_type'));
expectedArguments(\rex_session(), 1, argumentsSet('cast_type'));
expectedArguments(\rex_cookie(), 1, argumentsSet('cast_type'));
expectedArguments(\rex_files(), 1, argumentsSet('cast_type'));
expectedArguments(\rex_env(), 1, argumentsSet('cast_type'));
expectedArguments(\rex_request::get(), 1, argumentsSet('cast_type'));
expectedArguments(\rex_request::post(), 1, argumentsSet('cast_type'));
expectedArguments(\rex_request::request(), 1, argumentsSet('cast_type'));
expectedArguments(\rex_request::server(), 1, argumentsSet('cast_type'));
expectedArguments(\rex_request::session(), 1, argumentsSet('cast_type'));
expectedArguments(\rex_request::cookie(), 1, argumentsSet('cast_type'));
expectedArguments(\rex_request::files(), 1, argumentsSet('cast_type'));
expectedArguments(\rex_request::env(), 1, argumentsSet('cast_type'));

override(
    \Redaxo\Core\Security\User::getComplexPerm(0),
    map([
        'clang' => \Redaxo\Core\Language\LanguagePermission::class,
        'media' => \Redaxo\Core\MediaPool\MediaPoolPermission::class,
        'modules' => \Redaxo\Core\Content\ModulePermission::class,
        'structure' => \Redaxo\Core\Content\StructurePermission::class,
    ])
);

expectedArguments(\Redaxo\Core\Validator\Validator::add(), 0, 'notEmpty', 'type', 'minLength', 'maxLength', 'min', 'max', 'url', 'email', 'match', 'notMatch', 'values', 'custom');

expectedArguments(\Redaxo\Core\RexVar\RexVar::parse(), 1, \Redaxo\Core\RexVar\RexVar::ENV_BACKEND | \Redaxo\Core\RexVar\RexVar::ENV_FRONTEND | \Redaxo\Core\RexVar\RexVar::ENV_INPUT | \Redaxo\Core\RexVar\RexVar::ENV_OUTPUT);
expectedArguments(\Redaxo\Core\RexVar\RexVar::environmentIs(), 0, \Redaxo\Core\RexVar\RexVar::ENV_BACKEND | \Redaxo\Core\RexVar\RexVar::ENV_FRONTEND | \Redaxo\Core\RexVar\RexVar::ENV_INPUT | \Redaxo\Core\RexVar\RexVar::ENV_OUTPUT);

expectedArguments(\Redaxo\Core\View\Asset::addCssFile(), 1, 'all', 'print', 'screen', 'speech');

expectedArguments(\Symfony\Component\Console\Command\Command::addArgument(), 1, \Symfony\Component\Console\Input\InputArgument::REQUIRED, \Symfony\Component\Console\Input\InputArgument::OPTIONAL, \Symfony\Component\Console\Input\InputArgument::IS_ARRAY);

expectedArguments(\Symfony\Component\Console\Command\Command::addOption(), 2, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, \Symfony\Component\Console\Input\InputOption::VALUE_IS_ARRAY);
