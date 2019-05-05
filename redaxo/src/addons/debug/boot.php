<?php

// all these debugging capabilities require ChromePhp, which only works in the browser
if (rex_server('REQUEST_URI')) {
    require_once 'vendor/chromephp/ChromePhp.php';

    rex_sql::setFactoryClass('rex_sql_debug');
    rex_extension::setFactoryClass('rex_extension_debug');
    rex_logger::setFactoryClass('rex_logger_debug');
    rex_api_function::setFactoryClass('rex_api_function_debug');
}
