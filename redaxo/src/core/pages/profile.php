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
$userlogin = $user->getLogin();

// --------------------------------- Title
echo rex_view::title(rex_i18n::msg('profile_title'), '');

// --------------------------------- BE LANG

// backend sprache
$sel_be_sprache = new rex_select();
$sel_be_sprache->setSize(1);
$sel_be_sprache->setStyle('class="form-control"');
$sel_be_sprache->setName('userperm_be_sprache');
$sel_be_sprache->setId('rex-id-userperm-mylang');
$sel_be_sprache->addOption('default', '');

$saveLocale = rex_i18n::getLocale();
$langs = [];
foreach (rex_i18n::getLocales() as $locale) {
    rex_i18n::setLocale($locale, false); // Locale nicht neu setzen
    $sel_be_sprache->addOption(rex_i18n::msg('lang'), $locale);
}
rex_i18n::setLocale($saveLocale, false);
$userperm_be_sprache = rex_request('userperm_be_sprache', 'string', $user->getLanguage());
$sel_be_sprache->setSelected($userperm_be_sprache);

// --------------------------------- FUNCTIONS

if (rex_post('upd_profile_button', 'bool')) {
    $updateuser = rex_sql::factory();
    $updateuser->setTable(rex::getTablePrefix() . 'user');
    $updateuser->setWhere(['id' => $user_id]);
    $updateuser->setValue('name', $username);
    $updateuser->setValue('description', $userdesc);
    $updateuser->setValue('language', $userperm_be_sprache);

    $updateuser->addGlobalUpdateFields();

    try {
        $updateuser->update();
        $success = rex_i18n::msg('user_data_updated');
    } catch (rex_sql_exception $e) {
        $error = $e->getMessage();
    }
}

if (rex_post('upd_psw_button', 'bool')) {
    // the server side encryption of pw is only required
    // when not already encrypted by client using javascript
    $isPreHashed = rex_post('javascript', 'boolean');
    if ($userpsw != '' && $userpsw_new_1 != '' && $userpsw_new_1 == $userpsw_new_2
        && rex_login::passwordVerify($userpsw, $user->getValue('password'), $isPreHashed)
    ) {
        $userpsw_new_1 = rex_login::passwordHash($userpsw_new_1, $isPreHashed);

        $updateuser = rex_sql::factory();
        $updateuser->setTable(rex::getTablePrefix() . 'user');
        $updateuser->setWhere(['id' => $user_id]);
        $updateuser->setValue('password', $userpsw_new_1);
        $updateuser->addGlobalUpdateFields();

        try {
            $updateuser->update();
            $success = rex_i18n::msg('user_psw_updated');
        } catch (rex_sql_exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = rex_i18n::msg('user_psw_error');
    }
}

// ---------------------------------- ERR MSG

if ($success != '') {
    echo rex_view::success($success);
}

if ($error != '') {
    echo rex_view::error($error);
}

// --------------------------------- FORMS

$grid = [];

$content = '';
$content .= '<fieldset>';

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-id-userlogin">' . rex_i18n::msg('login_name') . '</label>';
$n['field'] = '<span class="form-control-static" id="rex-id-userlogin">' . htmlspecialchars($userlogin) . '</span>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-userperm-mylang">' . rex_i18n::msg('backend_language') . '</label>';
$n['field'] = $sel_be_sprache->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-username">' . rex_i18n::msg('name') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-id-username" name="username" value="' . htmlspecialchars($username) . '" autofocus />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-userdesc">' . rex_i18n::msg('description') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-id-userdesc" name="userdesc" value="' . htmlspecialchars($userdesc) . '" />';
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
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        ' . $content . '
    </form>';

echo $content;

$content = '';
$content .= '
    <fieldset>
        <input class="rex-js-javascript" type="hidden" name="javascript" value="0" />';

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
        ' . $content . '
    </form>
    </div>

    <script type="text/javascript">
         <!--
        jQuery(function($) {
            $(".rex-js-form-profile-password")
                .submit(function(){
                    var pwInp0 = $(".rex-js-userpsw");
                    if(pwInp0.val() != "") {
                        $(".rex-js-form-profile-password").append(\'<input type="hidden" name="\'+pwInp0.attr("name")+\'" value="\'+Sha1.hash(pwInp0.val())+\'" />\');
                        pwInp0.removeAttr("name");
                    }

                    var pwInp1 = $(".rex-js-userpsw-new-1");
                    if(pwInp1.val() != "") {
                        $(".rex-js-form-profile-password").append(\'<input type="hidden" name="\'+pwInp1.attr("name")+\'" value="\'+Sha1.hash(pwInp1.val())+\'" />\');
                        pwInp1.removeAttr("name");
                    }

                    var pwInp2 = $(".rex-js-userpsw-new-2");
                    if(pwInp2.val() != "") {
                        $(".rex-js-form-profile-password").append(\'<input type="hidden" name="\'+pwInp2.attr("name")+\'" value="\'+Sha1.hash(pwInp2.val())+\'" />\');
                        pwInp2.removeAttr("name");
                    }
            });

            $(".rex-js-javascript").val("1");
        });
         //-->
    </script>';

echo $content;
