<?php
/**
 *
 * @package redaxo5
 */

$info = '';
$warning = '';
$user = rex::getUser();
$user_id = $user->getValue('user_id');

// Allgemeine Infos
$userpsw       = rex_request('userpsw', 'string');
$userpsw_new_1 = rex_request('userpsw_new_1', 'string');
$userpsw_new_2 = rex_request('userpsw_new_2', 'string');

$username = rex_request('username', 'string', $user->getName());
$userdesc = rex_request('userdesc', 'string', $user->getValue('description'));
$userlogin = $user->getUserLogin();

// --------------------------------- Title

echo rex_view::title(rex_i18n::msg('profile_title'), '');

// --------------------------------- BE LANG

// backend sprache
$sel_be_sprache = new rex_select;
$sel_be_sprache->setStyle('class="rex-form-select"');
$sel_be_sprache->setSize(1);
$sel_be_sprache->setName('userperm_be_sprache');
$sel_be_sprache->setId('userperm-mylang');
$sel_be_sprache->addOption('default', '');

$saveLocale = rex_i18n::getLocale();
$langs = array();
foreach (rex_i18n::getLocales() as $locale) {
  rex_i18n::setLocale($locale, false); // Locale nicht neu setzen
  $sel_be_sprache->addOption(rex_i18n::msg('lang'), $locale);
}
rex_i18n::setLocale($saveLocale, false);
$userperm_be_sprache = rex_request('userperm_be_sprache', 'string', $user->getLanguage());
$sel_be_sprache->setSelected($userperm_be_sprache);


// --------------------------------- FUNCTIONS

if (rex_post('upd_profile_button', 'string')) {
  $updateuser = rex_sql::factory();
  $updateuser->setTable(rex::getTablePrefix() . 'user');
  $updateuser->setWhere(array('user_id' => $user_id));
  $updateuser->setValue('name', $username);
  $updateuser->setValue('description', $userdesc);
  $updateuser->setValue('language', $userperm_be_sprache);

  $updateuser->addGlobalUpdateFields();

  try {
    $updateuser->update();
    $info = rex_i18n::msg('user_data_updated');
  } catch (rex_sql_exception $e) {
    $warning = $e->getMessage();
  }
}


if (rex_post('upd_psw_button', 'string')) {
  // the server side encryption of pw is only required
  // when not already encrypted by client using javascript
  $isPreHashed = rex_post('javascript', 'boolean');
  if ($userpsw != '' && $userpsw_new_1 != '' && $userpsw_new_1 == $userpsw_new_2
    && rex_login::passwordVerify($userpsw, $user->getValue('password'), $isPreHashed)
  ) {
    $userpsw_new_1 = rex_login::passwordHash($userpsw_new_1, $isPreHashed);

    $updateuser = rex_sql::factory();
    $updateuser->setTable(rex::getTablePrefix() . 'user');
    $updateuser->setWhere(array('user_id' => $user_id));
    $updateuser->setValue('password', $userpsw_new_1);
    $updateuser->addGlobalUpdateFields();

    try {
      $updateuser->update();
      $info = rex_i18n::msg('user_psw_updated');
    } catch (rex_sql_exception $e) {
      $warning = $e->getMessage();
    }
  } else {
    $warning = rex_i18n::msg('user_psw_error');
  }

}


// ---------------------------------- ERR MSG

if ($info != '')
  echo rex_view::info($info);

if ($warning != '')
  echo rex_view::warning($warning);

// --------------------------------- FORMS


$content = '';
$content .= '
<div id="rex-form-profile" class="rex-form">
  <form action="' . rex_url::currentBackendPage() . '" method="post">
    <fieldset>
      <h2>' . rex_i18n::msg('profile_myprofile') . '</h2>';


          $formElements = array();

            $n = array();
            $n['label'] = '<label for="userlogin">' . rex_i18n::msg('login_name') . '</label>';
            $n['field'] = '<span class="rex-form-read" id="userlogin">' . htmlspecialchars($userlogin) . '</span>';
            $formElements[] = $n;

            $n = array();
            $n['label'] = '<label for="userperm-mylang">' . rex_i18n::msg('backend_language') . '</label>';
            $n['field'] = $sel_be_sprache->get();
            $formElements[] = $n;

            $n = array();
            $n['label'] = '<label for="username">' . rex_i18n::msg('name') . '</label>';
            $n['field'] = '<input type="text" id="username" name="username" value="' . htmlspecialchars($username) . '" />';
            $formElements[] = $n;

            $n = array();
            $n['label'] = '<label for="userdesc">' . rex_i18n::msg('description') . '</label>';
            $n['field'] = '<input type="text" id="userdesc" name="userdesc" value="' . htmlspecialchars($userdesc) . '" />';
            $formElements[] = $n;

          $fragment = new rex_fragment();
          $fragment->setVar('columns', 2, false);
          $fragment->setVar('elements', $formElements, false);
          $content .= $fragment->parse('form.tpl');

$content .= '
    </fieldset>

    <fieldset class="rex-form-action">';

          $formElements = array();

            $n = array();
            $n['field'] = '<input type="submit" name="upd_profile_button" value="' . rex_i18n::msg('profile_save') . '" ' . rex::getAccesskey(rex_i18n::msg('profile_save'), 'save') . ' />';
            $formElements[] = $n;

          $fragment = new rex_fragment();
          $fragment->setVar('elements', $formElements, false);
          $content .= $fragment->parse('form.tpl');

$content .= '
    </fieldset>
  </form>
  </div>';
echo rex_view::contentBlock($content, '', 'block');



$content = '';
$content .= '
  <div id="rex-form-profile-password" class="rex-form">
  <form action="' . rex_url::currentBackendPage() . '" method="post" id="pwformular">
    <input type="hidden" name="javascript" value="0" id="javascript" />
    <fieldset>
      <h2>' . rex_i18n::msg('profile_changepsw') . '</h2>';


          $formElements = array();

            $n = array();
            $n['label'] = '<label for="userpsw">' . rex_i18n::msg('old_password') . '</label>';
            $n['field'] = '<input type="password" id="userpsw" name="userpsw" autocomplete="off" />';
            $formElements[] = $n;

            $n = array();
            $n['field'] = '';
            $formElements[] = $n;

            $n = array();
            $n['label'] = '<label for="userpsw">' . rex_i18n::msg('new_password') . '</label>';
            $n['field'] = '<input class="rex-form-text" type="password" id="userpsw_new_1" name="userpsw_new_1" autocomplete="off" />';
            $formElements[] = $n;

            $n = array();
            $n['label'] = '<label for="userpsw">' . rex_i18n::msg('new_password_repeat') . '</label>';
            $n['field'] = '<input class="rex-form-text" type="password" id="userpsw_new_2" name="userpsw_new_2" autocomplete="off" />';
            $formElements[] = $n;

          $fragment = new rex_fragment();
          $fragment->setVar('columns', 2, false);
          $fragment->setVar('elements', $formElements, false);
          $content .= $fragment->parse('form.tpl');

$content .= '
    </fieldset>

    <fieldset class="rex-form-action">';

          $formElements = array();

            $n = array();
            $n['field'] = '<input class="rex-form-submit" type="submit" name="upd_psw_button" value="' . rex_i18n::msg('profile_save_psw') . '" ' . rex::getAccesskey(rex_i18n::msg('profile_save_psw'), 'save') . ' />';
            $formElements[] = $n;

          $fragment = new rex_fragment();
          $fragment->setVar('elements', $formElements, false);
          $content .= $fragment->parse('form.tpl');

$content .= '
    </fieldset>
  </form>
  </div>

  <script type="text/javascript">
     <!--
    jQuery(function($) {
      $("#username").focus();

      $("#pwformular")
        .submit(function(){
          var pwInp0 = $("#userpsw");
          if(pwInp0.val() != "")
          {
            $("#pwformular").append(\'<input type="hidden" name="\'+pwInp0.attr("name")+\'" value="\'+Sha1.hash(pwInp0.val())+\'" />\');
          }

          var pwInp1 = $("#userpsw_new_1");
          if(pwInp1.val() != "")
          {
            $("#pwformular").append(\'<input type="hidden" name="\'+pwInp1.attr("name")+\'" value="\'+Sha1.hash(pwInp1.val())+\'" />\');
          }

          var pwInp2 = $("#userpsw_new_2");
          if(pwInp2.val() != "")
          {
            $("#pwformular").append(\'<input type="hidden" name="\'+pwInp2.attr("name")+\'" value="\'+Sha1.hash(pwInp2.val())+\'" />\');
          }
      });

      $("#javascript").val("1");
    });
     //-->
  </script>';
echo rex_view::contentBlock($content, '', 'block');
