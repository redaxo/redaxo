<?php

use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Http\Request;
use Redaxo\Core\Http\Response;
use Redaxo\Core\Language\LanguageHandler;
use Redaxo\Core\Security\BackendPasswordPolicy;
use Redaxo\Core\Security\Login;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\Message;

$step = Request::request('step', 'int', 1);
$lang = Request::request('lang', 'string');
$func = Request::request('func', 'string');

$context = rex_setup::getContext();

// ---------------------------------- Global Step features

$cancelSetupBtn = '';
if (!rex_setup::isInitialSetup()) {
    $cancelSetupBtn = '
    <style nonce="' . Response::getNonce() . '">
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
    <a href="' . $context->getUrl(['func' => 'abort']) . '" data-confirm="' . I18n::msg('setup_cancel') . '?" class="btn btn-delete rex-cancel-setup">' . I18n::msg('setup_cancel') . '</a>';

    if ('abort' === $func) {
        rex_setup::markSetupCompleted();

        Response::sendRedirect(Url::backendController());
    }
}

// ---------------------------------- Step 1 . Language
if (1 >= $step) {
    require Path::core('pages/setup.step1.php');

    return;
}

// ---------------------------------- Step 2 . Perms, Environment

$errorArray = [];
$successArray = [];

$errors = rex_setup::checkEnvironment();
if (count($errors) > 0) {
    foreach ($errors as $error) {
        $errorArray[] = Message::error($error);
    }
} else {
    $successArray[] = I18n::msg('setup_208', PHP_VERSION);
}

$res = rex_setup::checkFilesystem();
if (count($res) > 0) {
    foreach ($res as $key => $messages) {
        if (count($messages) > 0) {
            $li = [];
            foreach ($messages as $message) {
                $li[] = '<li>' . Path::relative($message) . '</li>';
            }
            $errorArray[] = '<p>' . I18n::msg($key) . '</p><ul>' . implode('', $li) . '</ul>';
        }
    }
} else {
    $successArray[] = I18n::msg('setup_209');
}

if (count($errorArray) > 0) {
    $step = 2;
    $context->setParam('step', $step);
}

if (2 === $step) {
    require Path::core('pages/setup.step2.php');

    return;
}

// ---------------------------------- step 3 . Config

$errorArray = [];

$configFile = Path::coreData('config.yml');
$config = array_merge(
    File::getConfig(Path::core('default.config.yml')),
    File::getConfig($configFile),
);

if (isset($_SERVER['HTTP_HOST']) && 'https://www.redaxo.org/' == $config['server']) {
    $config['server'] = 'https://' . $_SERVER['HTTP_HOST'];
}

if ($step > 3) {
    if ('-1' != Request::post('serveraddress', 'string', '-1')) {
        $config['server'] = Request::post('serveraddress', 'string');
        $config['servername'] = Request::post('servername', 'string');
        $config['lang'] = $lang;
        $config['error_email'] = Request::post('error_email', 'string');
        $config['timezone'] = Request::post('timezone', 'string');
        $config['db'][1]['host'] = trim(Request::post('mysql_host', 'string'));
        $config['db'][1]['login'] = trim(Request::post('redaxo_db_user_login', 'string'));

        $passwd = Request::post('redaxo_db_user_pass', 'string', rex_setup::DEFAULT_DUMMY_PASSWORD);
        if (rex_setup::DEFAULT_DUMMY_PASSWORD != $passwd) {
            $config['db'][1]['password'] = $passwd;
        }
        $config['db'][1]['name'] = trim(Request::post('dbname', 'string'));
        $config['use_https'] = Request::post('use_https', 'string');

        if ('true' === $config['use_https']) {
            $config['use_https'] = true;
        } elseif ('false' === $config['use_https']) {
            $config['use_https'] = false;
        }
    }

    $redaxoDbCreate = Request::post('redaxo_db_create', 'boolean');

    if (empty($config['instname'])) {
        $config['instname'] = 'rex' . date('YmdHis');
    }

    // check if timezone is valid
    if (!@date_default_timezone_set($config['timezone'])) {
        $errorArray[] = Message::error(I18n::msg('setup_313'));
    }

    $check = ['server', 'servername', 'error_email', 'lang'];
    foreach ($check as $key) {
        if (!isset($config[$key]) || !$config[$key]) {
            $errorArray[] = Message::error(I18n::msg($key . '_required'));
            continue;
        }
        try {
            Core::setProperty($key, $config[$key]);
        } catch (InvalidArgumentException) {
            $errorArray[] = Message::error(I18n::msg($key . '_invalid'));
        }
    }

    foreach ($config as $key => $value) {
        if (in_array($key, $check)) {
            continue;
        }
        if (in_array($key, ['fileperm', 'dirperm'])) {
            $value = octdec($value);
        }
        Core::setProperty($key, $value);
    }

    if (0 == count($errorArray)) {
        if (!File::putConfig($configFile, $config)) {
            $errorArray[] = Message::error(I18n::msg('setup_301', Path::relative($configFile)));
        }
    }

    if (0 == count($errorArray)) {
        try {
            Sql::closeConnection();
            $err = rex_setup::checkDb($config, $redaxoDbCreate);
        } catch (PDOException $e) {
            $err = I18n::msg('setup_315', $e->getMessage());
        }

        if ('' != $err) {
            $errorArray[] = Message::error($err);
        }
    }

    if (count($errorArray) > 0) {
        $step = 3;
        $context->setParam('step', $step);
    }
}

if (3 === $step) {
    require Path::core('pages/setup.step3.php');

    return;
}

// ---------------------------------- step 4 . create db / demo

$errors = [];

$createdb = Request::post('createdb', 'int', -1);

if ($step > 4 && $createdb > -1) {
    $tablesComplete = '' == rex_setup_importer::verifyDbSchema();

    if (4 == $createdb) {
        $error = rex_setup_importer::updateFromPrevious();
        if ('' != $error) {
            $errors[] = Message::error($error);
        }
    } elseif (3 == $createdb) {
        $importName = Request::post('import_name', 'string');

        $error = rex_setup_importer::loadExistingImport($importName);
        if ('' != $error) {
            $errors[] = Message::error($error);
        }
    } elseif (2 == $createdb && $tablesComplete) {
        $error = rex_setup_importer::databaseAlreadyExists();
        if ('' != $error) {
            $errors[] = Message::error($error);
        }
    } elseif (1 == $createdb) {
        $error = rex_setup_importer::overrideExisting();
        if ('' != $error) {
            $errors[] = Message::error($error);
        }
    } elseif (0 == $createdb) {
        $error = rex_setup_importer::prepareEmptyDb();
        if ('' != $error) {
            $errors[] = Message::error($error);
        }
    } else {
        $errors[] = Message::error(I18n::msg('error_undefined'));
    }

    if (0 == count($errors)) {
        $error = rex_setup_importer::verifyDbSchema();
        if ('' != $error) {
            $errors[] = $error;
        }
    }

    if (0 == count($errors)) {
        LanguageHandler::generateCache();
        Core::setConfig('version', Core::getVersion());
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
    require Path::core('pages/setup.step4.php');

    return;
}

// ---------------------------------- Step 5 . Create User

$errors = [];

if (6 === $step) {
    $noadmin = Request::post('noadmin', 'int');
    $redaxoUserLogin = Request::post('redaxo_user_login', 'string');
    $redaxoUserPass = Request::post('redaxo_user_pass', 'string');

    if (1 != $noadmin) {
        if ('' == $redaxoUserLogin) {
            $errors[] = Message::error(I18n::msg('setup_501'));
        }

        if ('' == $redaxoUserPass) {
            $errors[] = Message::error(I18n::msg('setup_502'));
        }

        $passwordPolicy = BackendPasswordPolicy::factory();
        if (true !== $msg = $passwordPolicy->check($redaxoUserPass)) {
            $errors[] = Message::error($msg);
        }

        if (0 == count($errors)) {
            $ga = Sql::factory();
            $ga->setQuery('select * from ' . Core::getTablePrefix() . 'user where login = ? ', [$redaxoUserLogin]);

            if ($ga->getRows() > 0) {
                $errors[] = Message::error(I18n::msg('setup_503'));
            } else {
                // the server side encryption of pw is only required
                // when not already encrypted by client using javascript
                $redaxoUserPass = Login::passwordHash($redaxoUserPass);

                $user = Sql::factory();
                // $user->setDebug();
                $user->setTable(Core::getTablePrefix() . 'user');
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
                } catch (rex_sql_exception) {
                    $errors[] = Message::error(I18n::msg('setup_504'));
                }
            }
        }
    } else {
        $gu = Sql::factory();
        $gu->setQuery('select * from ' . Core::getTablePrefix() . 'user LIMIT 1');
        if (0 == $gu->getRows()) {
            $errors[] = Message::error(I18n::msg('setup_505'));
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
    require Path::core('pages/setup.step5.php');

    return;
}

// ---------------------------------- step 6 . thank you . setup false

if (6 === $step) {
    require Path::core('pages/setup.step6.php');
}
