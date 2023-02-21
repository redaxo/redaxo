<?php

$error = '';
$success = '';
$user = rex::requireUser();
$userId = $user->getId();

$login = rex::getProperty('login');
$passwordChangeRequired = $login->requiresPasswordChange();

// Allgemeine Infos
$userpsw = rex_request('userpsw', 'string');
$passkey = rex_request('passkey', 'string');
$userpswNew1 = rex_request('userpsw_new_1', 'string');
$userpswNew2 = rex_request('userpsw_new_2', 'string');

$username = rex_request('username', 'string', $user->getName());
$userdesc = rex_request('userdesc', 'string', $user->getValue('description'));
$useremail = rex_request('useremail', 'string', $user->getValue('email'));
$usertheme = rex_request('usertheme', 'string', $user->getValue('theme')) ?: null;
$userlogin = $user->getLogin();
$csrfToken = rex_csrf_token::factory('profile');
$passwordPolicy = rex_backend_password_policy::factory();
$webauthn = new rex_webauthn();

// --------------------------------- Title
echo rex_view::title(rex_i18n::msg('profile_title'), '');

// --------------------------------- BE LANG

// backend sprache
$userpermBeSprache = rex_request('userperm_be_sprache', 'string', $user->getLanguage());
$selBeSprache = new rex_select();
$selBeSprache->setSize(1);
$selBeSprache->setStyle('class="form-control"');
$selBeSprache->setName('userperm_be_sprache');
$selBeSprache->setId('rex-id-userperm-mylang');
$selBeSprache->setAttribute('class', 'form-control selectpicker');
$selBeSprache->addOption('default', '');
$selBeSprache->setSelected($userpermBeSprache);
$locales = rex_i18n::getLocales();
asort($locales);
foreach ($locales as $locale) {
    $selBeSprache->addOption(rex_i18n::msgInLocale('lang', $locale), $locale);
}

// --------------------------------- Theme

$selBeTheme = new rex_select();
$selBeTheme->setSize(1);
$selBeTheme->setStyle('class="form-control"');
$selBeTheme->setName('usertheme');
$selBeTheme->setId('rex-id-usertheme');
$selBeTheme->setAttribute('class', 'form-control selectpicker');
$selBeTheme->setDisabled(null !== rex::getProperty('theme'));
$selBeTheme->setSelected($usertheme);
$selBeTheme->addOption(rex_i18n::msg('theme_auto'), '');
$selBeTheme->addOption(rex_i18n::msg('theme_light'), 'light');
$selBeTheme->addOption(rex_i18n::msg('theme_dark'), 'dark');

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
    $updateuser->setWhere(['id' => $userId]);
    $updateuser->setValue('name', $username);
    $updateuser->setValue('description', $userdesc);
    $updateuser->setValue('email', $useremail);
    $updateuser->setValue('theme', $usertheme);
    $updateuser->setValue('language', $userpermBeSprache);

    $updateuser->addGlobalUpdateFields();

    try {
        $updateuser->update();
        rex_user::clearInstance($userId);

        rex_extension::registerPoint(new rex_extension_point('PROFILE_UPDATED', '', [
            'user_id' => $userId,
            'user' => rex_user::require($userId),
        ], true));

        // trigger a fullpage-reload which immediately reflects a possible changed language
        rex_response::sendRedirect(rex_url::currentBackendPage(['rex_user_updated' => true], false));
    } catch (rex_sql_exception $e) {
        $error = $e->getMessage();
    }
}

$verifyLogin = static function () use ($user, $login, $userpsw, $webauthn): bool|string {
    if (!$login->getPasskey()) {
        if (!$userpsw || !rex_login::passwordVerify($userpsw, $user->getValue('password'))) {
            return rex_i18n::msg('user_psw_verify_error');
        }

        return true;
    }

    $result = $webauthn->processGet(rex_post('passkey_verify', 'string'));
    if ($result) {
        [$id, $passkeyUser] = $result;

        if ($id === $login->getPasskey() && $passkeyUser->getId() === $user->getId()) {
            return true;
        }
    }

    return rex_i18n::msg('passkey_verify_error');
};

if (rex_post('upd_psw_button', 'bool')) {
    if (!$csrfToken->isValid()) {
        $error = rex_i18n::msg('csrf_token_invalid');
    } elseif (true !== $msg = $verifyLogin()) {
        $error = $msg;
    } elseif (!$userpswNew1 || $userpswNew1 != $userpswNew2) {
        $error = rex_i18n::msg('user_psw_new_error');
    } elseif (true !== $msg = $passwordPolicy->check($userpswNew1, $userId)) {
        $error = $msg;
    } elseif ($passwordChangeRequired && $userpsw === $userpswNew1) {
        $error = rex_i18n::msg('password_not_changed');
    } else {
        $userpswNew1 = rex_login::passwordHash($userpswNew1);

        $updateuser = rex_sql::factory();
        $updateuser->setTable(rex::getTablePrefix() . 'user');
        $updateuser->setWhere(['id' => $userId]);
        $updateuser->setValue('password', $userpswNew1);
        $updateuser->addGlobalUpdateFields();
        $updateuser->setValue('password_change_required', 0);
        $updateuser->setDateTimeValue('password_changed', time());
        $updateuser->setArrayValue('previous_passwords', $passwordPolicy->updatePreviousPasswords($user, $userpswNew1));

        try {
            $updateuser->update();
            rex_user::clearInstance($userId);

            $success = rex_i18n::msg('user_psw_updated');

            if ($passwordChangeRequired) {
                $passwordChangeRequired = false;
            }
            $login->changedPassword($userpswNew1);

            rex_extension::registerPoint(new rex_extension_point('PASSWORD_UPDATED', '', [
                'user_id' => $userId,
                'user' => rex_user::require($userId),
                'password' => $userpswNew2,
            ], true));
        } catch (rex_sql_exception $e) {
            $error = $e->getMessage();
        }
    }
}

if ('add_passkey' === rex_request('function', 'string')) {
    if (!$csrfToken->isValid()) {
        $error = rex_i18n::msg('csrf_token_invalid');
    } elseif (true !== $msg = $verifyLogin()) {
        $error = $msg;
    } else {
        [$passkeyId, $passkeyPublicKey] = $webauthn->processCreate($passkey);

        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('user_passkey'));
        $sql->setValue('id', $passkeyId);
        $sql->setValue('user_id', $userId);
        $sql->setValue('public_key', $passkeyPublicKey);
        $sql->setDateTimeValue('createdate', time());
        $sql->insert();

        $success = rex_i18n::msg('passkey_added');
    }
}

// ---------------------------------- ERR MSG

if ($passwordChangeRequired) {
    echo rex_view::warning(rex_i18n::msg('password_change_required'));
}

if ('' != $success) {
    echo rex_view::success($success);
}

if ('' != $error) {
    echo rex_view::error($error);
}

echo rex_api_function::getMessage();

// --------------------------------- FORMS

$content = '';
$content .= '<fieldset>';

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-id-userlogin">' . rex_i18n::msg('login_name') . '</label>';
$n['field'] = '<span class="form-control-static" id="rex-id-userlogin">' . rex_escape($userlogin) . '</span>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-userperm-mylang">' . rex_i18n::msg('backend_language') . '</label>';
$n['field'] = $selBeSprache->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-username">' . rex_i18n::msg('name') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-id-username" name="username" value="' . rex_escape($username) . '" autocomplete="name" autofocus />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-userdesc">' . rex_i18n::msg('description') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-id-userdesc" name="userdesc" value="' . rex_escape($userdesc) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-useremail">' . rex_i18n::msg('email') . '</label>';
$n['field'] = '<input class="form-control" type="email" id="rex-id-useremail" name="useremail" value="' . rex_escape($useremail) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-usertheme">' . rex_i18n::msg('theme') . '</label>';
$n['field'] = $selBeTheme->get();
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

if (!$passwordChangeRequired) {
    echo $content;
}

$confirmField = static function (string $id) use ($login, $webauthn): string {
    $formElements = [];
    $n = [];

    if ($login->getPasskey()) {
        $n['label'] = '<label for="'.$id.'">' . rex_i18n::msg('passkey_current') . '</label>';
        $n['field'] = '<div data-auth-passkey-verify="'.rex_escape($webauthn->getGetArgs($login->getPasskey())).'">
        <button type="button" class="btn btn-primary" id="'.$id.'">' . rex_i18n::msg('passkey_current_verify') . '</button>
        <i class="fa fa-check-circle-o text-success hidden"></i>
        <input type="hidden" name="passkey_verify"/>
    </div>';
    } else {
        $n['label'] = '<label for="'.$id.'">' . rex_i18n::msg('old_password') . '</label>';
        $n['field'] = '<input class="form-control rex-js-userpsw" type="password" id="'.$id.'" name="userpsw" autocomplete="current-password" required />';
    }

    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('flush', true);
    $fragment->setVar('group', true);
    $fragment->setVar('elements', $formElements, false);

    return $fragment->parse('core/form/form.php');
};

$content = '<fieldset>'.$confirmField('rex-id-userpsw');

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-id-userpsw-new-1">' . rex_i18n::msg('new_password') . '</label>';
$n['field'] = '<input class="form-control rex-js-userpsw-new-1" type="password" id="rex-id-userpsw-new-1" name="userpsw_new_1" autocomplete="new-password" required '.rex_string::buildAttributes($passwordPolicy->getHtmlAttributes()).' />';
$n['note'] = $passwordPolicy->getDescription();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-userpsw-new-2">' . rex_i18n::msg('new_password_repeat') . '</label>';
$n['field'] = '<input class="form-control rex-js-userpsw-new-2" type="password" id="rex-id-userpsw-new-2" name="userpsw_new_2" autocomplete="new-password" required />';
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
$fragment->setVar('title', rex_i18n::msg($user->getValue('password') ? 'profile_changepsw' : 'add_password'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$changePassword = $fragment->parse('core/page/section.php');

$content = '<fieldset>'.$confirmField('rex-id-user-passkey');
$content .= '<input type="hidden" name="passkey" data-auth-passkey="'.rex_escape($webauthn->getCreateArgs()).'"/>';
$content .= '</fieldset>';

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" value="1" name="add_passkey" ' . rex::getAccesskey(rex_i18n::msg('passkey_add'), 'save') . '>' . rex_i18n::msg('passkey_add') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('passkey_add'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$addPasskey = $fragment->parse('core/page/section.php');

$content = '
    <div class="row">
        <div class="col-md-6">
            <form class="rex-js-form-profile-password" action="' . rex_url::currentBackendPage() . '" method="post" data-auth-change-password>
                ' . $csrfToken->getHiddenField() . '
                ' . $changePassword . '
            </form>
        </div>';
if (!$passwordChangeRequired) {
    $content .= '
        <div class="col-md-6">
            <form class="hidden" action="' . rex_url::currentBackendPage() . '" method="post" data-auth-add-passkey>
                <input type="hidden" name="function" value="add_passkey"/>
                ' . $csrfToken->getHiddenField() . '
                ' . $addPasskey . '
            </form>
        </div>';
}
$content .= '
    </div>';

echo $content;

require rex_path::core('pages/profile.auth_methods.php');
require rex_path::core('pages/profile.sessions.php');
