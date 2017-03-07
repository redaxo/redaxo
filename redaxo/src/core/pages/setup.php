<?php

/**
 * @package redaxo5
 */

// --------------------------------------------- END: SETUP FUNCTIONS

$step = rex_request('step', 'int', 1);
$send = rex_request('send', 'string');
$createdb = rex_request('createdb', 'string');
$noadmin = rex_request('noadmin', 'string');
$lang = rex_request('lang', 'string');

// ---------------------------------- Step 1 . Language
if ($step == 1) {
    rex_setup::init();

    $saveLocale = rex_i18n::getLocale();
    $langs = [];
    foreach (rex_i18n::getLocales() as $locale) {
        rex_i18n::setLocale($locale, false); // Locale nicht neu setzen
        $label = rex_i18n::msg('lang');
        $langs[$locale] = '<a class="list-group-item" href="' . rex_url::backendPage('setup', ['step' => 2, 'lang' => $locale]) . '">' . $label . '</a>';
    }
    rex_i18n::setLocale($saveLocale, false);

    echo rex_view::title(rex_i18n::msg('setup_100'));
    $content = '<div class="list-group">' . implode('', $langs) . '</div>';

    $fragment = new rex_fragment();
    $fragment->setVar('heading', rex_i18n::msg('setup_101'), false);
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}

// ---------------------------------- Step 2 . license

if ($step == 2) {
    rex::setProperty('lang', $lang);

    $license_file = rex_path::base('LICENSE.md');
    $license = '<p>' . nl2br(rex_file::get($license_file)) . '</p>';

    $content = rex_i18n::rawMsg('setup_202');
    $content .= $license;

    $buttons = '<a class="btn btn-setup" href="' . rex_url::backendPage('setup', ['step' => 3, 'lang' => $lang]) . '">' . rex_i18n::msg('setup_203') . '</a>';

    echo rex_view::title(rex_i18n::msg('setup_200'));

    $fragment = new rex_fragment();
    $fragment->setVar('heading', rex_i18n::msg('setup_201'), false);
    $fragment->setVar('body', '<div class="rex-scrollable">' . $content . '</div>', false);
    $fragment->setVar('buttons', $buttons, false);
    echo $fragment->parse('core/page/section.php');
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
    $base = rex_path::base();
    foreach ($res as $key => $messages) {
        if (count($messages) > 0) {
            $li = [];
            foreach ($messages as $message) {
                $li[] = '<li>' . str_replace($base, '', $message) . '</li>';
            }
            $error_array[] = '<p>' . rex_i18n::msg($key) . '</p><ul>' . implode('', $li) . '</ul>';
        }
    }
} else {
    $success_array[] = rex_i18n::msg('setup_309');
}

if ($step > 2 && count($error_array) > 0) {
    $step = 3;
}

if ($step == 3) {
    $content = '';

    if (count($success_array) > 0) {
        $content .= '<ul><li>' . implode('</li><li>', $success_array) . '</li></ul>';
    }

    $buttons = '';
    $class = '';
    if (count($error_array) > 0) {
        $class = 'error';
        $content .= implode('', $error_array);

        $buttons = '<a class="btn btn-setup" href="' . rex_url::backendPage('setup', ['step' => 4, 'lang' => $lang]) . '">' . rex_i18n::msg('setup_312') . '</a>';
    } else {
        $class = 'success';
        $buttons = '<a class="btn btn-setup" href="' . rex_url::backendPage('setup', ['step' => 4, 'lang' => $lang]) . '">' . rex_i18n::msg('setup_310') . '</a>';
    }

    $security = '<div class="rex-js-setup-security-message" style="display:none">' . rex_view::error(rex_i18n::msg('setup_security_msg') . '<br />' . rex_i18n::msg('setup_no_js_security_msg')) . '</div>';
    $security .= '<noscript>' . rex_view::error(rex_i18n::msg('setup_no_js_security_msg')) . '</noscript>';
    $security .= '<script>

    jQuery(function($){
        var urls = [
            "' . rex_url::backend('bin/console') . '", 
            "' . rex_url::backend('data/.redaxo') . '", 
            "' . rex_url::backend('src/core/boot.php') . '", 
            "' . rex_url::backend('cache/.redaxo') . '"
        ];
        
        $.each(urls, function (i, url) {
            $.ajax({
                url: url,
                cache: false,
                success: function(data) {
                    $(".rex-js-setup-security-message").show();
                    $(".rex-js-setup-section").hide();
                }
            });
        });

    })

    </script>';

    echo rex_view::title(rex_i18n::msg('setup_300'));

    $fragment = new rex_fragment();
    $fragment->setVar('class', $class, false);
    $fragment->setVar('title', rex_i18n::msg('setup_307'), false);
    $fragment->setVar('body', $content, false);
    $fragment->setVar('buttons', $buttons, false);
    echo '<div class="rex-js-setup-section">' . $fragment->parse('core/page/section.php') . '</div>';
    echo $security;
}

// ---------------------------------- step 4 . Config

$error_array = [];

if ($step >= 4) {
    $configFile = rex_path::coreData('config.yml');
    $config = array_merge(
        rex_file::getConfig(rex_path::core('default.config.yml')),
        rex_file::getConfig($configFile)
    );
}

if ($step > 4 && rex_post('serveraddress', 'string', '-1') != '-1') {
    $config['server'] = rex_post('serveraddress', 'string');
    $config['servername'] = rex_post('servername', 'string');
    $config['lang'] = $lang;
    $config['error_email'] = rex_post('error_email', 'string');
    $config['timezone'] = rex_post('timezone', 'string');
    $config['db'][1]['host'] = rex_post('mysql_host', 'string');
    $config['db'][1]['login'] = rex_post('redaxo_db_user_login', 'string');
    $config['db'][1]['password'] = rex_post('redaxo_db_user_pass', 'string');
    $config['db'][1]['name'] = rex_post('dbname', 'string');
}

if ($step > 4) {
    $redaxo_db_create = rex_post('redaxo_db_create', 'boolean');

    if (empty($config['instname'])) {
        $config['instname'] = 'rex' . date('YmdHis');
    }

    // check if timezone is valid
    if (@date_default_timezone_set($config['timezone']) === false) {
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

    if (count($error_array) == 0) {
        if (!rex_file::putConfig($configFile, $config)) {
            $error_array[] = rex_view::error(rex_i18n::msg('setup_401'));
        }
    }

    if (count($error_array) == 0) {
        try {
            $err = rex_setup::checkDb($config, $redaxo_db_create);
        } catch (PDOException $e) {
            $err = rex_i18n::msg('setup_415', $e->getMessage());
        }

        if ($err != '') {
            $error_array[] = rex_view::error($err);
        }
    }

    if (count($error_array) > 0) {
        $step = 4;
    }
}

if ($step == 4) {
    $headline = rex_view::title(rex_i18n::msg('setup_400'));

    $content = '';

    $submit_message = rex_i18n::msg('setup_410');
    if (count($error_array) > 0) {
        $submit_message = rex_i18n::msg('setup_414');
    }

    $content .= '
            <fieldset>
                <input type="hidden" name="page" value="setup" />
                <input type="hidden" name="step" value="5" />
                <input type="hidden" name="lang" value="' . $lang . '" />';

    $timezone_sel = new rex_select();
    $timezone_sel->setId('rex-form-timezone');
    $timezone_sel->setStyle('class="form-control"');
    $timezone_sel->setName('timezone');
    $timezone_sel->setSize(1);
    $timezone_sel->addOptions(DateTimeZone::listIdentifiers(), true);
    $timezone_sel->setSelected($config['timezone']);

    $db_create_checked = rex_post('redaxo_db_create', 'boolean') ? ' checked="checked"' : '';

    $content .= '<legend>' . rex_i18n::msg('setup_402') . '</legend>';

    $formElements = [];

    $n = [];
    $n['label'] = '<label for="rex-form-serveraddress">' . rex_i18n::msg('server') . '</label>';
    $n['field'] = '<input class="form-control" type="text" id="rex-form-serveraddress" name="serveraddress" value="' . $config['server'] . '" autofocus />';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-form-servername">' . rex_i18n::msg('servername') . '</label>';
    $n['field'] = '<input class="form-control" type="text" id="rex-form-servername" name="servername" value="' . $config['servername'] . '" />';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-form-error-email">' . rex_i18n::msg('error_email') . '</label>';
    $n['field'] = '<input class="form-control" type="text" id="rex-form-error-email" name="error_email" value="' . $config['error_email'] . '" />';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-form-timezone">' . rex_i18n::msg('setup_412') . '</label>';
    $n['field'] = $timezone_sel->get();
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $content .= $fragment->parse('core/form/form.php');

    $content .= '</fieldset><fieldset><legend>' . rex_i18n::msg('setup_403') . '</legend>';

    $formElements = [];

    $n = [];
    $n['label'] = '<label for="rex-form-dbname">' . rex_i18n::msg('setup_408') . '</label>';
    $n['field'] = '<input class="form-control" type="text" value="' . $config['db'][1]['name'] . '" id="rex-form-dbname" name="dbname" />';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for=rex-form-mysql-host">MySQL Host</label>';
    $n['field'] = '<input class="form-control" type="text" id="rex-form-mysql-host" name="mysql_host" value="' . $config['db'][1]['host'] . '" />';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-form-db-user-login">Login</label>';
    $n['field'] = '<input class="form-control" type="text" id="rex-form-db-user-login" name="redaxo_db_user_login" value="' . $config['db'][1]['login'] . '" />';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-form-db-user-pass">' . rex_i18n::msg('setup_409') . '</label>';
    $n['field'] = '<input class="form-control" type="password" id="rex-form-db-user-pass" name="redaxo_db_user_pass" value="' . $config['db'][1]['password'] . '" />';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $content .= $fragment->parse('core/form/form.php');

    $formElements = [];
    $n = [];
    $n['label'] = '<label>' . rex_i18n::msg('setup_411') . '</label>';
    $n['field'] = '<input type="checkbox" name="redaxo_db_create" value="1"' . $db_create_checked . ' />';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $content .= $fragment->parse('core/form/checkbox.php');

    $content .= '</fieldset>';

    $formElements = [];

    $n = [];
    $n['field'] = '<button class="btn btn-setup" type="submit" value="' . rex_i18n::msg('system_update') . '">' . $submit_message . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $buttons = $fragment->parse('core/form/submit.php');

    echo $headline;
    echo implode('', $error_array);

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('setup_416'), false);
    $fragment->setVar('body', $content, false);
    $fragment->setVar('buttons', $buttons, false);
    $content = $fragment->parse('core/page/section.php');

    echo '<form action="' . rex_url::backendController() . '" method="post">' . $content . '</form>';
}

// ---------------------------------- step 5 . create db / demo

$errors = [];

$createdb = rex_post('createdb', 'int', -1);

if ($step > 5 && $createdb > -1) {
    $tables_complete = (rex_setup_importer::verifyDbSchema() == '') ? true : false;

    if ($createdb == 4) {
        $error = rex_setup_importer::updateFromPrevious();
        if ($error != '') {
            $errors[] = rex_view::error($error);
        }
    } elseif ($createdb == 3) {
        $import_name = rex_post('import_name', 'string');
        $error = rex_setup_importer::loadExistingImport($import_name);
        if ($error != '') {
            $errors[] = rex_view::error($error);
        }
    } elseif ($createdb == 2 && $tables_complete) {
        $error = rex_setup_importer::databaseAlreadyExists();
        if ($error != '') {
            $errors[] = rex_view::error($error);
        }
    } elseif ($createdb == 1) {
        $error = rex_setup_importer::overrideExisting();
        if ($error != '') {
            $errors[] = rex_view::error($error);
        }
    } elseif ($createdb == 0) {
        $error = rex_setup_importer::prepareEmptyDb();
        if ($error != '') {
            $errors[] = rex_view::error($error);
        }
    } else {
        $errors[] = rex_view::error(rex_i18n::msg('error_undefined'));
    }

    if (count($errors) == 0 && $createdb !== '') {
        $error = rex_setup_importer::verifyDbSchema();
        if ($error != '') {
            $errors[] = $error;
        }
    }

    if (count($errors) == 0) {
        rex_clang_service::generateCache();
        rex::setConfig('version', rex::getVersion());
    } else {
        $step = 5;
    }
}

if ($step > 5) {
    if (!rex_setup_importer::verifyDbSchema() == '') {
        $step = 5;
    }
}

if ($step == 5) {
    $tables_complete = (rex_setup_importer::verifyDbSchema() == '') ? true : false;

    $createdb = rex_post('createdb', 'int', '');

    $headline = rex_view::title(rex_i18n::msg('setup_500'));

    $content = '
            <fieldset>
                <input type="hidden" name="page" value="setup" />
                <input type="hidden" name="step" value="6" />
                <input type="hidden" name="lang" value="' . $lang . '" />
            ';

    $submit_message = rex_i18n::msg('setup_511');
    if (count($errors) > 0) {
        $errors[] = rex_view::error(rex_i18n::msg('setup_503'));
        $headline .= implode('', $errors);
        $submit_message = rex_i18n::msg('setup_512');
    }

    $dbchecked = array_fill(0, 6, '');
    switch ($createdb) {
        case 1:
        case 2:
        case 3:
        case 4:
            $dbchecked[$createdb] = ' checked="checked"';
            break;
        default:
            $dbchecked[0] = ' checked="checked"';
    }

    // Vorhandene Exporte auslesen
    $sel_export = new rex_select();
    $sel_export->setName('import_name');
    $sel_export->setId('rex-form-import-name');
    $sel_export->setSize(1);
    $sel_export->setStyle('class="form-control rex-js-import-name"');
    $sel_export->setAttribute('onclick', 'checkInput(\'createdb_3\')');
    $export_dir = rex_backup::getDir();
    $exports_found = false;

    if (is_dir($export_dir)) {
        if ($handle = opendir($export_dir)) {
            $export_archives = [];
            $export_sqls = [];

            while (($file = readdir($handle)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                $isSql = (substr($file, strlen($file) - 4) == '.sql');
                $isArchive = (substr($file, strlen($file) - 7) == '.tar.gz');

                if ($isSql) {
                    // cut .sql
                    $export_sqls[] = substr($file, 0, -4);
                    $exports_found = true;
                } elseif ($isArchive) {
                    // cut .tar.gz
                    $export_archives[] = substr($file, 0, -7);
                    $exports_found = true;
                }
            }
            closedir($handle);
        }

        foreach ($export_sqls as $sql_export) {
            $sel_export->addOption($sql_export, $sql_export);
        }
    }

    $formElements = [];

    $n = [];
    $n['label'] = '<label for="rex-form-createdb-0">' . rex_i18n::msg('setup_504') . '</label>';
    $n['field'] = '<input type="radio" id="rex-form-createdb-0" name="createdb" value="0"' . $dbchecked[0] . ' />';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-form-createdb-1">' . rex_i18n::msg('setup_505') . '</label>';
    $n['field'] = '<input type="radio" id="rex-form-createdb-1" name="createdb" value="1"' . $dbchecked[1] . ' />';
    $n['note'] = rex_i18n::msg('setup_505_note');
    $formElements[] = $n;

    if ($tables_complete) {
        $n = [];
        $n['label'] = '<label for="rex-form-createdb-2">' . rex_i18n::msg('setup_506') . '</label>';
        $n['field'] = '<input type="radio" id="rex-form-createdb-2" name="createdb" value="2"' . $dbchecked[2] . ' />';
        $n['note'] = rex_i18n::msg('setup_506_note');
        $formElements[] = $n;
    }

    $n = [];
    $n['label'] = '<label for="rex-form-createdb-4">' . rex_i18n::msg('setup_514') . '</label>';
    $n['field'] = '<input type="radio" id="rex-form-createdb-4" name="createdb" value="4"' . $dbchecked[4] . ' />';
    $n['note'] = rex_i18n::msg('setup_514_note');
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $content .= $fragment->parse('core/form/radio.php');

    if ($exports_found) {
        $formElements = [];
        $n = [];
        $n['label'] = '<label for="rex-form-createdb-3">' . rex_i18n::msg('setup_507') . '</label>';
        $n['field'] = '<input type="radio" id="rex-form-createdb-3" name="createdb" value="3"' . $dbchecked[3] . ' />';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/radio.php');

        $formElements = [];
        $n = [];
        $n['field'] = $sel_export->get();
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/form.php');
    }

    $content .= '</fieldset>';

    $formElements = [];

    $n = [];
    $n['field'] = '<button class="btn btn-setup" type="submit" value="' . $submit_message . '">' . $submit_message . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $buttons = $fragment->parse('core/form/submit.php');

    $content .= '</form>';

    $content .= '
            <script type="text/javascript">
                 <!--
                jQuery(function($) {
                    $(".rex-js-import-name").on("click","",function(){
                        $(".rex-js-setup-step-5 [name=createdb]").prop("checked", false);
                        $(".rex-js-createdb-3").prop("checked", true);
                    });
                });
                 //-->
            </script>';

    echo $headline;
    echo implode('', $error_array);

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('setup_501'), false);
    $fragment->setVar('body', $content, false);
    $fragment->setVar('buttons', $buttons, false);
    $content = $fragment->parse('core/page/section.php');

    echo '<form action="' . rex_url::backendController() . '" method="post">' . $content . '</form>';
}

// ---------------------------------- Step 7 . Create User

$errors = [];

if ($step == 7) {
    $noadmin = rex_post('noadmin', 'int');
    $redaxo_user_login = rex_post('redaxo_user_login', 'string');
    $redaxo_user_pass = rex_post('redaxo_user_pass', 'string');

    if ($noadmin != 1) {
        if ($redaxo_user_login == '') {
            $errors[] = rex_view::error(rex_i18n::msg('setup_601'));
        }

        if ($redaxo_user_pass == '') {
            $errors[] = rex_view::error(rex_i18n::msg('setup_602'));
        }

        if (count($errors) == 0) {
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
        if ($gu->getRows() == 0) {
            $errors[] = rex_view::error(rex_i18n::msg('setup_605'));
        }
    }

    if (count($errors) == 0) {
        $step = 7;
    } else {
        $step = 6;
    }
}

if ($step == 6) {
    $user_sql = rex_sql::factory();
    $user_sql->setQuery('select * from ' . rex::getTablePrefix() . 'user LIMIT 1');

    $headline = rex_view::title(rex_i18n::msg('setup_600'));

    $submit_message = rex_i18n::msg('setup_610');
    if (count($errors) > 0) {
        $submit_message = rex_i18n::msg('setup_611');
        $headline .= implode('', $errors);
    }

    $content = '';

    $content .= '
        <fieldset>
            <input class="rex-js-javascript" type="hidden" name="javascript" value="0" />
            <input type="hidden" name="page" value="setup" />
            <input type="hidden" name="step" value="7" />
            <input type="hidden" name="lang" value="' . $lang . '" />
            ';

    $redaxo_user_login = rex_post('redaxo_user_login', 'string');
    $redaxo_user_pass = rex_post('redaxo_user_pass', 'string');

    if ($user_sql->getRows() > 0) {
        $formElements = [];
        $n = [];

        $checked = '';
        if (!isset($_REQUEST['redaxo_user_login'])) {
            $checked = 'checked="checked"';
        }

        $n['label'] = '<label>' . rex_i18n::msg('setup_609') . '</label>';
        $n['field'] = '<input class="rex-js-noadmin" type="checkbox" name="noadmin" value="1" ' . $checked . ' />';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/checkbox.php');
    }

    $formElements = [];

    $n = [];
    $n['label'] = '<label for="rex-form-redaxo-user-login">' . rex_i18n::msg('setup_607') . '</label>';
    $n['field'] = '<input class="form-control" type="text" value="' . $redaxo_user_login . '" id="rex-form-redaxo-user-login" name="redaxo_user_login" autofocus />';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-form-redaxo-user-pass">' . rex_i18n::msg('setup_608') . '</label>';
    $n['field'] = '<input class="form-control rex-js-redaxo-user-pass" type="password" value="' . $redaxo_user_pass . '" id="rex-form-redaxo-user-pass" name="redaxo_user_pass" />';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $content .= '<div class="rex-js-login-data">' . $fragment->parse('core/form/form.php') . '</div>';

    $content .= '</fieldset>';

    $formElements = [];

    $n = [];
    $n['field'] = '<button class="btn btn-setup" type="submit" value="' . $submit_message . '">' . $submit_message . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $buttons = $fragment->parse('core/form/submit.php');

    $content .= '

    <script type="text/javascript">
         <!--
        jQuery(function($) {
            $(".rex-js-createadminform")
                .submit(function(){
                    var pwInp = $(".rex-js-redaxo-user-pass");
                    if(pwInp.val() != "") {
                        $(".rex-js-createadminform").append(\'<input type="hidden" name="\'+pwInp.attr("name")+\'" value="\'+Sha1.hash(pwInp.val())+\'" />\');
                        pwInp.removeAttr("name");
                    }
            });

            $(".rex-js-javascript").val("1");

            $(".rex-js-createadminform .rex-js-noadmin").on("change",function (){

                if($(this).is(":checked")) {
                    $(".rex-js-login-data").each(function() {
                        $(this).css("display","none");
                    })
                } else {
                    $(".rex-js-login-data").each(function() {
                        $(this).css("display","block");
                    })
                }

            }).trigger("change");

        });
     //-->
    </script>';

    echo $headline;

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('setup_606'), false);
    $fragment->setVar('body', $content, false);
    $fragment->setVar('buttons', $buttons, false);
    $content = $fragment->parse('core/page/section.php');

    echo '<form class="rex-js-createadminform" action="' . rex_url::backendController() . '" method="post" autocomplete="off">' . $content . '</form>';
}

// ---------------------------------- step 7 . thank you . setup false

if ($step == 7) {
    $configFile = rex_path::coreData('config.yml');
    $config = array_merge(
        rex_file::getConfig(rex_path::core('default.config.yml')),
        rex_file::getConfig($configFile)
    );
    $config['setup'] = false;

    if (rex_file::putConfig($configFile, $config)) {
        $errmsg = '';
        rex_file::delete(rex_path::coreCache('config.yml.cache'));
    } else {
        $errmsg = rex_i18n::msg('setup_701');
    }

    $headline = rex_view::title(rex_i18n::msg('setup_700'));

    $content = '<h3>' . rex_i18n::msg('setup_703') . '</h3>';
    $content .= rex_i18n::rawMsg('setup_704', '<a href="' . rex_url::backendController() . '">', '</a>');
    $content .= '<p>' . rex_i18n::msg('setup_705') . '</p>';

    $buttons = '<a class="btn btn-setup" href="' . rex_url::backendController() . '">' . rex_i18n::msg('setup_706') . '</a>';

    echo $headline;

    $fragment = new rex_fragment();
    $fragment->setVar('heading', rex_i18n::msg('setup_702'), false);
    $fragment->setVar('body', $content, false);
    $fragment->setVar('buttons', $buttons, false);
    echo $fragment->parse('core/page/section.php');
}
