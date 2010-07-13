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


// Allgemeine Permissions setzen
$sel_all = new rex_select;
$sel_all->setMultiple(1);
$sel_all->setStyle('class="rex-form-select"');
$sel_all->setSize(10);
$sel_all->setName('userperm_all[]');
$sel_all->setId('userperm-all');
sort($REX['PERM']);
$sel_all->addArrayOptions($REX['PERM'],false);
$userperm_all = rex_request('userperm_all', 'array');


// Erweiterte Permissions setzen
$sel_ext = new rex_select;
$sel_ext->setMultiple(1);
$sel_ext->setStyle('class="rex-form-select"');
$sel_ext->setSize(10);
$sel_ext->setName('userperm_ext[]');
$sel_ext->setId('userperm-ext');
sort($REX['EXTPERM']);
$sel_ext->addArrayOptions($REX['EXTPERM'],false);
$userperm_ext = rex_request('userperm_ext', 'array');
$allcats = rex_request('allcats', 'int');


// zugriff auf categorien
$sel_cat = new rex_category_select(false, false, false, false);
$sel_cat->setMultiple(1);
$sel_cat->setStyle('class="rex-form-select"');
$sel_cat->setSize(20);
$sel_cat->setName('userperm_cat[]');
$sel_cat->setId('userperm-cat');

$userperm_cat = rex_request('userperm_cat', 'array');
$allmcats = rex_request('allmcats', 'int');
$userperm_cat_read = rex_request('userperm_cat_read', 'array');


// zugriff auf mediacategorien
$sel_media = new rex_mediacategory_select(false);
$sel_media->setMultiple(1);
$sel_media->setStyle('class="rex-form-select"');
$sel_media->setSize(20);
$sel_media->setName('userperm_media[]');
$sel_media->setId('userperm-media');

$userperm_media = rex_request('userperm_media', 'array');

// zugriff auf sprachen
$sel_sprachen = new rex_select;
$sel_sprachen->setMultiple(1);
$sel_sprachen->setStyle('class="rex-form-select"');
$sel_sprachen->setSize(3);
$sel_sprachen->setName('userperm_sprachen[]');
$sel_sprachen->setId('userperm-sprachen');
$sqlsprachen = rex_sql::factory();
$sqlsprachen->setQuery('select * from '.$REX['TABLE_PREFIX'].'clang order by id');
for ($i=0;$i<$sqlsprachen->getRows();$i++)
{
  $name = $sqlsprachen->getValue('name');
  $sel_sprachen->addOption($name,$sqlsprachen->getValue('id'));
  $sqlsprachen->next();
}
$userperm_sprachen = rex_request('userperm_sprachen', 'array');


// backend sprache
$sel_be_sprache = new rex_select;
$sel_be_sprache->setStyle('class="rex-form-select"');
$sel_be_sprache->setSize(1);
$sel_be_sprache->setName("userperm_be_sprache");
$sel_be_sprache->setId("userperm-mylang");
$sel_be_sprache->addOption("default","");
$cur_htmlcharset = $I18N->msg('htmlcharset');
$langpath = $REX['INCLUDE_PATH'].'/lang';
$langs = array();
if ($handle = opendir($langpath))
{
	while (false !== ($file = readdir($handle)))
  {
		if (substr($file,-5) == '.lang')
    {
			$locale = substr($file,0,strlen($file)-strlen(substr($file,-5)));
			$I18N_T = rex_create_lang($locale,$langpath,FALSE); // Locale nicht neu setzen
      $i_htmlcharset = $I18N_T->msg('htmlcharset');
      if ($cur_htmlcharset == $i_htmlcharset)
      {
      	$sel_be_sprache->addOption($I18N_T->msg('lang'),$locale);
      	$langs[$locale] = $I18N_T->msg('lang');
      }
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


// zugriff auf module
$sel_module = new rex_select;
$sel_module->setMultiple(1);
$sel_module->setStyle('class="rex-form-select"');
$sel_module->setSize(10);
$sel_module->setName('userperm_module[]');
$sel_module->setId('userperm-module');
$sqlmodule = rex_sql::factory();
$sqlmodule->setQuery('select * from '.$REX['TABLE_PREFIX'].'module order by name');
for ($i=0;$i<$sqlmodule->getRows();$i++)
{
  $sel_module->addOption($sqlmodule->getValue('name'),$sqlmodule->getValue('id'));
  $sqlmodule->next();
}
$userperm_module = rex_request('userperm_module', 'array');


// extrarechte - von den addons übergeben
$sel_extra = new rex_select;
$sel_extra->setMultiple(1);
$sel_extra->setStyle('class="rex-form-select"');
$sel_extra->setSize(10);
$sel_extra->setName('userperm_extra[]');
$sel_extra->setId('userperm-extra');
if (isset($REX['EXTRAPERM']))
{
  sort($REX['EXTRAPERM']);
  $sel_extra->addArrayOptions($REX['EXTRAPERM'], false);
}
$userperm_extra = rex_request('userperm_extra', 'array');


// --------------------------------- Title

rex_title($I18N->msg('title_user'),'');

// --------------------------------- FUNCTIONS
$FUNC_UPDATE = rex_request("FUNC_UPDATE","string");
$FUNC_APPLY = rex_request("FUNC_APPLY","string");
$FUNC_DELETE = rex_request("FUNC_DELETE","string");
$FUNC_ADD = rex_request("FUNC_ADD","string");
$save = rex_request("save","int");
$adminchecked = "";
$allcatschecked = "";
$allmcatschecked = "";



if ($FUNC_UPDATE != '' || $FUNC_APPLY != '')
{
  $loginReset = rex_request('logintriesreset', 'int');
  $userstatus = rex_request('userstatus', 'int');

  $updateuser = rex_sql::factory();
  $updateuser->setTable($REX['TABLE_PREFIX'].'user');
  $updateuser->setWhere('user_id='. $user_id);
  $updateuser->setValue('name',$username);
  $updateuser->addGlobalUpdateFields();
  if ($REX['PSWFUNC']!='' && $userpsw != $sql->getValue($REX['TABLE_PREFIX'].'user.psw')) $userpsw = call_user_func($REX['PSWFUNC'],$userpsw);
  $updateuser->setValue('psw',$userpsw);
  $updateuser->setValue('description',$userdesc);
  if ($loginReset == 1) $updateuser->setValue('login_tries','0');
  if ($userstatus == 1) $updateuser->setValue('status',1);
  else $updateuser->setValue('status',0);

  $perm = '';
  if ($useradmin == 1)
    $perm .= '#admin[]';

  if ($allcats == 1)
    $perm .= '#csw[0]';

  if ($allmcats == 1)
    $perm .= '#media[0]';

  // userperm_all
  foreach($userperm_all as $_perm)
    $perm .= '#'.$_perm;

  // userperm_ext
  foreach($userperm_ext as $_perm)
    $perm .= '#'.$_perm;

  // userperm_extra
  foreach($userperm_extra as $_perm)
    $perm .= '#'.$_perm;

  // userperm_cat
  foreach($userperm_cat as $ccat)
  {
    $gp = rex_sql::factory();
    $gp->setQuery("select * from ".$REX['TABLE_PREFIX']."article where id='$ccat' and clang=0");
    if ($gp->getRows()==1)
    {
      // Alle Eltern-Kategorien im Pfad bis zu ausgewählten, mit
      // Lesendem zugriff versehen, damit man an die aktuelle Kategorie drann kommt
      foreach (explode('|',$gp->getValue('path')) as $a)
        if ($a!='')$userperm_cat_read[$a] = $a;
    }
    $perm .= '#csw['. $ccat .']';
  }

  /*foreach($userperm_cat_read as $_perm)
    $perm .= '#csr['. $_perm .']';*/

  // userperm_media
  foreach($userperm_media as $_perm)
    $perm .= '#media['.$_perm.']';

  // userperm_sprachen
  foreach($userperm_sprachen as $_perm)
    $perm .= '#clang['.$_perm.']';

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

  // userperm_module
  foreach($userperm_module as $_perm)
    if($_perm != "") $perm .= '#module['.$_perm.']';

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
  // man kann sich selbst nicht löschen..
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

} elseif ($FUNC_ADD != '' and $save == '')
{
  // bei add default selected
  $sel_sprachen->setSelected("0");
} elseif ($FUNC_ADD != '' and $save == 1)
{
  $adduser = rex_sql::factory();
  $adduser->setQuery("SELECT * FROM ".$REX['TABLE_PREFIX']."user WHERE login = '$userlogin'");

  if ($adduser->getRows()==0 and $userlogin != '')
  {
    $adduser = rex_sql::factory();
    $adduser->setTable($REX['TABLE_PREFIX'].'user');
    $adduser->setValue('name',$username);
    if ($REX['PSWFUNC']!='') $userpsw = call_user_func($REX['PSWFUNC'],$userpsw);
    $adduser->setValue('psw',$userpsw);
    $adduser->setValue('login',$userlogin);
    $adduser->setValue('description',$userdesc);
    $adduser->addGlobalCreateFields();
    if (isset($userstatus) and $userstatus == 1) $adduser->setValue('status',1);
    else $adduser->setValue('status',0);

    $perm = '';
    if ($useradmin == 1) $perm .= '#'.'admin[]';
    if ($allcats == 1)     $perm .= '#'.'csw[0]';
    if ($allmcats == 1)   $perm .= '#'.'media[0]';

    // userperm_all
    foreach($userperm_all as $_perm)
      $perm .= '#'.$_perm;

    // userperm_ext
    foreach($userperm_ext as $_perm)
      $perm .= '#'.$_perm;

    // userperm_sprachen
    foreach($userperm_sprachen as $_perm)
      $perm .= '#clang['.$_perm.']';

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



    // userperm_extra
    foreach($userperm_extra as $_perm)
      $perm .= '#'.$_perm;

    // userperm_cat
    foreach($userperm_cat as $ccat)
    {
      $gp = rex_sql::factory();
      $gp->setQuery("select * from ".$REX['TABLE_PREFIX']."article where id='$ccat' and clang=0");
      if ($gp->getRows()==1)
      {
        // Alle Eltern-Kategorien im Pfad bis zu ausgewählten, mit
        // Lesendem zugriff versehen, damit man an die aktuelle Kategorie drann kommt
        foreach (explode('|',$gp->getValue('path')) as $a)
          if ($a!='')$userperm_cat_read[$a] = $a;
      }
      $perm .= '#csw['. $ccat .']';
    }
      
    /*foreach($userperm_cat_read as $_perm)
      $perm .= '#csr['. $_perm .']';*/

    // userperm_media
    foreach($userperm_media as $_perm)
      $perm .= '#media['.$_perm.']';

    // userperm_module
    foreach($userperm_module as $_perm)
      $perm .= '#module['.$_perm.']';

    $adduser->setValue('rights',$perm.'#');
    $adduser->insert();
    $user_id = 0;
    $FUNC_ADD = "";
    $info = $I18N->msg('user_added');
  } else
  {

    if ($useradmin == 1) $adminchecked = 'checked="checked"';
    if ($allcats == 1) $allcatschecked = 'checked="checked"';
    if ($allmcats == 1) $allmcatschecked = 'checked="checked"';


    // userperm_all
    foreach($userperm_all as $_perm)
      $sel_all->setSelected($_perm);

    // userperm_ext
    foreach($userperm_ext as $_perm)
      $sel_ext->setSelected($_perm);

    // userperm_extra
    foreach($userperm_extra as $_perm)
      $sel_extra->setSelected($_perm);

    // userperm_sprachen
    foreach($userperm_sprachen as $_perm)
      $sel_sprachen->setSelected($_perm);

		// userperm_be_sprache
    if ($userperm_be_sprache == '') $userperm_be_sprache = 'default';
    $sel_be_sprache->setSelected($userperm_be_sprache);

		// userperm_be_sprache
    if ($userperm_startpage == '') $userperm_startpage = 'default';
    $sel_startpage->setSelected($userperm_startpage);

    // userperm_cat
    foreach($userperm_cat as $_perm)
      $sel_cat->setSelected($_perm);
      
    // userperm_media
    foreach($userperm_media as $_perm)
      $sel_media->setSelected($_perm);

    // userperm_module
    foreach($userperm_module as $_perm)
      $sel_module->setSelected($_perm);

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

      if ($sql->hasPerm('csw[0]')) $allcatschecked = 'checked="checked"';
      else $allcatschecked = '';

      if ($sql->hasPerm('media[0]')) $allmcatschecked = 'checked="checked"';
      else $allmcatschecked = '';

      if ($sql->getValue($REX['TABLE_PREFIX'].'user.status') == 1) $statuschecked = 'checked="checked"';
      else $statuschecked = '';

      // Allgemeine Permissions setzen
      foreach($REX['PERM'] as $_perm)
        if ($sql->hasPerm($_perm)) $sel_all->setSelected($_perm);

      // optionen
      foreach($REX['EXTPERM'] as $_perm)
        if ($sql->hasPerm($_perm)) $sel_ext->setSelected($_perm);

      // extras
      if (isset($REX['EXTRAPERM']))
      {
        foreach($REX['EXTRAPERM'] as $_perm)
          if ($sql->hasPerm($_perm)) $sel_extra->setSelected($_perm);
      }

      // categories
      foreach ($sql->getPermAsArray('csw') as $cat_id)
        $sel_cat->setSelected($cat_id);
      
      // media categories
      foreach ($sql->getPermAsArray('media') as $cat_id)
        $sel_media->setSelected($cat_id);
        
      foreach ($sql->getPermAsArray('module') as $module_id)
        $sel_module->setSelected($module_id);

      foreach ($sql->getPermAsArray('clang') as $uclang_id)
        $sel_sprachen->setSelected($uclang_id);
        
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
  <form action="index.php" method="post">
    <fieldset class="rex-form-col-2">
      <legend>'.$form_label.'</legend>

      <div class="rex-form-wrapper">
	      <input type="hidden" name="page" value="user" />
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
            <label for="userperm-sprachen">'.$I18N->msg('user_lang_xs').'</label>
            '. $sel_sprachen->get() .'
            <span class="rex-form-notice">'. $I18N->msg('ctrl') .'</span>
          </p>
          <p class="rex-form-col-b rex-form-select">
            <label for="userperm-mylang">'.$I18N->msg('backend_language').'</label>
            '.$sel_be_sprache->get().'
          </p>
		    </div>

        <div class="rex-form-row">
          <p class="rex-form-col-a rex-form-select">
            <label for="userperm-startpage">'.$I18N->msg('startpage').'</label>
            '. $sel_startpage->get() .'
          </p>
		    </div>

        <div class="rex-form-row">
          <p class="rex-form-col-a rex-form-select">
            <label for="userperm-all">'.$I18N->msg('user_all').'</label>
            '. $sel_all->get() .'
            <span class="rex-form-notice">'. $I18N->msg('ctrl') .'</span>
          </p>
          <p class="rex-form-col-b rex-form-select">
            <label for="userperm-ext">'.$I18N->msg('user_options').'</label>
            '. $sel_ext->get() .'
            <span class="rex-form-notice">'. $I18N->msg('ctrl') .'</span>
          </p>
		    </div>

				<div class="rex-form-row" id="cats_mcats_box">
					<p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
						<input class="rex-form-checkbox" type="checkbox" id="allcats" name="allcats" value="1" '.$allcatschecked.' />
						<label for="allcats">'.$I18N->msg('all_categories').'</label>
					</p>
					<p class="rex-form-col-b rex-form-checkbox rex-form-label-right">
						<input class="rex-form-checkbox" type="checkbox" id="allmcats" name="allmcats" value="1" '.$allmcatschecked.' />
						<label for="allmcats">'.$I18N->msg('all_mediafolder').'</label>
					</p>
				</div>

				<div class="rex-form-row" id="cats_mcats_perms">
					<p class="rex-form-col-a rex-form-select">
						<label for="userperm-cat">'.$I18N->msg('categories').'</label>
						' .$sel_cat->get() .'
						<span class="rex-form-notice">'. $I18N->msg('ctrl') .'</span>
					</p>
					<p class="rex-form-col-b rex-form-select">
						<label for="userperm-media">'.$I18N->msg('mediafolder').'</label>
						'. $sel_media->get() .'
						<span class="rex-form-notice">'. $I18N->msg('ctrl') .'</span>
					</p>
				</div>

				
				<div class="rex-form-row">
          <p class="rex-form-col-a rex-form-select">
            <label for="userperm-module">'.$I18N->msg('modules').'</label>
            '.$sel_module->get().'
            <span class="rex-form-notice">'. $I18N->msg('ctrl') .'</span>
          </p>
          <p class="rex-form-col-b rex-form-select">
            <label for="userperm-extra">'.$I18N->msg('extras').'</label>
            '. $sel_extra->get() .'
            <span class="rex-form-notice">'. $I18N->msg('ctrl') .'</span>
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
    $("#useradmin").click(function() {
      if($(this).is(":checked"))
      {
        $("#userperm-module").attr("disabled", "disabled");
        $("#cats_mcats_perms").slideUp("slow");
        $("#cats_mcats_box").slideUp("slow");
        $("#userperm-extra").find("option[value=\'editContentOnly\[\]\']").attr("disabled", "disabled");
      }
      else
      {
        $("#userperm-module").attr("disabled", "");
        $("#cats_mcats_box").slideDown("slow");
        $("#userperm-extra").find("option[value=\'editContentOnly\[\]\']").attr("disabled", "");
        catsChecked();
      }
    });
    $("#allmcats").click(function() {
      catsChecked();
    });
    $("#allcats").click(function() {
      catsChecked();
    });
    function catsChecked(animate) {
      var c_checked = $("#allcats").is(":checked");
      var m_checked = $("#allmcats").is(":checked");
      animate = typeof(animate) == "undefined" ? true : animate;

      if(c_checked)
        $("#userperm-cat").attr("disabled", "disabled");
      else
        $("#userperm-cat").attr("disabled", "");

      if(m_checked)
        $("#userperm-media").attr("disabled", "disabled");
      else
        $("#userperm-media").attr("disabled", "");

      if(animate)
      {
        if(c_checked && m_checked)
          $("#cats_mcats_perms").slideUp("slow");
        else
          $("#cats_mcats_perms").slideDown("slow");
      }
      else
      {
        if(c_checked && m_checked)
          $("#cats_mcats_perms").hide();
        else
          $("#cats_mcats_perms").show();
      }
    };

    // init behaviour
    catsChecked(false);
    if($("#useradmin").is(":checked")) {
      $("#userperm-module").attr("disabled", "disabled");
      $("#cats_mcats_perms").hide();
      $("#cats_mcats_box").hide();
      $("#userperm-extra").find("option[value=\'editContentOnly\[\]\']").attr("disabled", "disabled");
    };
  });

  //--></script>';

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