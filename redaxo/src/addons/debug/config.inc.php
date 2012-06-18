<?php

require 'vendor/FirePHPCore/fb.php';

rex_sql::setFactoryClass('rex_sql_debug');
rex_extension::setFactoryClass('rex_extension_debug');
rex_logger::setFactoryClass('rex_logger_debug');
rex_api_function::setFactoryClass('rex_api_function_debug');
