<?php

/**
 * @package redaxo5
 */

$step = rex_request('step', 'int', 1);
$send = rex_request('send', 'string');
$createdb = rex_request('createdb', 'string');
$noadmin = rex_request('noadmin', 'string');
$lang = rex_request('lang', 'string');

$context = new rex_context([
    'page' => 'setup',
    'lang' => $lang,
    'step' => $step,
]);

// ---------------------------------- Step 1 . Language
if (1 >= $step) {
    require rex_path::core('pages/setup.step1.php');

    return;
}

// ---------------------------------- Step 2 . license

if (2 === $step) {
    require rex_path::core('pages/setup.step2.php');

    return;
}

// ---------------------------------- Step 3 . Perms, Environment

$error_array = [];
$success_array = [];

$errors = rex_setup::checkEnvironment();
if (count($errors) > 0) {
    foreach ($errors as $error) {
        $error_array[] = rex_view::error($error);
    }
} else {
    $success_array[] = rex_i18n::msg('setup_308');
}

$res = rex_setup::checkFilesystem();
if (count($res) > 0) {
    foreach ($res as $key => $messages) {
        if (count($messages) > 0) {
            $li = [];
            foreach ($messages as $message) {
                $li[] = '<li>' . rex_path::relative($message) . '</li>';
            }
            $error_array[] = '<p>' . rex_i18n::msg($key) . '</p><ul>' . implode('', $li) . '</ul>';
        }
    }
} else {
    $success_array[] = rex_i18n::msg('setup_309');
}

if (count($error_array) > 0) {
    $step = 3;
    $context->setParam('step', $step);
}

if (3 === $step) {
    require rex_path::core('pages/setup.step3.php');

    return;
}

// ---------------------------------- step 4 . Config

$error_array = [];

$configFile = rex_path::coreData('config.yml');
$config = array_merge(
    rex_file::getConfig(rex_path::core('default.config.yml')),
    rex_file::getConfig($configFile)
);

if (isset($_SERVER['HTTP_HOST']) && 'https://www.redaxo.org/' == $config['server']) {
    $config['server'] = 'https://' . $_SERVER['HTTP_HOST'];
}

if ($step > 4) {
    if ('-1' != rex_post('serveraddress', 'string', '-1')) {
        $config['server'] = rex_post('serveraddress', 'string');
        $config['servername'] = rex_post('servername', 'string');
        $config['lang'] = $lang;
        $config['error_email'] = rex_post('error_email', 'string');
        $config['timezone'] = rex_post('timezone', 'string');
        $config['db'][1]['host'] = trim(rex_post('mysql_host', 'string'));
        $config['db'][1]['login'] = trim(rex_post('redaxo_db_user_login', 'string'));

        $passwd = rex_post('redaxo_db_user_pass', 'string', rex_setup::DEFAULT_DUMMY_PASSWORD);
        if (rex_setup::DEFAULT_DUMMY_PASSWORD != $passwd) {
            $config['db'][1]['password'] = $passwd;
        }
        $config['db'][1]['name'] = trim(rex_post('dbname', 'string'));
        $config['use_https'] = rex_post('use_https', 'string');

        if ('true' === $config['use_https']) {
            $config['use_https'] = true;
        } elseif ('false' === $config['use_https']) {
            $config['use_https'] = false;
        }
    }

    $redaxo_db_create = rex_post('redaxo_db_create', 'boolean');

    if (empty($config['instname'])) {
        $config['instname'] = 'rex' . date('YmdHis');
    }

    // check if timezone is valid
    if (false === @date_default_timezone_set($config['timezone'])) {
        $error_array[] = rex_view::error(rex_i18n::msg('setup_413'));
    }

    $check = ['server', 'servername', 'error_email', 'lang'];
    foreach ($check as $key) {
        if (!isset($config[$key]) || !$config[$key]) {
            $error_array[] = rex_view::error(rex_i18n::msg($key . '_required'));
            continue;
        }
        try {
            rex::setProperty($key, $config[$key]);
        } catch (InvalidArgumentException $e) {
            $error_array[] = rex_view::error(rex_i18n::msg($key . '_invalid'));
        }
    }

    foreach ($config as $key => $value) {
        if (in_array($key, $check)) {
            continue;
        }
        if (in_array($key, ['fileperm', 'dirperm'])) {
            $value = octdec($value);
        }
        rex::setProperty($key, $value);
    }

    if (0 == count($error_array)) {
        if (!rex_file::putConfig($configFile, $config)) {
            $error_array[] = rex_view::error(rex_i18n::msg('setup_401'));
        }
    }

    if (0 == count($error_array)) {
        try {
            $err = rex_setup::checkDb($config, $redaxo_db_create);
        } catch (PDOException $e) {
            $err = rex_i18n::msg('setup_415', $e->getMessage());
        }

        if ('' != $err) {
            $error_array[] = rex_view::error($err);
        }
    }

    if (count($error_array) > 0) {
        $step = 4;
        $context->setParam('step', $step);
    }
}

if (4 === $step) {
    require rex_path::core('pages/setup.step4.php');

    return;
}

// ---------------------------------- step 5 . create db / demo

$errors = [];

$createdb = rex_post('createdb', 'int', -1);

if ($step > 5 && $createdb > -1) {
    $tables_complete = ('' == rex_setup_importer::verifyDbSchema()) ? true : false;

    $utf8mb4 = null;
    if (!in_array($step, [2, 3])) {
        $utf8mb4 = rex_setup_importer::supportsUtf8mb4() && rex_post('utf8mb4', 'bool', true);
        rex_sql_table::setUtf8mb4($utf8mb4);
    }

    if (4 == $createdb) {
        $error = rex_setup_importer::updateFromPrevious();
        if ('' != $error) {
            $errors[] = rex_view::error($error);
        }
    } elseif (3 == $createdb) {
        $import_name = rex_post('import_name', 'string');
        $error = rex_setup_importer::loadExistingImport($import_name);
        if ('' != $error) {
            $errors[] = rex_view::error($error);
        }
    } elseif (2 == $createdb && $tables_complete) {
        $error = rex_setup_importer::databaseAlreadyExists();
        if ('' != $error) {
            $errors[] = rex_view::error($error);
        }
    } elseif (1 == $createdb) {
        $error = rex_setup_importer::overrideExisting();
        if ('' != $error) {
            $errors[] = rex_view::error($error);
        }
    } elseif (0 == $createdb) {
        $error = rex_setup_importer::prepareEmptyDb();
        if ('' != $error) {
            $errors[] = rex_view::error($error);
        }
    } else {
        $errors[] = rex_view::error(rex_i18n::msg('error_undefined'));
    }

    if (0 == count($errors) && '' !== $createdb) {
        $error = rex_setup_importer::verifyDbSchema();
        if ('' != $error) {
            $errors[] = $error;
        }
    }

    if (0 == count($errors)) {
        rex_clang_service::generateCache();
        rex::setConfig('version', rex::getVersion());

        if (null !== $utf8mb4) {
            rex::setConfig('utf8mb4', $utf8mb4);
        }
    } else {
        $step = 5;
        $context->setParam('step', $step);
    }
}

if ($step > 5 && '' == !rex_setup_importer::verifyDbSchema()) {
    $step = 5;
    $context->setParam('step', $step);
}

if (5 === $step) {
    require rex_path::core('pages/setup.step5.php');

    return;
}

// ---------------------------------- Step 7 . Create User

$errors = [];

if (7 === $step) {
    $noadmin = rex_post('noadmin', 'int');
    $redaxo_user_login = rex_post('redaxo_user_login', 'string');
    $redaxo_user_pass = rex_post('redaxo_user_pass', 'string');

    if (1 != $noadmin) {
        if ('' == $redaxo_user_login) {
            $errors[] = rex_view::error(rex_i18n::msg('setup_601'));
        }

        if ('' == $redaxo_user_pass) {
            $errors[] = rex_view::error(rex_i18n::msg('setup_602'));
        }

        if (0 == count($errors)) {
            $ga = rex_sql::factory();
            $ga->setQuery('select * from ' . rex::getTablePrefix() . 'user where login = ? ', [$redaxo_user_login]);

            if ($ga->getRows() > 0) {
                $errors[] = rex_view::error(rex_i18n::msg('setup_603'));
            } else {
                // the server side encryption of pw is only required
                // when not already encrypted by client using javascript
                $redaxo_user_pass = rex_login::passwordHash($redaxo_user_pass, rex_post('javascript', 'boolean'));

                $user = rex_sql::factory();
                // $user->setDebug();
                $user->setTable(rex::getTablePrefix() . 'user');
                $user->setValue('name', 'Administrator');
                $user->setValue('login', $redaxo_user_login);
                $user->setValue('password', $redaxo_user_pass);
                $user->setValue('admin', 1);
                $user->addGlobalCreateFields('setup');
                $user->setValue('status', '1');
                try {
                    $user->insert();
                } catch (rex_sql_exception $e) {
                    $errors[] = rex_view::error(rex_i18n::msg('setup_604'));
                }
            }
        }
    } else {
        $gu = rex_sql::factory();
        $gu->setQuery('select * from ' . rex::getTablePrefix() . 'user LIMIT 1');
        if (0 == $gu->getRows()) {
            $errors[] = rex_view::error(rex_i18n::msg('setup_605'));
        }
    }

    if (0 == count($errors)) {
        $step = 7;
    } else {
        $step = 6;
    }
    $context->setParam('step', $step);
}

if (6 === $step) {
    require rex_path::core('pages/setup.step6.php');

    return;
}

// ---------------------------------- step 7 . thank you . setup false

if (7 === $step) {
    require rex_path::core('pages/setup.step7.php');
}
