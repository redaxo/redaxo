<?php

$step = rex_request('step', 'int', 1);
$lang = rex_request('lang', 'string');
$func = rex_request('func', 'string');

$context = rex_setup::getContext();

// ---------------------------------- Global Step features

$cancelSetupBtn = '';
if (!rex_setup::isInitialSetup()) {
    $cancelSetupBtn = '
    <style nonce="'.rex_response::getNonce().'">
        .rex-cancel-setup {
            position: absolute;
            top: 7px;
            right: 15px;
            z-index: 1100;
        }
        @media (min-width: 992px) {
            .rex-cancel-setup {
                top: 12px;
            }
        }
        @media (min-width: 1200px) {
            .rex-cancel-setup {
                right: 30px;
            }
        }
    </style>
    <a href="'.$context->getUrl(['func' => 'abort']).'" data-confirm="Cancel Setup?" class="btn btn-delete rex-cancel-setup">'.rex_i18n::msg('setup_cancel').'</a>';

    if ('abort' === $func) {
        rex_setup::markSetupCompleted();

        rex_response::sendRedirect(rex_url::backendController());
    }
}

// ---------------------------------- Step 1 . Language
if (1 >= $step) {
    require rex_path::core('pages/setup.step1.php');

    return;
}

// ---------------------------------- Step 2 . Perms, Environment

$errorArray = [];
$successArray = [];

$errors = rex_setup::checkEnvironment();
if (count($errors) > 0) {
    foreach ($errors as $error) {
        $errorArray[] = rex_view::error($error);
    }
} else {
    $successArray[] = rex_i18n::msg('setup_208');
}

$res = rex_setup::checkFilesystem();
if (count($res) > 0) {
    foreach ($res as $key => $messages) {
        if (count($messages) > 0) {
            $li = [];
            foreach ($messages as $message) {
                $li[] = '<li>' . rex_path::relative($message) . '</li>';
            }
            $errorArray[] = '<p>' . rex_i18n::msg($key) . '</p><ul>' . implode('', $li) . '</ul>';
        }
    }
} else {
    $successArray[] = rex_i18n::msg('setup_209');
}

if (count($errorArray) > 0) {
    $step = 2;
    $context->setParam('step', $step);
}

if (2 === $step) {
    require rex_path::core('pages/setup.step2.php');

    return;
}

// ---------------------------------- step 3 . Config

$errorArray = [];

$configFile = rex_path::coreData('config.yml');
$config = array_merge(
    rex_file::getConfig(rex_path::core('default.config.yml')),
    rex_file::getConfig($configFile),
);

if (isset($_SERVER['HTTP_HOST']) && 'https://www.redaxo.org/' == $config['server']) {
    $config['server'] = 'https://' . $_SERVER['HTTP_HOST'];
}

if ($step > 3) {
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

    $redaxoDbCreate = rex_post('redaxo_db_create', 'boolean');

    if (empty($config['instname'])) {
        $config['instname'] = 'rex' . date('YmdHis');
    }

    // check if timezone is valid
    if (!@date_default_timezone_set($config['timezone'])) {
        $errorArray[] = rex_view::error(rex_i18n::msg('setup_313'));
    }

    $check = ['server', 'servername', 'error_email', 'lang'];
    foreach ($check as $key) {
        if (!isset($config[$key]) || !$config[$key]) {
            $errorArray[] = rex_view::error(rex_i18n::msg($key . '_required'));
            continue;
        }
        try {
            rex::setProperty($key, $config[$key]);
        } catch (InvalidArgumentException $e) {
            $errorArray[] = rex_view::error(rex_i18n::msg($key . '_invalid'));
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

    if (0 == count($errorArray)) {
        if (!rex_file::putConfig($configFile, $config)) {
            $errorArray[] = rex_view::error(rex_i18n::msg('setup_301', rex_path::relative($configFile)));
        }
    }

    if (0 == count($errorArray)) {
        try {
            rex_sql::closeConnection();
            $err = rex_setup::checkDb($config, $redaxoDbCreate);
        } catch (PDOException $e) {
            $err = rex_i18n::msg('setup_315', $e->getMessage());
        }

        if ('' != $err) {
            $errorArray[] = rex_view::error($err);
        }
    }

    if (count($errorArray) > 0) {
        $step = 3;
        $context->setParam('step', $step);
    }
}

if (3 === $step) {
    require rex_path::core('pages/setup.step3.php');

    return;
}

// ---------------------------------- step 4 . create db / demo

$errors = [];

$createdb = rex_post('createdb', 'int', -1);

if ($step > 4 && $createdb > -1) {
    $tablesComplete = '' == rex_setup_importer::verifyDbSchema();

    $utf8mb4 = null;
    if (!in_array($createdb, [2, 3])) {
        $utf8mb4 = rex_setup_importer::supportsUtf8mb4() && rex_post('utf8mb4', 'bool', true);
        rex_sql_table::setUtf8mb4($utf8mb4);
    }

    if (4 == $createdb) {
        $error = rex_setup_importer::updateFromPrevious();
        if ('' != $error) {
            $errors[] = rex_view::error($error);
        }
    } elseif (3 == $createdb) {
        $importName = rex_post('import_name', 'string');

        $error = rex_setup_importer::loadExistingImport($importName);
        if ('' != $error) {
            $errors[] = rex_view::error($error);
        }
    } elseif (2 == $createdb && $tablesComplete) {
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

    if (0 == count($errors)) {
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
        $step = 4;
        $context->setParam('step', $step);
    }
}

if ($step > 4 && '' == !rex_setup_importer::verifyDbSchema()) {
    $step = 4;
    $context->setParam('step', $step);
}

if (4 === $step) {
    require rex_path::core('pages/setup.step4.php');

    return;
}

// ---------------------------------- Step 5 . Create User

$errors = [];

if (6 === $step) {
    $noadmin = rex_post('noadmin', 'int');
    $redaxoUserLogin = rex_post('redaxo_user_login', 'string');
    $redaxoUserPass = rex_post('redaxo_user_pass', 'string');

    if (1 != $noadmin) {
        if ('' == $redaxoUserLogin) {
            $errors[] = rex_view::error(rex_i18n::msg('setup_501'));
        }

        if ('' == $redaxoUserPass) {
            $errors[] = rex_view::error(rex_i18n::msg('setup_502'));
        }

        $passwordPolicy = rex_backend_password_policy::factory();
        if (true !== $msg = $passwordPolicy->check($redaxoUserPass)) {
            $errors[] = rex_view::error($msg);
        }

        if (0 == count($errors)) {
            $ga = rex_sql::factory();
            $ga->setQuery('select * from ' . rex::getTablePrefix() . 'user where login = ? ', [$redaxoUserLogin]);

            if ($ga->getRows() > 0) {
                $errors[] = rex_view::error(rex_i18n::msg('setup_503'));
            } else {
                // the server side encryption of pw is only required
                // when not already encrypted by client using javascript
                $redaxoUserPass = rex_login::passwordHash($redaxoUserPass);

                $user = rex_sql::factory();
                // $user->setDebug();
                $user->setTable(rex::getTablePrefix() . 'user');
                $user->setValue('name', 'Administrator');
                $user->setValue('login', $redaxoUserLogin);
                $user->setValue('password', $redaxoUserPass);
                $user->setValue('admin', 1);
                $user->addGlobalCreateFields('setup');
                $user->addGlobalUpdateFields('setup');
                $user->setDateTimeValue('password_changed', time());
                $user->setArrayValue('previous_passwords', $passwordPolicy->updatePreviousPasswords(null, $redaxoUserPass));
                $user->setValue('status', '1');
                try {
                    $user->insert();
                } catch (rex_sql_exception $e) {
                    $errors[] = rex_view::error(rex_i18n::msg('setup_504'));
                }
            }
        }
    } else {
        $gu = rex_sql::factory();
        $gu->setQuery('select * from ' . rex::getTablePrefix() . 'user LIMIT 1');
        if (0 == $gu->getRows()) {
            $errors[] = rex_view::error(rex_i18n::msg('setup_505'));
        }
    }

    if (0 == count($errors)) {
        $step = 6;
    } else {
        $step = 5;
    }
    $context->setParam('step', $step);
}

if (5 === $step) {
    require rex_path::core('pages/setup.step5.php');

    return;
}

// ---------------------------------- step 6 . thank you . setup false

if (6 === $step) {
    require rex_path::core('pages/setup.step6.php');
}
