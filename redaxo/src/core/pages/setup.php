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
if (1 >= $step) {
    rex_setup::init();

    $langs = [];
    foreach (rex_i18n::getLocales() as $locale) {
        $label = rex_i18n::msgInLocale('lang', $locale);
        $langs[$label] = '<a class="list-group-item" href="' . rex_url::backendPage('setup', ['step' => 2, 'lang' => $locale]) . '">' . $label . '</a>';
    }
    ksort($langs);
    echo rex_view::title(rex_i18n::msg('setup_100'));
    $content = '<div class="list-group">' . implode('', $langs) . '</div>';

    $fragment = new rex_fragment();
    $fragment->setVar('heading', rex_i18n::msg('setup_101'), false);
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');

    return;
}

// ---------------------------------- Step 2 . license

if (2 === $step) {
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
}

if (3 === $step) {
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

    if (!rex_request::isHttps()) {
        $security .= rex_view::warning(rex_i18n::msg('setup_security_no_https'));
    }

    if (function_exists('apache_get_modules') && in_array('mod_security', apache_get_modules())) {
        $security .= rex_view::warning(rex_i18n::msg('setup_security_warn_mod_security'));
    }

    if ('0' !== ini_get('session.auto_start')) {
        $security .= rex_view::warning(rex_i18n::msg('setup_session_autostart_warning'));
    }

    if (1 == version_compare(PHP_VERSION, '7.2', '<') && time() > strtotime('1 Dec 2019')) {
        $security .= rex_view::warning(rex_i18n::msg('setup_security_deprecated_php', PHP_VERSION));
    } elseif (1 == version_compare(PHP_VERSION, '7.3', '<') && time() > strtotime('30 Nov 2020')) {
        $security .= rex_view::warning(rex_i18n::msg('setup_security_deprecated_php', PHP_VERSION));
    } elseif (1 == version_compare(PHP_VERSION, '7.4', '<') && time() > strtotime('6 Dec 2021')) {
        $security .= rex_view::warning(rex_i18n::msg('setup_security_deprecated_php', PHP_VERSION));
    } elseif (1 == version_compare(PHP_VERSION, '8.0', '<') && time() > strtotime('28 Nov 2022')) {
        $security .= rex_view::warning(rex_i18n::msg('setup_security_deprecated_php', PHP_VERSION));
    }

    echo rex_view::title(rex_i18n::msg('setup_300'));

    $fragment = new rex_fragment();
    $fragment->setVar('class', $class, false);
    $fragment->setVar('title', rex_i18n::msg('setup_307'), false);
    $fragment->setVar('body', $content, false);
    $fragment->setVar('buttons', $buttons, false);
    echo '<div class="rex-js-setup-section">' . $fragment->parse('core/page/section.php') . '</div>';
    echo $security;

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

if ($step > 4 && '-1' != rex_post('serveraddress', 'string', '-1')) {
    $config['server'] = rex_post('serveraddress', 'string');
    $config['servername'] = rex_post('servername', 'string');
    $config['lang'] = $lang;
    $config['error_email'] = rex_post('error_email', 'string');
    $config['timezone'] = rex_post('timezone', 'string');
    $config['db'][1]['host'] = rex_post('mysql_host', 'string');
    $config['db'][1]['login'] = rex_post('redaxo_db_user_login', 'string');
    $config['db'][1]['password'] = rex_post('redaxo_db_user_pass', 'string');
    $config['db'][1]['name'] = rex_post('dbname', 'string');
    $config['use_https'] = rex_post('use_https', 'string');

    if ('true' === $config['use_https']) {
        $config['use_https'] = true;
    } elseif ('false' === $config['use_https']) {
        $config['use_https'] = false;
    }
}

if ($step > 4) {
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
    }
}

if (4 === $step) {
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
                <input type="hidden" name="lang" value="' . rex_escape($lang) . '" />';

    $timezone_sel = new rex_select();
    $timezone_sel->setId('rex-form-timezone');
    $timezone_sel->setStyle('class="form-control selectpicker"');
    $timezone_sel->setAttribute('data-live-search', 'true');
    $timezone_sel->setName('timezone');
    $timezone_sel->setSize(1);
    $timezone_sel->addOptions(DateTimeZone::listIdentifiers(), true);
    $timezone_sel->setSelected($config['timezone']);

    $db_create_checked = rex_post('redaxo_db_create', 'boolean') ? ' checked="checked"' : '';

    $httpsRedirectSel = new rex_select();
    $httpsRedirectSel->setId('rex-form-https');
    $httpsRedirectSel->setStyle('class="form-control selectpicker"');
    $httpsRedirectSel->setName('use_https');
    $httpsRedirectSel->setSize(1);
    $httpsRedirectSel->addArrayOptions(['false' => rex_i18n::msg('https_disable'), 'backend' => rex_i18n::msg('https_only_backend'), 'frontend' => rex_i18n::msg('https_only_frontend'), 'true' => rex_i18n::msg('https_activate')]);
    $httpsRedirectSel->setSelected(true === $config['use_https'] ? 'true' : $config['use_https']);

    // If the setup is called over http disable https options to prevent user from being locked out
    if (!rex_request::isHttps()) {
        $httpsRedirectSel->setAttribute('disabled', 'disabled');
    }

    $content .= '<legend>' . rex_i18n::msg('setup_402') . '</legend>';

    $formElements = [];

    $n = [];
    $n['label'] = '<label for="rex-form-serveraddress">' . rex_i18n::msg('server') . '</label>';
    $n['field'] = '<input class="form-control" type="text" id="rex-form-serveraddress" name="serveraddress" value="' . rex_escape($config['server']) . '" autofocus />';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-form-servername">' . rex_i18n::msg('servername') . '</label>';
    $n['field'] = '<input class="form-control" type="text" id="rex-form-servername" name="servername" value="' . rex_escape($config['servername']) . '" />';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-form-error-email">' . rex_i18n::msg('error_email') . '</label>';
    $n['field'] = '<input class="form-control" type="text" id="rex-form-error-email" name="error_email" value="' . rex_escape($config['error_email']) . '" />';
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
    $n['label'] = '<label for=rex-form-mysql-host">MySQL Host</label>';
    $n['field'] = '<input class="form-control" type="text" id="rex-form-mysql-host" name="mysql_host" value="' . rex_escape($config['db'][1]['host']) . '" />';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-form-db-user-login">Login</label>';
    $n['field'] = '<input class="form-control" type="text" id="rex-form-db-user-login" name="redaxo_db_user_login" value="' . rex_escape($config['db'][1]['login']) . '" />';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-form-db-user-pass">' . rex_i18n::msg('setup_409') . '</label>';
    $n['field'] = '<input class="form-control" type="password" id="rex-form-db-user-pass" name="redaxo_db_user_pass" value="' . rex_escape($config['db'][1]['password']) . '" />';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-form-dbname">' . rex_i18n::msg('setup_408') . '</label>';
    $n['field'] = '<input class="form-control" type="text" value="' . rex_escape($config['db'][1]['name']) . '" id="rex-form-dbname" name="dbname" />';
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

    $content .= '</fieldset><fieldset><legend>' . rex_i18n::msg('setup_security') . '</legend>';

    $formElements = [];

    if (!rex_request::isHttps()) {
        $n = [];
        $n['field'] = '<label class="form-control-static"><i class="fa fa-warning"></i> '.rex_i18n::msg('https_only_over_https').'</label>';
        $formElements[] = $n;
    }

    $n = [];
    $n['label'] = '<label>'.rex_i18n::msg('https_activate_redirect_for').'</label>';
    $n['field'] = $httpsRedirectSel->get();
    $formElements[] = $n;

    $n = [];
    $n['field'] = '<p>'.rex_i18n::msg('hsts_more_information').'</p>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $content .= $fragment->parse('core/form/form.php');

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
    }
}

if ($step > 5 && '' == !rex_setup_importer::verifyDbSchema()) {
    $step = 5;
}

if (5 === $step) {
    $tables_complete = ('' == rex_setup_importer::verifyDbSchema()) ? true : false;

    $createdb = rex_post('createdb', 'int', '');

    $supportsUtf8mb4 = rex_setup_importer::supportsUtf8mb4();
    $existingUtf8mb4 = false;
    $utf8mb4 = false;
    if ($supportsUtf8mb4) {
        $utf8mb4 = rex_post('utf8mb4', 'bool', true);
        $existingUtf8mb4 = $utf8mb4;
        if ($tables_complete) {
            $existingUtf8mb4 = rex_sql::factory()->getArray('SELECT value FROM '.rex::getTable('config').' WHERE namespace="core" AND `key`="utf8mb4"')[0]['utf8mb4'] ?? false;
        }
    }

    $headline = rex_view::title(rex_i18n::msg('setup_500'));

    $content = '
            <fieldset class="rex-js-setup-step-5">
                <input type="hidden" name="page" value="setup" />
                <input type="hidden" name="step" value="6" />
                <input type="hidden" name="lang" value="' . rex_escape($lang) . '" />
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
    $sel_export->setStyle('class="form-control selectpicker rex-js-import-name"');
    $sel_export->setAttribute('onclick', 'checkInput(\'createdb_3\')');
    $export_dir = rex_backup::getDir();
    $exports_found = false;

    if (is_dir($export_dir)) {
        if ($handle = opendir($export_dir)) {
            $export_archives = [];
            $export_sqls = [];

            while (false !== ($file = readdir($handle))) {
                if ('.' == $file || '..' == $file) {
                    continue;
                }

                $isSql = ('.sql' == substr($file, strlen($file) - 4));
                $isArchive = ('.tar.gz' == substr($file, strlen($file) - 7));

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
    $mode = $fragment->parse('core/form/radio.php');

    if ($exports_found) {
        $formElements = [];
        $n = [];
        $n['label'] = '<label for="rex-form-createdb-3">' . rex_i18n::msg('setup_507') . '</label>';
        $n['field'] = '<input type="radio" id="rex-form-createdb-3" name="createdb" value="3"' . $dbchecked[3] . ' />';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $mode .= $fragment->parse('core/form/radio.php');

        $formElements = [];
        $n = [];
        $n['field'] = $sel_export->get();
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $mode .= $fragment->parse('core/form/form.php');
    }

    $formElements = [];

    $n = [];
    $n['label'] = '<label for="rex-form-utf8mb4">'.rex_i18n::msg('setup_charset_utf8mb4').'</label>';
    $n['field'] = '<input type="radio" id="rex-form-utf8mb4" name="utf8mb4" value="1"'.($utf8mb4 ? ' checked' : '').($supportsUtf8mb4 ? '' : ' disabled').' />';
    $n['note'] = rex_i18n::msg('setup_charset_utf8mb4_note');
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-form-utf8">'.rex_i18n::msg('setup_charset_utf8').'</label>';
    $n['field'] = '<input type="radio" id="rex-form-utf8" name="utf8mb4" value="0"'.($utf8mb4 ? '' : ' checked').' />';
    $n['note'] = rex_i18n::msg('setup_charset_utf8_note');
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $charset = $fragment->parse('core/form/radio.php');

    $formElements = [];

    $sql = rex_sql::factory();

    $n = [];
    $n['label'] = '<label>'.rex_i18n::msg('version').'</label>';
    $n['field'] = '<p class="form-control-static">'.$sql->getDbType().' '.$sql->getDbVersion().'</p>';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label>'.rex_i18n::msg('mode').'</label>';
    $n['field'] = $mode;
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label>'.rex_i18n::msg('charset').'</label>';
    $n['field'] = $charset;
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $content .= $fragment->parse('core/form/form.php');

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
                    var $container = $(".rex-js-setup-step-5");

                    // when opening backup dropdown -> mark corresponding radio button as checked
                    $container.find(".rex-js-import-name").click(function () {
                        $container.find("[name=createdb][value=3]").prop("checked", true);
                    });

                    if (!$container.find("[name=utf8mb4][value=1]").prop("disabled")) {
                        // when changing mode -> reset disabled state
                        $container.find("[name=createdb]").click(function () {
                            $container.find("[name=utf8mb4]").prop("disabled", false);
                        });

                        // when selecting "existing db" -> select current charset and disable radios
                        $container.find("[name=createdb][value=2]").click(function () {
                            $container.find("[name=utf8mb4][value='.((int) $existingUtf8mb4).']").prop("checked", true);
                            $container.find("[name=utf8mb4]").prop("disabled", true);
                        });

                        // when selecting "update db" -> select utf8mb4 charset
                        $container.find("[name=createdb][value=4]").click(function () {
                            $container.find("[name=utf8mb4][value=1]").prop("checked", true);
                        });

                        // when selecting "import backup" -> disable radios
                        $container.find("[name=createdb][value=3]").click(function () {
                            $container.find("[name=utf8mb4]").prop("disabled", true);
                        });
                    }
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
}

if (6 === $step) {
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
            <input type="hidden" name="lang" value="' . rex_escape($lang) . '" />
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
    $n['field'] = '<input class="form-control" type="text" value="' . rex_escape($redaxo_user_login) . '" id="rex-form-redaxo-user-login" name="redaxo_user_login" autofocus />';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-form-redaxo-user-pass">' . rex_i18n::msg('setup_608') . '</label>';
    $n['field'] = '<input class="form-control rex-js-redaxo-user-pass" type="password" value="' . rex_escape($redaxo_user_pass) . '" id="rex-form-redaxo-user-pass" name="redaxo_user_pass" />';
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

    return;
}

// ---------------------------------- step 7 . thank you . setup false

if (7 === $step) {
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
