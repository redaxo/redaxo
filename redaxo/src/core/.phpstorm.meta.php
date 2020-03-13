<?php

namespace PHPSTORM_META;

// https://blog.jetbrains.com/phpstorm/2019/02/new-phpstorm-meta-php-features/

override(
    \rex::getProperty(0),
    map([
        'console' => '\rex_console_application|null',
        'login' => '\rex_backend_login|null',
        'timer' => \rex_timer::class,
        'user' => '\rex_user|null',
    ])
);

expectedReturnValues(\rex::getEnvironment(), 'frontend', 'backend', 'console');

expectedArguments(\rex_extension::register(), 2, \rex_extension::EARLY, \rex_extension::NORMAL, \rex_extension::LATE);

expectedArguments(\rex_finder::sort(), 0, \rex_sortable_iterator::KEYS, \rex_sortable_iterator::VALUES);
expectedArguments(\rex_sortable_iterator::__construct(), 1, \rex_sortable_iterator::KEYS, \rex_sortable_iterator::VALUES);

expectedArguments(\rex_form::factory(), 3, 'get', 'post');

registerArgumentsSet('form_field_type', 'text', 'textarea', 'checkbox', 'radio', 'select', 'media', 'medialist', 'link', 'linklist', 'hidden', 'readonly', 'readonlytext', 'control');
expectedArguments(\rex_form::createInput(), 0, argumentsSet('form_field_type'));
expectedArguments(\rex_form_container_element::addField(), 0, argumentsSet('form_field_type'));
expectedArguments(\rex_form_container_element::addGroupedField(), 1, argumentsSet('form_field_type'));

registerArgumentsSet('formatter_type', 'date', 'strftime', 'number', 'bytes', 'sprintf', 'nl2br', 'truncate', 'widont', 'version', 'url', 'email', 'custom');
expectedArguments(\rex_formatter::format(), 1, argumentsSet('formatter_type'));
expectedArguments(\rex_list::setColumnFormat(), 1, argumentsSet('formatter_type'));

registerArgumentsSet('locale', 'de_de', 'en_gb', 'es_es', 'it_it', 'nl_nl', 'pt_br', 'sv_se');
expectedArguments(\rex_i18n::setLocale(), 0, argumentsSet('locale'));
expectedReturnValues(\rex_i18n::getLocale(), argumentsSet('locale'));
expectedReturnValues(\rex_i18n::getLanguage(), 'de', 'en', 'es', 'it', 'nl', 'pt', 'sv');
expectedArguments(\rex_i18n::msgInLocale(), 1, argumentsSet('locale'));
expectedArguments(\rex_i18n::rawMsgInLocale(), 1, argumentsSet('locale'));

expectedArguments(\rex_list::setColumnSortable(), 1, 'asc', 'desc');

expectedArguments(\rex_perm::register(), 2, \rex_perm::GENERAL, \rex_perm::OPTIONS, \rex_perm::EXTRAS);
expectedArguments(\rex_perm::getAll(), 0, \rex_perm::GENERAL, \rex_perm::OPTIONS, \rex_perm::EXTRAS);

registerArgumentsSet('status_code', \rex_response::HTTP_OK, \rex_response::HTTP_PARTIAL_CONTENT, \rex_response::HTTP_MOVED_PERMANENTLY, \rex_response::HTTP_NOT_MODIFIED, \rex_response::HTTP_MOVED_TEMPORARILY, \rex_response::HTTP_NOT_FOUND, \rex_response::HTTP_FORBIDDEN, \rex_response::HTTP_UNAUTHORIZED, \rex_response::HTTP_RANGE_NOT_SATISFIABLE, \rex_response::HTTP_INTERNAL_ERROR, \rex_response::HTTP_SERVICE_UNAVAILABLE);
expectedArguments(\rex_response::setStatus(), 0, argumentsSet('status_code'));
expectedReturnValues(\rex_response::getStatus(), argumentsSet('status_code'));

expectedArguments(\rex_response::preload(), 1, 'audio', 'document', 'embed', 'fetch', 'font', 'image', 'object', 'script', 'style', 'track', 'worker', 'video');

expectedArguments(\rex_response::sendFile(), 2, 'inline', 'attachment');
expectedArguments(\rex_response::sendResource(), 4, 'inline', 'attachment');

expectedArguments(\rex_socket::factory(), 1, 80, 443);
expectedArguments(\rex_socket_proxy::setDestination(), 1, 80, 443);

expectedReturnValues(\rex_sql::getDbType(), \rex_sql::MYSQL, \rex_sql::MARIADB);

registerArgumentsSet('column_type', 'int(10) unsigned', 'int(11)', 'tinyint(1)', 'date', 'datetime', 'time', 'varchar(255)', 'varchar(191)', 'text', 'longtext');
expectedArguments(\rex_sql_column::__construct(), 1, argumentsSet('column_type'));
expectedArguments(\rex_sql_column::setType(), 0, argumentsSet('column_extra'));
expectedReturnValues(\rex_sql_column::getType(), argumentsSet('column_extra'));

registerArgumentsSet('column_extra', 'auto_increment', 'CURRENT_TIMESTAMP');
expectedArguments(\rex_sql_column::__construct(), 4, argumentsSet('column_extra'));
expectedArguments(\rex_sql_column::setExtra(), 0, argumentsSet('column_extra'));
expectedReturnValues(\rex_sql_column::getExtra(), argumentsSet('column_extra'));

registerArgumentsSet('foreign_key_on_clause', \rex_sql_foreign_key::CASCADE, \rex_sql_foreign_key::RESTRICT, \rex_sql_foreign_key::SET_NULL);
expectedArguments(\rex_sql_foreign_key::__construct(), 3, argumentsSet('foreign_key_on_clause'));
expectedArguments(\rex_sql_foreign_key::__construct(), 4, argumentsSet('foreign_key_on_clause'));
expectedArguments(\rex_sql_foreign_key::setOnUpdate(), 0, argumentsSet('foreign_key_on_clause'));
expectedArguments(\rex_sql_foreign_key::setOnDelete(), 0, argumentsSet('foreign_key_on_clause'));
expectedReturnValues(\rex_sql_foreign_key::getOnUpdate(), argumentsSet('foreign_key_on_clause'));
expectedReturnValues(\rex_sql_foreign_key::getOnDelete(), argumentsSet('foreign_key_on_clause'));

registerArgumentsSet('index_type', \rex_sql_index::INDEX, \rex_sql_index::UNIQUE, \rex_sql_index::FULLTEXT);
expectedArguments(\rex_sql_index::__construct(), 2, argumentsSet('index_type'));
expectedArguments(\rex_sql_index::setType(), 0, argumentsSet('index_type'));
expectedReturnValues(\rex_sql_index::getType(), argumentsSet('index_type'));

expectedArguments(\rex_string::versionCompare(), 2, '<', '<=', '>', '>=', '==', '!=');
expectedArguments(\rex_version::compare(), 2, '<', '<=', '>', '>=', '==', '!=');

expectedArguments(\rex_timer::getDelta(), 0, \rex_timer::SEC, \rex_timer::MILLISEC, \rex_timer::MICROSEC);
expectedArguments(\rex_timer::getFormattedDelta(), 0, \rex_timer::SEC, \rex_timer::MILLISEC, \rex_timer::MICROSEC);

registerArgumentsSet('cast_type', 'bool', 'int', 'float', 'string', 'object', 'array');
expectedArguments(\rex_type::cast(), 1, argumentsSet('cast_type'));
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
    \rex_user::getComplexPerm(0),
    map([
        'clang' => \rex_clang_perm::class,
        'media' => \rex_media_perm::class,
        'modules' => \rex_module_perm::class,
        'structure' => \rex_structure_perm::class,
    ])
);

expectedArguments(\rex_validator::add(), 0, 'notEmpty', 'type', 'minLength', 'maxLength', 'min', 'max', 'url', 'email', 'match', 'notMatch', 'values', 'custom');

expectedArguments(\rex_var::parse(), 1, \rex_var::ENV_BACKEND | \rex_var::ENV_FRONTEND | \rex_var::ENV_INPUT | \rex_var::ENV_OUTPUT);
expectedArguments(\rex_var::environmentIs(), 0, \rex_var::ENV_BACKEND | \rex_var::ENV_FRONTEND | \rex_var::ENV_INPUT | \rex_var::ENV_OUTPUT);

expectedArguments(\rex_view::addCssFile(), 1, 'all', 'print', 'screen', 'speech');

expectedArguments(\Symfony\Component\Console\Command\Command::addArgument(), 1, \Symfony\Component\Console\Input\InputArgument::REQUIRED, \Symfony\Component\Console\Input\InputArgument::OPTIONAL, \Symfony\Component\Console\Input\InputArgument::IS_ARRAY);

expectedArguments(\Symfony\Component\Console\Command\Command::addOption(), 2, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, \Symfony\Component\Console\Input\InputOption::VALUE_IS_ARRAY);
