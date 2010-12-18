<?php

/**
 *
 * @package redaxo4
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
  $sql->setQuery('SELECT * FROM '.$REX['TABLE_PREFIX'].'user WHERE user_id = '. $user_id .' LIMIT 2');
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
$sel_role->addOption($I18N->msg('user_no_role'), 0);
$roles = array();
$sql_role = rex_sql::factory();
$sql_role->setQuery('SELECT id, name FROM '. $REX['TABLE_PREFIX'] .'user_role');
while($sql_role->hasNext())
{
  $roles[$sql_role->getValue('id')] = $sql_role->getValue('name');
  $sel_role->addOption($sql_role->getValue('name'), $sql_role->getValue('id'));
  $sql_role->next();
}
$userrole = rex_request('userrole', 'string');

// backend sprache
$sel_be_sprache = new rex_select;
$sel_be_sprache->setStyle('class="rex-form-select"');
$sel_be_sprache->setSize(1);
$sel_be_sprache->setName("userperm_be_sprache");
$sel_be_sprache->setId("userperm-mylang");
$sel_be_sprache->addOption("default","");
$langpath = $REX['SRC_PATH'].'/core/lang';
$langs = array();
if ($handle = opendir($langpath))
{
	while (false !== ($file = readdir($handle)))
  {
		if (substr($file,-5) == '.lang')
    {
			$locale = substr($file,0,strlen($file)-strlen(substr($file,-5)));
			$I18N_T = rex_create_lang($locale,$langpath,FALSE); // Locale nicht neu setzen
      $sel_be_sprache->addOption($I18N_T->msg('lang'),$locale);
      $langs[$locale] = $I18N_T->msg('lang');
		}
	}
	closedir($handle);
	unset($I18N_T);
}
$userperm_be_sprache = rex_request('userperm_be_sprache', 'string');


// ----- welche startseite
$sel_startpage = new rex_select;
$sel_startpage->setStyle('class="rex-form-select"');
$sel_startpage->setSize(1);
$sel_startpage->setName("userperm_startpage");
$sel_startpage->setId("userperm-startpage");
$sel_startpage->addOption("default","");

$startpages = array();
$startpages['structure'] = array($I18N->msg('structure'),'');
$startpages['profile'] = array($I18N->msg('profile'),'');
foreach($REX['ADDON']['status'] as $k => $v)
{
	if (isset($REX['ADDON']['perm'][$k]) && isset($REX['ADDON']['name'][$k]))
	{
		$startpages[$k] = array($REX['ADDON']['name'][$k],$REX['ADDON']['perm'][$k]);
	}
}

foreach($startpages as $k => $v)
{
  $sel_startpage->addOption($v[0],$k);
}
$userperm_startpage = rex_request('userperm_startpage', 'string');


// --------------------------------- Title



// --------------------------------- FUNCTIONS
$FUNC_UPDATE = rex_request("FUNC_UPDATE","string");
$FUNC_APPLY = rex_request("FUNC_APPLY","string");
$FUNC_DELETE = rex_request("FUNC_DELETE","string");
$FUNC_ADD = rex_request("FUNC_ADD","string");
$save = rex_request("save","int");
$adminchecked = "";



if ($FUNC_UPDATE != '' || $FUNC_APPLY != '')
{
  $loginReset = rex_request('logintriesreset', 'int');
  $userstatus = rex_request('userstatus', 'int');

  // the service side encryption of pw is only required
  // when not already encrypted by client using javascript
  if ($REX['PSWFUNC'] != '' && rex_post('javascript') == '0' && $userpsw != $sql->getValue($REX['TABLE_PREFIX'].'user.psw'))
    $userpsw = call_user_func($REX['PSWFUNC'],$userpsw);

  $updateuser = rex_sql::factory();
  $updateuser->setTable($REX['TABLE_PREFIX'].'user');
  $updateuser->setWhere('user_id='. $user_id);
  $updateuser->setValue('name',$username);
  $updateuser->setValue('role', $userrole);
  $updateuser->addGlobalUpdateFields();
  $updateuser->setValue('psw',$userpsw);
  $updateuser->setValue('description',$userdesc);
  if ($loginReset == 1) $updateuser->setValue('login_tries','0');
  if ($userstatus == 1) $updateuser->setValue('status',1);
  else $updateuser->setValue('status',0);

  $perm = '';
  if ($useradmin == 1)
    $perm .= '#admin[]';

  // userperm_be_sprache
	foreach($langs as $k => $v)
	{
		if($userperm_be_sprache == $k)
		{
		  $perm .= '#be_lang['.$userperm_be_sprache.']';
		  break;
		}
	}

	// userperm_startpage
	foreach($startpages as $k => $v)
	{
	  if($userperm_startpage == $k)
	  {
	    $perm .= '#startpage['.$userperm_startpage.']';
	    break;
	  }
	}

  $updateuser->setValue('rights',$perm.'#');
  $updateuser->update();

  if(isset($FUNC_UPDATE) && $FUNC_UPDATE != '')
  {
    $user_id = 0;
    $FUNC_UPDATE = "";
  }

  $info = $I18N->msg('user_data_updated');

} elseif ($FUNC_DELETE != '')
{
  // man kann sich selbst nicht l�schen..
  if ($REX['USER']->getValue("user_id") != $user_id)
  {
    $deleteuser = rex_sql::factory();
    $deleteuser->setQuery("DELETE FROM ".$REX['TABLE_PREFIX']."user WHERE user_id = '$user_id' LIMIT 1");
    $info = $I18N->msg("user_deleted");
    $user_id = 0;
  }else
  {
    $warning = $I18N->msg("user_notdeleteself");
  }

} elseif ($FUNC_ADD != '' and $save == 1)
{
  $adduser = rex_sql::factory();
  $adduser->setQuery("SELECT * FROM ".$REX['TABLE_PREFIX']."user WHERE login = '$userlogin'");

  if ($adduser->getRows()==0 and $userlogin != '')
  {
    $adduser = rex_sql::factory();
    $adduser->setTable($REX['TABLE_PREFIX'].'user');
    $adduser->setValue('name',$username);
    // the service side encryption of pw is only required
    // when not already encrypted by client using javascript
    if ($REX['PSWFUNC'] != '' && rex_post('javascript') == '0')
      $userpsw = call_user_func($REX['PSWFUNC'],$userpsw);
    $adduser->setValue('psw',$userpsw);
    $adduser->setValue('login',$userlogin);
    $adduser->setValue('description',$userdesc);
    $adduser->setValue('role', $userrole);
    $adduser->addGlobalCreateFields();
    if (isset($userstatus) and $userstatus == 1) $adduser->setValue('status',1);
    else $adduser->setValue('status',0);

    $perm = '';
    if ($useradmin == 1) $perm .= '#'.'admin[]';

    // userperm be sprache
	  foreach($langs as $k => $v)
	  {
	    if($userperm_be_sprache == $k) $perm .= '#be_lang['.$userperm_be_sprache.']';
	  }

    // userperm startpage
	  foreach($startpages as $k => $v)
	  {
	    if($userperm_startpage == $k) $perm .= '#startpage['.$userperm_startpage.']';
	  }


    $adduser->setValue('rights',$perm.'#');
    $adduser->insert();
    $user_id = 0;
    $FUNC_ADD = "";
    $info = $I18N->msg('user_added');
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

    $warning = $I18N->msg('user_login_exists');
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

    $form_label = $I18N->msg('edit_user');
    $add_hidden = '<input type="hidden" name="user_id" value="'.$user_id.'" />';
    $add_submit = '<div class="rex-form-row">
						<p class="rex-form-col-a"><input type="submit" class="rex-form-submit" name="FUNC_UPDATE" value="'.$I18N->msg('user_save').'" '. rex_accesskey($I18N->msg('user_save'), $REX['ACKEY']['SAVE']) .' /></p>
						<p class="rex-form-col-b"><input type="submit" class="rex-form-submit" name="FUNC_APPLY" value="'.$I18N->msg('user_apply').'" '. rex_accesskey($I18N->msg('user_apply'), $REX['ACKEY']['APPLY']) .' /></p>
					</div>';
		$add_user_class = ' rex-form-read';
    $add_user_login = '<span class="rex-form-read" id="userlogin">'. htmlspecialchars($sql->getValue($REX['TABLE_PREFIX'].'user.login')) .'</span>';

    $sql = new rex_login_sql;
    $sql->setQuery('select * from '. $REX['TABLE_PREFIX'] .'user where user_id='. $user_id);

    if ($sql->getRows()==1)
    {
      // ----- EINLESEN DER PERMS
      if ($sql->isAdmin()) $adminchecked = 'checked="checked"';
      else $adminchecked = '';

      if ($sql->getValue($REX['TABLE_PREFIX'].'user.status') == 1) $statuschecked = 'checked="checked"';
      else $statuschecked = '';

      $userrole = $sql->getValue($REX['TABLE_PREFIX'].'user.role');
      $sel_role->setSelected($userrole);

			foreach($langs as $k => $v)
			{
				if ($sql->hasPerm('be_lang['.$k.']')) $userperm_be_sprache = $k;
			}
			$sel_be_sprache->setSelected($userperm_be_sprache);

			foreach($startpages as $k => $v)
			{
				if ($sql->hasPerm('startpage['.$k.']')) $userperm_startpage = $k;
			}
			$sel_startpage->setSelected($userperm_startpage);


      $userpsw = $sql->getValue($REX['TABLE_PREFIX'].'user.psw');
      $username = $sql->getValue($REX['TABLE_PREFIX'].'user.name');
      $userdesc = $sql->getValue($REX['TABLE_PREFIX'].'user.description');

      // Der Benutzer kann sich selbst die Rechte nicht entziehen
      if ($REX['USER']->getValue('login') == $sql->getValue($REX['TABLE_PREFIX'].'user.login') && $adminchecked != '')
      {
        $add_admin_chkbox = '<input type="hidden" name="useradmin" value="1" /><input class="rex-form-checkbox" type="checkbox" id="useradmin" name="useradmin" value="1" '.$adminchecked.' disabled="disabled" />';
      }
      else
      {
        $add_admin_chkbox = '<input class="rex-form-checkbox" type="checkbox" id="useradmin" name="useradmin" value="1" '.$adminchecked.' />';
      }

      // Der Benutzer kann sich selbst den Status nicht entziehen
      if ($REX['USER']->getValue('login') == $sql->getValue($REX['TABLE_PREFIX'].'user.login') && $statuschecked != '')
      {
        $add_status_chkbox = '<input type="hidden" name="userstatus" value="1" /><input class="rex-form-checkbox" type="checkbox" id="userstatus" name="userstatus" value="1" '.$statuschecked.' disabled="disabled" />';
      }
      else
      {
        $add_status_chkbox = '<input class="rex-form-checkbox" type="checkbox" id="userstatus" name="userstatus" value="1" '.$statuschecked.' />';
      }



      // Account gesperrt?
      if ($REX['MAXLOGINS'] < $sql->getValue("login_tries"))
      {
        $add_login_reset_chkbox = '
        <div class="rex-message">
        <p class="rex-warning rex-form-checkbox rex-form-label-right">
        	<span>
	          <input class="rex-form-checkbox" type="checkbox" name="logintriesreset" id="logintriesreset" value="1" />
  	        <label for="logintriesreset">'. $I18N->msg("user_reset_tries",$REX['MAXLOGINS']) .'</label>
  	      </span>
        </p>
        </div>';
      }

    }
  }
  else
  {
    // User Add
    $form_label = $I18N->msg('create_user');
    $add_hidden = '<input type="hidden" name="FUNC_ADD" value="1" />';
    $add_submit = '<div class="rex-form-row">
						<p class="rex-form-submit">
						<input type="submit" class="rex-form-submit" name="function" value="'.$I18N->msg("add_user").'" '. rex_accesskey($I18N->msg('add_user'), $REX['ACKEY']['SAVE']) .' />
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
            <label for="userlogin">'. htmlspecialchars($I18N->msg('login_name')).'</label>
            '. $add_user_login .'
          </p>
          <p class="rex-form-col-b rex-form-text">
            <label for="userpsw">'.$I18N->msg('password').'</label>
            <input type="text" id="userpsw" name="userpsw" value="'.htmlspecialchars($userpsw).'" />
            '. ($REX['PSWFUNC']!='' ? '<span class="rex-form-notice">'. $I18N->msg('psw_encrypted') .'</span>' : '') .'
          </p>
		    </div>

        <div class="rex-form-row">
          <p class="rex-form-col-a rex-form-text">
            <label for="username">'.$I18N->msg('name').'</label>
            <input type="text" id="username" name="username" value="'.htmlspecialchars($username).'" />
          </p>
          <p class="rex-form-col-b rex-form-text">
            <label for="userdesc">'.$I18N->msg('description').'</label>
            <input type="text" id="userdesc" name="userdesc" value="'.htmlspecialchars($userdesc).'" />
          </p>
    		</div>

        <div class="rex-form-row">
          <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
            '. $add_admin_chkbox .'
            <label for="useradmin">'.$I18N->msg('user_admin').'</label>
          </p>
          <p class="rex-form-col-b rex-form-checkbox rex-form-label-right">
            '. $add_status_chkbox .'
            <label for="userstatus">'.$I18N->msg('user_status').'</label>
          </p>
    		</div>

    		<div class="rex-form-row">
          <p class="rex-form-col-a rex-form-select">
            <label for="userrole">'.$I18N->msg('user_role').'</label>
            '. $sel_role->get() .'
          </p>
		    </div>

		    <div class="rex-form-row">
          <p class="rex-form-col-a rex-form-select">
            <label for="userperm-startpage">'.$I18N->msg('startpage').'</label>
            '. $sel_startpage->get() .'
          </p>
          <p class="rex-form-col-b rex-form-select">
            <label for="userperm-mylang">'.$I18N->msg('backend_language').'</label>
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
  $list = rex_list::factory('SELECT user_id, name, login, lasttrydate FROM '.$REX['TABLE_PREFIX'].'user ORDER BY name');
  $list->setCaption($I18N->msg('user_caption'));
  $list->addTableAttribute('summary', $I18N->msg('user_summary'));
  $list->addTableColumnGroup(array(40, '5%', '*', 153, 153, 153));

  $tdIcon = '<span class="rex-i-element rex-i-user"><span class="rex-i-element-text">###name###</span></span>';
  $thIcon = '<a class="rex-i-element rex-i-user-add" href="'. $list->getUrl(array('FUNC_ADD' => '1')) .'"'. rex_accesskey($I18N->msg('create_user'), $REX['ACKEY']['ADD']) .'><span class="rex-i-element-text">'. $I18N->msg('create_user') .'</span></a>';
  $list->addColumn($thIcon, $tdIcon, 0, array('<th class="rex-icon">###VALUE###</th>','<td class="rex-icon">###VALUE###</td>'));
  $list->setColumnParams($thIcon, array('user_id' => '###user_id###'));

  $list->setColumnLabel('user_id', 'ID');
  $list->setColumnLayout('user_id', array('<th class="rex-small">###VALUE###</th>','<td class="rex-small">###VALUE###</td>'));

  $list->setColumnLabel('name', $I18N->msg('name'));
  $list->setColumnParams('name', array('user_id' => '###user_id###'));
  $list->setColumnFormat('name', 'custom',
    create_function(
      '$params',
      '$list = $params["list"];
       return $list->getColumnLink("name", htmlspecialchars($list->getValue("name") != "" ? $list->getValue("name") : $list->getValue("login")));'
    )
  );

  $list->setColumnLabel('login', $I18N->msg('login'));

  $list->setColumnLabel('lasttrydate', $I18N->msg('last_login'));
  $list->setColumnFormat('lasttrydate', 'strftime', 'datetime');

  $list->addColumn('funcs', $I18N->msg('user_delete'));
  $list->setColumnLabel('funcs', $I18N->msg('user_functions'));
  $list->setColumnParams('funcs', array('FUNC_DELETE' => '1', 'user_id' => '###user_id###'));
  $list->setColumnFormat('funcs', 'custom',
    create_function(
      '$params',
      'global $REX;
       $list = $params["list"];
       if($list->getValue("user_id") == $REX["USER"]->getValue("user_id"))
       {
         return \'<span class="rex-strike">'. $I18N->msg('user_delete') .'</span>\';
       }
       return $list->getColumnLink("funcs","'. $I18N->msg('user_delete') .'");'
    )
  );
  $list->addLinkAttribute('funcs', 'onclick', 'return confirm(\''.$I18N->msg('delete').' ?\')');

  $list->show();
}