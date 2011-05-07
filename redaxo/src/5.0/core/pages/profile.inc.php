<?php
/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$info = '';
$warning = '';
$user_id = $REX['USER']->getValue('user_id');

// Allgemeine Infos
$userpsw       = rex_request('userpsw', 'string');
$userpsw_new_1 = rex_request('userpsw_new_1', 'string');
$userpsw_new_2 = rex_request('userpsw_new_2', 'string');

$username = rex_request('username', 'string');
$userdesc = rex_request('userdesc', 'string');

// --------------------------------- Title

rex_title(rex_i18n::msg('profile_title'),'');

// --------------------------------- BE LANG

// backend sprache
$sel_be_sprache = new rex_select;
$sel_be_sprache->setStyle('class="rex-form-select"');
$sel_be_sprache->setSize(1);
$sel_be_sprache->setName("userperm_be_sprache");
$sel_be_sprache->setId("userperm-mylang");
$sel_be_sprache->addOption("default","");

$saveLocale = rex_i18n::getLocale();
$langs = array();
foreach(rex_i18n::getLocales() as $locale)
{
	rex_i18n::setLocale($locale,FALSE); // Locale nicht neu setzen
  $sel_be_sprache->addOption(rex_i18n::msg('lang'), $locale);
  $langs[$locale] = rex_i18n::msg('lang');
}
rex_i18n::setLocale($saveLocale, false);
$userperm_be_sprache = rex_request('userperm_be_sprache', 'string');
$userperm_be_sprache_selected = '';
foreach($langs as $k => $v)
{
	if ($REX['LOGIN']->USER->hasPerm('be_lang['.$k.']'))
	{
	  $userperm_be_sprache_selected = $k;
	}
}


// --------------------------------- FUNCTIONS

if (rex_post('upd_profile_button', 'string'))
{
  $updateuser = rex_sql::factory();
  $updateuser->setTable($REX['TABLE_PREFIX'].'user');
  $updateuser->setWhere('user_id='. $user_id);
  $updateuser->setValue('name',$username);
  $updateuser->setValue('description',$userdesc);

  // set be langauage
  $userperm_be_sprache = rex_request("userperm_be_sprache","string");
  if(!isset($langs[$userperm_be_sprache]))
    $userperm_be_sprache = "default";
  $userperm_be_sprache_selected = $userperm_be_sprache;

  $rights = $REX['USER']->removePerm('be_lang');
  $rights .= 'be_lang['.$userperm_be_sprache.']#';
  $updateuser->setValue('rights',$rights);

  $updateuser->addGlobalUpdateFields();

  if($updateuser->update())
    $info = rex_i18n::msg('user_data_updated');
  else
    $warning = $updateuser->getError();
}


if (rex_post('upd_psw_button', 'string'))
{
  $updateuser = rex_sql::factory();
  $updateuser->setTable($REX['TABLE_PREFIX'].'user');
  $updateuser->setWhere('user_id='. $user_id);

  // the service side encryption of pw is only required
  // when not already encrypted by client using javascript
  if ($REX['PSWFUNC'] != '' && rex_post('javascript') == '0')
    $userpsw = call_user_func($REX['PSWFUNC'],$userpsw);

  if($userpsw != '' && $REX['USER']->getValue('psw') == $userpsw && $userpsw_new_1 != '' && $userpsw_new_1 == $userpsw_new_2)
  {
    // the service side encryption of pw is only required
    // when not already encrypted by client using javascript
    if ($REX['PSWFUNC'] != '' && rex_post('javascript') == '0')
      $userpsw_new_1 = call_user_func($REX['PSWFUNC'],$userpsw_new_1);

    $updateuser->setValue('psw',$userpsw_new_1);
    $updateuser->addGlobalUpdateFields();

    if($updateuser->update())
      $info = rex_i18n::msg('user_psw_updated');
    else
      $warning = $updateuser->getError();

  }else
  {
  	$warning = rex_i18n::msg('user_psw_error');
  }

}


$sel_be_sprache->setSelected($userperm_be_sprache_selected);



// ---------------------------------- ERR MSG

if ($info != '')
  echo rex_info($info);

if ($warning != '')
  echo rex_warning($warning);

// --------------------------------- FORMS

$sql = new rex_login_sql;
$sql->setQuery('select * from '. $REX['TABLE_PREFIX'] .'user where user_id='. $user_id);
if ($sql->getRows()!=1)
{
  echo rex_warning('You have no permission to this area!');
}
else
{
  // $userpsw = $sql->getValue($REX['TABLE_PREFIX'].'user.psw');
  $user_name = $sql->getValue($REX['TABLE_PREFIX'].'user.name');
  $user_desc = $sql->getValue($REX['TABLE_PREFIX'].'user.description');
  $user_login = $sql->getValue($REX['TABLE_PREFIX'].'user.login');

  $fragment = new rex_fragment();
  $fragment->setVar('user_name', $user_name);
  $fragment->setVar('user_desc', $user_desc);
  $fragment->setVar('user_login', $user_login);
  $fragment->setVar('backend_language', $sel_be_sprache->get(), false);
  echo $fragment->parse('core_profile');
  unset($fragment);
}