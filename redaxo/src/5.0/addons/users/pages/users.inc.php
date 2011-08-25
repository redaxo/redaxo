<?php

/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */



/*
----------------------------- todos

sprachen zugriff
englisch / deutsch / ...
clang

allgemeine zugriffe (array + addons)
  mediapool[]templates[] ...

optionen
  advancedMode[]

zugriff auf folgende categorien
  csw[2] write
  csr[2] read

mulselect zugriff auf mediapool
  media[2]

mulselect module
- liste der module
  module[2]module[3]

*/







$user_id = rex_request('user_id', 'rex-user-id');
$info = '';
$warning = '';

if ($user_id != 0)
{
  $sql = rex_sql::factory();
  $sql->setQuery('SELECT * FROM '.rex::getTablePrefix().'user WHERE user_id = '. $user_id .' LIMIT 2');
  if ($sql->getRows()!= 1) $user_id = 0;
}

// Allgemeine Infos
$userpsw    = rex_request('userpsw', 'string');
$userlogin  = rex_request('userlogin', 'string');
$username   = rex_request('username', 'string');
$userdesc   = rex_request('userdesc', 'string');
$useradmin  = rex_request('useradmin', 'int');
$userstatus = rex_request('userstatus', 'int');


// role
$sel_role = new rex_select;
$sel_role->setStyle('class="rex-form-select"');
$sel_role->setSize(1);
$sel_role->setName("userrole");
$sel_role->setId("userrole");
$sel_role->addOption(rex_i18n::msg('user_no_role'), 0);
$roles = array();
$sql_role = rex_sql::factory();
$sql_role->setQuery('SELECT id, name FROM '. rex::getTablePrefix() .'user_role');
foreach($sql_role as $role)
{
  $roles[$role->getValue('id')] = $role->getValue('name');
  $sel_role->addOption($role->getValue('name'), $role->getValue('id'));
}
$userrole = rex_request('userrole', 'string');

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
}
rex_i18n::setLocale($saveLocale, false);
$userperm_be_sprache = rex_request('userperm_be_sprache', 'string');


// ----- welche startseite
$sel_startpage = new rex_select;
$sel_startpage->setStyle('class="rex-form-select"');
$sel_startpage->setSize(1);
$sel_startpage->setName("userperm_startpage");
$sel_startpage->setId("userperm-startpage");
$sel_startpage->addOption("default","");

$startpages = array();
$startpages['structure'] = array(rex_i18n::msg('structure'),'');
$startpages['profile'] = array(rex_i18n::msg('profile'),'');
// TODO set startpages
/*
foreach($REX['ADDON']['status'] as $k => $v)
{
	if (isset($REX['ADDON']['perm'][$k]) && isset($REX['ADDON']['name'][$k]))
	{
		$startpages[$k] = array($REX['ADDON']['name'][$k],$REX['ADDON']['perm'][$k]);
	}
}
*/

foreach($startpages as $k => $v)
{
  $sel_startpage->addOption($v[0],$k);
}
$userperm_startpage = rex_request('userperm_startpage', 'string');


// --------------------------------- Title



// --------------------------------- FUNCTIONS
$FUNC_UPDATE = '';
$FUNC_APPLY = '';
$FUNC_DELETE = '';
if($user_id != 0 && (rex::getUser()->isAdmin() || !$sql->getValue('admin')))
{
  $FUNC_UPDATE = rex_request("FUNC_UPDATE","string");
  $FUNC_APPLY = rex_request("FUNC_APPLY","string");
  $FUNC_DELETE = rex_request("FUNC_DELETE","string");
}
else
{
  $user_id = 0;
}
$FUNC_ADD = rex_request("FUNC_ADD","string");
$save = rex_request("save","int");
$adminchecked = "";



if ($FUNC_UPDATE != '' || $FUNC_APPLY != '')
{
  $loginReset = rex_request('logintriesreset', 'int');
  $userstatus = rex_request('userstatus', 'int');

  $updateuser = rex_sql::factory();
  $updateuser->setTable(rex::getTablePrefix().'user');
  $updateuser->setWhere('user_id='. $user_id);
  $updateuser->setValue('name',$username);
  $updateuser->setValue('role', $userrole);
  $updateuser->setValue('admin', $useradmin == 1 ? 1 : 0);
  $updateuser->setValue('language', $userperm_be_sprache);
  $updateuser->setValue('startpage', $userperm_startpage);
  $updateuser->addGlobalUpdateFields();
  $updateuser->setValue('description',$userdesc);
  if ($loginReset == 1) $updateuser->setValue('login_tries','0');
  if ($userstatus == 1) $updateuser->setValue('status',1);
  else $updateuser->setValue('status',0);

  if($userpsw != '')
  {
    // the service side encryption of pw is only required
    // when not already encrypted by client using javascript
    if (rex::getProperty('pswfunc') != '' && rex_post('javascript') == '0' && $userpsw != $sql->getValue(rex::getTablePrefix().'user.password'))
      $userpsw = call_user_func(rex::getProperty('pswfunc'),$userpsw);

    $updateuser->setValue('password',$userpsw);
  }

  $updateuser->update();

  if(isset($FUNC_UPDATE) && $FUNC_UPDATE != '')
  {
    $user_id = 0;
    $FUNC_UPDATE = "";
  }

  $info = rex_i18n::msg('user_data_updated');

} elseif ($FUNC_DELETE != '')
{
  // man kann sich selbst nicht l�schen..
  if (rex::getUser()->getValue("user_id") != $user_id)
  {
    $deleteuser = rex_sql::factory();
    $deleteuser->setQuery("DELETE FROM ".rex::getTablePrefix()."user WHERE user_id = '$user_id' LIMIT 1");
    $info = rex_i18n::msg("user_deleted");
    $user_id = 0;
  }else
  {
    $warning = rex_i18n::msg("user_notdeleteself");
  }

} elseif ($FUNC_ADD != '' and $save == 1)
{
  $adduser = rex_sql::factory();
  $adduser->setQuery("SELECT * FROM ".rex::getTablePrefix()."user WHERE login = '$userlogin'");

  if ($adduser->getRows()==0 and $userlogin != '')
  {
    $adduser = rex_sql::factory();
    $adduser->setTable(rex::getTablePrefix().'user');
    $adduser->setValue('name',$username);
    // the service side encryption of pw is only required
    // when not already encrypted by client using javascript
    if (rex::getProperty('pswfunc') != '' && rex_post('javascript') == '0')
      $userpsw = call_user_func(rex::getProperty('pswfunc'),$userpsw);
    $adduser->setValue('password',$userpsw);
    $adduser->setValue('login',$userlogin);
    $adduser->setValue('description',$userdesc);
    $adduser->setValue('admin', $useradmin == 1 ? 1 : 0);
    $adduser->setValue('language', $userperm_be_sprache);
    $adduser->setValue('startpage', $userperm_startpage);
    $adduser->setValue('role', $userrole);
    $adduser->addGlobalCreateFields();
    if (isset($userstatus) and $userstatus == 1) $adduser->setValue('status',1);
    else $adduser->setValue('status',0);

    $adduser->insert();
    $user_id = 0;
    $FUNC_ADD = "";
    $info = rex_i18n::msg('user_added');
  } else
  {

    if ($useradmin == 1) $adminchecked = 'checked="checked"';

    // userrole
    $sel_role->setSelected($userrole);

		// userperm_be_sprache
    if ($userperm_be_sprache == '') $userperm_be_sprache = 'default';
    $sel_be_sprache->setSelected($userperm_be_sprache);

		// userperm_startpage
    if ($userperm_startpage == '') $userperm_startpage = 'default';
    $sel_startpage->setSelected($userperm_startpage);

    $warning = rex_i18n::msg('user_login_exists');
  }
}


// ---------------------------------- ERR MSG

if ($info != '')
  echo rex_info($info);

if ($warning != '')
  echo rex_warning($warning);

// --------------------------------- FORMS

$SHOW = true;

if ($FUNC_ADD != "" || $user_id > 0)
{
  $SHOW = false;

  if ($FUNC_ADD != "") $statuschecked = 'checked="checked"';

  $add_login_reset_chkbox = '';

  if($user_id > 0)
  {
    // User Edit

    $form_label = rex_i18n::msg('edit_user');
    $add_hidden = '<input type="hidden" name="user_id" value="'.$user_id.'" />';
    $add_submit = '<div class="rex-form-row">
						<p class="rex-form-col-a"><input type="submit" class="rex-form-submit" name="FUNC_UPDATE" value="'.rex_i18n::msg('user_save').'" '. rex::getAccesskey(rex_i18n::msg('user_save'), 'save') .' /></p>
						<p class="rex-form-col-b"><input type="submit" class="rex-form-submit" name="FUNC_APPLY" value="'.rex_i18n::msg('user_apply').'" '. rex::getAccesskey(rex_i18n::msg('user_apply'), 'apply') .' /></p>
					</div>';
		$add_user_class = ' rex-form-read';
    $add_user_login = '<span class="rex-form-read" id="userlogin">'. htmlspecialchars($sql->getValue(rex::getTablePrefix().'user.login')) .'</span>';

    $sql = rex_sql::factory();
    $sql->setQuery('select * from '. rex::getTablePrefix() .'user where user_id='. $user_id);

    if ($sql->getRows()==1)
    {
      // ----- EINLESEN DER PERMS
      if ($sql->getValue('admin')) $adminchecked = 'checked="checked"';
      else $adminchecked = '';

      if ($sql->getValue(rex::getTablePrefix().'user.status') == 1) $statuschecked = 'checked="checked"';
      else $statuschecked = '';

      $userrole = $sql->getValue(rex::getTablePrefix().'user.role');
      $sel_role->setSelected($userrole);

			$userperm_be_sprache = $sql->getValue('language');
			$sel_be_sprache->setSelected($userperm_be_sprache);

			$userperm_startpage = $sql->getValue('startpage');
			$sel_startpage->setSelected($userperm_startpage);


      $userpsw = $sql->getValue(rex::getTablePrefix().'user.password');
      $username = $sql->getValue(rex::getTablePrefix().'user.name');
      $userdesc = $sql->getValue(rex::getTablePrefix().'user.description');

      // Der Benutzer kann sich selbst die Rechte nicht entziehen
      if (rex::getUser()->getValue('login') == $sql->getValue(rex::getTablePrefix().'user.login') && $adminchecked != '')
      {
        $add_admin_chkbox = '<input type="hidden" name="useradmin" value="1" /><input class="rex-form-checkbox" type="checkbox" id="useradmin" name="useradmin" value="1" '.$adminchecked.' disabled="disabled" />';
      }
      else
      {
        $add_admin_chkbox = '<input class="rex-form-checkbox" type="checkbox" id="useradmin" name="useradmin" value="1" '.$adminchecked.' />';
      }

      // Der Benutzer kann sich selbst den Status nicht entziehen
      if (rex::getUser()->getValue('login') == $sql->getValue(rex::getTablePrefix().'user.login') && $statuschecked != '')
      {
        $add_status_chkbox = '<input type="hidden" name="userstatus" value="1" /><input class="rex-form-checkbox" type="checkbox" id="userstatus" name="userstatus" value="1" '.$statuschecked.' disabled="disabled" />';
      }
      else
      {
        $add_status_chkbox = '<input class="rex-form-checkbox" type="checkbox" id="userstatus" name="userstatus" value="1" '.$statuschecked.' />';
      }



      // Account gesperrt?
      if (rex::getProperty('maxlogins') < $sql->getValue("login_tries"))
      {
        $add_login_reset_chkbox = '
        <div class="rex-message">
        <p class="rex-warning rex-form-checkbox rex-form-label-right">
        	<span>
	          <input class="rex-form-checkbox" type="checkbox" name="logintriesreset" id="logintriesreset" value="1" />
  	        <label for="logintriesreset">'. rex_i18n::msg("user_reset_tries",rex::getProperty('maxlogins')) .'</label>
  	      </span>
        </p>
        </div>';
      }

    }
  }
  else
  {
    // User Add
    $form_label = rex_i18n::msg('create_user');
    $add_hidden = '<input type="hidden" name="FUNC_ADD" value="1" />';
    $add_submit = '<div class="rex-form-row">
						<p class="rex-form-submit">
						<input type="submit" class="rex-form-submit" name="function" value="'.rex_i18n::msg("add_user").'" '. rex::getAccesskey(rex_i18n::msg('add_user'), 'save') .' />
						</p>
					</div>';
    $add_admin_chkbox = '<input class="rex-form-checkbox" type="checkbox" id="useradmin" name="useradmin" value="1" '.$adminchecked.' />';
    $add_status_chkbox = '<input class="rex-form-checkbox" type="checkbox" id="userstatus" name="userstatus" value="1" '.$statuschecked.' />';
		$add_user_class = ' rex-form-text';
    $add_user_login = '<input class="rex-form-text" type="text" id="userlogin" name="userlogin" value="'.htmlspecialchars($userlogin).'" />';
  }

  echo '
  <div class="rex-form" id="rex-form-user-editmode">
  <form action="index.php" method="post" id="userform">
  	<input type="hidden" name="javascript" value="0" id="javascript" />
    <fieldset class="rex-form-col-2">
      <legend>'.$form_label.'</legend>

      <div class="rex-form-wrapper">
        <input type="hidden" name="page" value="users" />
        <input type="hidden" name="subpage" value="" />
      	<input type="hidden" name="save" value="1" />
      	'. $add_hidden .'

      	'. $add_login_reset_chkbox .'

        <div class="rex-form-row">
          <p class="rex-form-col-a'.$add_user_class.'">
            <label for="userlogin">'. htmlspecialchars(rex_i18n::msg('login_name')).'</label>
            '. $add_user_login .'
          </p>
          <p class="rex-form-col-b rex-form-text">
            <label for="userpsw">'.rex_i18n::msg('password').'</label>
            <input type="password" id="userpsw" name="userpsw" autocomplete="off" />
            '. (rex::getProperty('pswfunc')!='' ? '<span class="rex-form-notice">'. rex_i18n::msg('psw_encrypted') .'</span>' : '') .'
          </p>
		    </div>

        <div class="rex-form-row">
          <p class="rex-form-col-a rex-form-text">
            <label for="username">'.rex_i18n::msg('name').'</label>
            <input type="text" id="username" name="username" value="'.htmlspecialchars($username).'" />
          </p>
          <p class="rex-form-col-b rex-form-text">
            <label for="userdesc">'.rex_i18n::msg('description').'</label>
            <input type="text" id="userdesc" name="userdesc" value="'.htmlspecialchars($userdesc).'" />
          </p>
    		</div>

        <div class="rex-form-row">
          <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
            '. $add_admin_chkbox .'
            <label for="useradmin">'.rex_i18n::msg('user_admin').'</label>
          </p>
          <p class="rex-form-col-b rex-form-checkbox rex-form-label-right">
            '. $add_status_chkbox .'
            <label for="userstatus">'.rex_i18n::msg('user_status').'</label>
          </p>
    		</div>

    		<div class="rex-form-row">
          <p class="rex-form-col-a rex-form-select">
            <label for="userrole">'.rex_i18n::msg('user_role').'</label>
            '. $sel_role->get() .'
          </p>
		    </div>

		    <div class="rex-form-row">
          <p class="rex-form-col-a rex-form-select">
            <label for="userperm-startpage">'.rex_i18n::msg('startpage').'</label>
            '. $sel_startpage->get() .'
          </p>
          <p class="rex-form-col-b rex-form-select">
            <label for="userperm-mylang">'.rex_i18n::msg('backend_language').'</label>
            '.$sel_be_sprache->get().'
          </p>
		    </div>


      '. $add_submit .'

      	<div class="rex-clearer"></div>
      </div>
    </fieldset>
  </form>
  </div>

  <script type="text/javascript">
   <!--
  jQuery(function($) {
    $("#username").focus();

    $("#userform")
      .submit(function(){
      	var pwInp = $("#userpsw");
      	if(pwInp.val() != "")
      	{
        	pwInp.val(Sha1.hash(pwInp.val()));
      	}
    });

    $("#javascript").val("1");
  });
   //-->
</script>
';

}








// ---------------------------------- Userliste

if (isset($SHOW) and $SHOW)
{
  $list = rex_list::factory('SELECT user_id, name, login, admin, lasttrydate FROM '.rex::getTablePrefix().'user ORDER BY name');
  $list->setCaption(rex_i18n::msg('user_caption'));
  $list->addTableAttribute('summary', rex_i18n::msg('user_summary'));
  $list->addTableColumnGroup(array(40, '5%', '*', '*', 50, 153, 153));

  $tdIcon = '<span class="rex-i-element rex-i-user"><span class="rex-i-element-text">###name###</span></span>';
  $thIcon = '<a class="rex-i-element rex-i-user-add" href="'. $list->getUrl(array('FUNC_ADD' => '1')) .'"'. rex::getAccesskey(rex_i18n::msg('create_user'), 'add') .'><span class="rex-i-element-text">'. rex_i18n::msg('create_user') .'</span></a>';
  $list->addColumn($thIcon, $tdIcon, 0, array('<th class="rex-icon">###VALUE###</th>','<td class="rex-icon">###VALUE###</td>'));
  $list->setColumnParams($thIcon, array('user_id' => '###user_id###'));
  $list->setColumnFormat($thIcon, 'custom', function($params) use($thIcon, $tdIcon) {
    $list = $params["list"];
    return !$list->getValue('admin') || rex::getUser()->isAdmin() ? $list->getColumnLink($thIcon, $tdIcon) : $tdIcon;
  });

  $list->setColumnLabel('user_id', 'ID');
  $list->setColumnLayout('user_id', array('<th class="rex-small">###VALUE###</th>','<td class="rex-small">###VALUE###</td>'));

  $list->setColumnLabel('name', rex_i18n::msg('name'));
  $list->setColumnParams('name', array('user_id' => '###user_id###'));
  $list->setColumnFormat('name', 'custom', function ($params) {
    $list = $params["list"];
    $name = htmlspecialchars($list->getValue("name") != "" ? $list->getValue("name") : $list->getValue("login"));
    return !$list->getValue('admin') || rex::getUser()->isAdmin() ? $list->getColumnLink("name", $name) : $name;
  });



  $list->setColumnLabel('login', rex_i18n::msg('login'));

  $list->setColumnLabel('admin', rex_i18n::msg('admin'));
  $list->setColumnFormat('admin', 'custom', function($params) {
    return $params['subject'] ? rex_i18n::msg('yes') : rex_i18n::msg('no');
  });

  $list->setColumnLabel('lasttrydate', rex_i18n::msg('last_login'));
  $list->setColumnFormat('lasttrydate', 'strftime', 'datetime');

  $list->addColumn('funcs', rex_i18n::msg('user_delete'));
  $list->setColumnLabel('funcs', rex_i18n::msg('user_functions'));
  $list->setColumnParams('funcs', array('FUNC_DELETE' => '1', 'user_id' => '###user_id###'));
  $list->setColumnFormat('funcs', 'custom', function ($params) {
    $list = $params['list'];
    if($list->getValue('user_id') == rex::getUser()->getValue('user_id') || $list->getValue('admin') && !rex::getUser()->isAdmin())
    {
      return '<span class="rex-strike">'. rex_i18n::msg('user_delete') .'</span>';
    }
    return $list->getColumnLink('funcs', rex_i18n::msg('user_delete'));
  });
  $list->addLinkAttribute('funcs', 'onclick', 'return confirm(\''.rex_i18n::msg('delete').' ?\')');

  $list->show();
}