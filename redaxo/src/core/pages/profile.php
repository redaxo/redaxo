<?php

/**
 * @package redaxo5
 */

$error = '';
$success = '';
$user = rex::getUser();
$user_id = $user->getId();

// Allgemeine Infos
$userpsw = rex_request('userpsw', 'string');
$userpsw_new_1 = rex_request('userpsw_new_1', 'string');
$userpsw_new_2 = rex_request('userpsw_new_2', 'string');

$username = rex_request('username', 'string', $user->getName());
$userdesc = rex_request('userdesc', 'string', $user->getValue('description'));
$useremail = rex_request('useremail', 'string', $user->getValue('email'));
$userlogin = $user->getLogin();
$csrfToken = rex_csrf_token::factory('profile');

// --------------------------------- Title
echo rex_view::title(rex_i18n::msg('profile_title'), '');

// --------------------------------- BE LANG

// backend sprache
$userperm_be_sprache = rex_request('userperm_be_sprache', 'string', $user->getLanguage());
$sel_be_sprache = new rex_select();
$sel_be_sprache->setSize(1);
$sel_be_sprache->setStyle('class="form-control"');
$sel_be_sprache->setName('userperm_be_sprache');
$sel_be_sprache->setId('rex-id-userperm-mylang');
$sel_be_sprache->setAttribute('class', 'form-control selectpicker');
$sel_be_sprache->addOption('default', '');
$sel_be_sprache->setSelected($userperm_be_sprache);
$locales = rex_i18n::getLocales();
asort($locales);
foreach ($locales as $locale) {
    $sel_be_sprache->addOption(rex_i18n::msgInLocale('lang', $locale), $locale);
}

// --------------------------------- FUNCTIONS

$update = rex_post('upd_profile_button', 'bool');

if ($update) {
    if (!$csrfToken->isValid()) {
        $error = rex_i18n::msg('csrf_token_invalid');
    } elseif ($useremail && !rex_validator::factory()->email($useremail)) {
        $error = rex_i18n::msg('invalid_email');
    }
}

// Restore success message after redirect
// is necessary to show the whole page in the selected language
if (rex_request('rex_user_updated', 'bool', false)) {
    $success = rex_i18n::msg('user_data_updated');
}

if ($update && !$error) {
    $updateuser = rex_sql::factory();
    $updateuser->setTable(rex::getTablePrefix() . 'user');
    $updateuser->setWhere(['id' => $user_id]);
    $updateuser->setValue('name', $username);
    $updateuser->setValue('description', $userdesc);
    $updateuser->setValue('email', $useremail);
    $updateuser->setValue('language', $userperm_be_sprache);

    $updateuser->addGlobalUpdateFields();

    try {
        $updateuser->update();

        rex_extension::registerPoint(new rex_extension_point('PROFILE_UPDATED', '', [
            'user_id' => $user_id,
            'user' => new rex_user($updateuser->setQuery('SELECT * FROM '.rex::getTable('user').' WHERE id = ?', [$user_id])),
        ], true));

        // trigger a fullpage-reload which immediately reflects a possible changed language
        rex_response::sendRedirect(rex_url::currentBackendPage(['rex_user_updated' => true], false));
    } catch (rex_sql_exception $e) {
        $error = $e->getMessage();
    }
}

if (rex_post('upd_psw_button', 'bool')) {
    if (!$csrfToken->isValid()) {
        $error = rex_i18n::msg('csrf_token_invalid');
    } elseif (!$userpsw || !$userpsw_new_1 || $userpsw_new_1 != $userpsw_new_2 || !rex_login::passwordVerify($userpsw, $user->getValue('password'))) {
        $error = rex_i18n::msg('user_psw_error');
    } elseif (true !== $msg = rex_backend_password_policy::factory(rex::getProperty('password_policy', []))->check($userpsw_new_1, $user_id)) {
        $error = $msg;
    } else {
        $userpsw_new_1 = rex_login::passwordHash($userpsw_new_1);

        $updateuser = rex_sql::factory();
        $updateuser->setTable(rex::getTablePrefix() . 'user');
        $updateuser->setWhere(['id' => $user_id]);
        $updateuser->setValue('password', $userpsw_new_1);
        $updateuser->addGlobalUpdateFields();

        try {
            $updateuser->update();
            $success = rex_i18n::msg('user_psw_updated');

            rex_extension::registerPoint(new rex_extension_point('PASSWORD_UPDATED', '', [
                'user_id' => $user_id,
                'user' => new rex_user($updateuser->setQuery('SELECT * FROM '.rex::getTable('user').' WHERE id = ?', [$user_id])),
                'password' => $userpsw_new_2,
            ], true));
        } catch (rex_sql_exception $e) {
            $error = $e->getMessage();
        }
    }
}

// ---------------------------------- ERR MSG

if ('' != $success) {
    echo rex_view::success($success);
}

if ('' != $error) {
    echo rex_view::error($error);
}

// --------------------------------- FORMS

$grid = [];

$content = '';
$content .= '<fieldset>';

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-id-userlogin">' . rex_i18n::msg('login_name') . '</label>';
$n['field'] = '<span class="form-control-static" id="rex-id-userlogin">' . rex_escape($userlogin) . '</span>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-userperm-mylang">' . rex_i18n::msg('backend_language') . '</label>';
$n['field'] = $sel_be_sprache->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-username">' . rex_i18n::msg('name') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-id-username" name="username" value="' . rex_escape($username) . '" autofocus />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-userdesc">' . rex_i18n::msg('description') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-id-userdesc" name="userdesc" value="' . rex_escape($userdesc) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-useremail">' . rex_i18n::msg('email') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-id-useremail" name="useremail" value="' . rex_escape($useremail) . '" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('group', true);
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset>';

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" value="1" name="upd_profile_button" ' . rex::getAccesskey(rex_i18n::msg('profile_save'), 'save') . '>' . rex_i18n::msg('profile_save') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('profile_myprofile'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

$content = '
    <form action="' . rex_url::currentBackendPage() . '" method="post" data-pjax="false">
        ' . $csrfToken->getHiddenField() . '
        ' . $content . '
    </form>';

echo $content;

$content = '';
$content .= '
    <fieldset>';

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-id-userpsw">' . rex_i18n::msg('old_password') . '</label>';
$n['field'] = '<input class="form-control rex-js-userpsw" type="password" id="rex-id-userpsw" name="userpsw" autocomplete="off" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('group', true);
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-id-userpsw-new-1">' . rex_i18n::msg('new_password') . '</label>';
$n['field'] = '<input class="form-control rex-js-userpsw-new-1" type="password" id="rex-id-userpsw-new-1" name="userpsw_new_1" autocomplete="off" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-userpsw-new-2">' . rex_i18n::msg('new_password_repeat') . '</label>';
$n['field'] = '<input class="form-control rex-js-userpsw-new-2" type="password" id="rex-id-userpsw-new-2" name="userpsw_new_2" autocomplete="off" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('group', true);
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset>';

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" value="1" name="upd_psw_button" ' . rex::getAccesskey(rex_i18n::msg('profile_save_psw'), 'save') . '>' . rex_i18n::msg('profile_save_psw') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('profile_changepsw'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

$content = '
    <form class="rex-js-form-profile-password" action="' . rex_url::currentBackendPage() . '" method="post">
        ' . $csrfToken->getHiddenField() . '
        ' . $content . '
    </form>';

echo $content;
