<?php

/**
 *
 * @package redaxo4
 * @version svn:$Id$
 */
 
$id = rex_request('id', 'int');
$info = '';
$warning = '';

if ($id != 0)
{
  $sql = rex_sql::factory();
  $sql->setQuery('SELECT * FROM '.$REX['TABLE_PREFIX'].'user_role WHERE id = '. $id .' LIMIT 2');
  if ($sql->getRows()!= 1) $id = 0;
}

// Allgemeine Infos
$username   = rex_request('username', 'string');
$userdesc   = rex_request('userdesc', 'string');

// Allgemeine Permissions setzen
$sel_all = new rex_select;
$sel_all->setMultiple(1);
$sel_all->setStyle('class="rex-form-select"');
$sel_all->setSize(10);
$sel_all->setName('userperm_all[]');
$sel_all->setId('userperm-all');
sort($REX['PERM']);
foreach($REX['PERM'] as $perm)
{
  $key = 'perm_general_'. $perm;
  $name = $I18N->hasMsg($key) ? $I18N->msg($key) : $perm;
  $sel_all->addOption($name, $perm);
}
$userperm_all = rex_request('userperm_all', 'array');


// Erweiterte Permissions setzen
$sel_ext = new rex_select;
$sel_ext->setMultiple(1);
$sel_ext->setStyle('class="rex-form-select"');
$sel_ext->setSize(10);
$sel_ext->setName('userperm_ext[]');
$sel_ext->setId('userperm-ext');
sort($REX['EXTPERM']);
foreach($REX['EXTPERM'] as $perm)
{
  $key = 'perm_options_'. $perm;
  $name = $I18N->hasMsg($key) ? $I18N->msg($key) : $perm;
  $sel_ext->addOption($name, $perm);
}
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
  foreach($REX['EXTRAPERM'] as $perm)
  {
    $key = 'perm_extras_'. $perm;
    $name = $I18N->hasMsg($key) ? $I18N->msg($key) : $perm;
    $sel_extra->addOption($name, $perm);
  }
}
$userperm_extra = rex_request('userperm_extra', 'array');


// --------------------------------- Title



// --------------------------------- FUNCTIONS
$FUNC_UPDATE = rex_request("FUNC_UPDATE","string");
$FUNC_APPLY = rex_request("FUNC_APPLY","string");
$FUNC_DELETE = rex_request("FUNC_DELETE","string");
$FUNC_ADD = rex_request("FUNC_ADD","string");
$save = rex_request("save","int");
$allcatschecked = "";
$allmcatschecked = "";



if ($FUNC_UPDATE != '' || $FUNC_APPLY != '')
{
  $updateuser = rex_sql::factory();
  $updateuser->setTable($REX['TABLE_PREFIX'].'user_role');
  $updateuser->setWhere('id='. $id);
  $updateuser->setValue('name',$username);
  $updateuser->addGlobalUpdateFields();
  $updateuser->setValue('description',$userdesc);

  $perm = '';

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

  // userperm_module
  foreach($userperm_module as $_perm)
    if($_perm != "") $perm .= '#module['.$_perm.']';

  $updateuser->setValue('rights',$perm.'#');
  $updateuser->update();

  if(isset($FUNC_UPDATE) && $FUNC_UPDATE != '')
  {
    $id = 0;
    $FUNC_UPDATE = "";
  }

  $info = $I18N->msg('user_role_data_updated');

} elseif ($FUNC_DELETE != '')
{
  $deleteuser = rex_sql::factory();
  $deleteuser->setQuery("DELETE FROM ".$REX['TABLE_PREFIX']."user_role WHERE id = '$id' LIMIT 1");
  $info = $I18N->msg("user_role_deleted");
  $id = 0;

} elseif ($FUNC_ADD != '' and $save == '')
{
  // bei add default selected
  $sel_sprachen->setSelected("0");
} elseif ($FUNC_ADD != '' and $save == 1)
{
  $adduser = rex_sql::factory();
  $adduser->setTable($REX['TABLE_PREFIX'].'user_role');
  $adduser->setValue('name',$username);
  $adduser->setValue('description',$userdesc);
  $adduser->addGlobalCreateFields();

  $perm = '';
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
  $id = 0;
  $FUNC_ADD = "";
  $info = $I18N->msg('user_role_added');
}


// ---------------------------------- ERR MSG

if ($info != '')
  echo rex_info($info);

if ($warning != '')
  echo rex_warning($warning);

// --------------------------------- FORMS

$SHOW = true;

if ($FUNC_ADD != "" || $id > 0)
{
  $SHOW = false;

  $add_login_reset_chkbox = '';

  if($id > 0)
  {
    // User Edit

    $form_label = $I18N->msg('edit_user_role');
    $add_hidden = '<input type="hidden" name="id" value="'.$id.'" />';
    $add_submit = '<div class="rex-form-row">
						<p class="rex-form-col-a"><input type="submit" class="rex-form-submit" name="FUNC_UPDATE" value="'.$I18N->msg('user_role_save').'" '. rex_accesskey($I18N->msg('user_role_save'), $REX['ACKEY']['SAVE']) .' /></p>
						<p class="rex-form-col-b"><input type="submit" class="rex-form-submit" name="FUNC_APPLY" value="'.$I18N->msg('user_role_apply').'" '. rex_accesskey($I18N->msg('user_role_apply'), $REX['ACKEY']['APPLY']) .' /></p>
					</div>';
		$add_user_class = ' rex-form-read';

    $sql = new rex_login_sql;
    $sql->setQuery('select * from '. $REX['TABLE_PREFIX'] .'user_role where id='. $id);

    if ($sql->getRows()==1)
    {
      // ----- EINLESEN DER PERMS

      if ($sql->hasPerm('csw[0]')) $allcatschecked = 'checked="checked"';
      else $allcatschecked = '';

      if ($sql->hasPerm('media[0]')) $allmcatschecked = 'checked="checked"';
      else $allmcatschecked = '';

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


      $username = $sql->getValue('name');
      $userdesc = $sql->getValue('description');

    }
  }
  else
  {
    // User Add
    $form_label = $I18N->msg('create_user_role');
    $add_hidden = '<input type="hidden" name="FUNC_ADD" value="1" />';
    $add_submit = '<div class="rex-form-row">
						<p class="rex-form-submit">
						<input type="submit" class="rex-form-submit" name="function" value="'.$I18N->msg("add_user_role").'" '. rex_accesskey($I18N->msg('add_user_role'), $REX['ACKEY']['SAVE']) .' />
						</p>
					</div>';
  }

  echo '
  <div class="rex-form" id="rex-form-user-editmode">
  <form action="index.php" method="post">
    <fieldset class="rex-form-col-2">
      <legend>'.$form_label.'</legend>

      <div class="rex-form-wrapper">
        <input type="hidden" name="page" value="users" />
        <input type="hidden" name="subpage" value="" />
      	<input type="hidden" name="save" value="1" />
      	'. $add_hidden .'

        <div class="rex-form-row">
          <p class="rex-form-col-a rex-form-text">
            <label for="username">'.$I18N->msg('name').'</label>
            <input type="text" class="rex-form-text" id="username" name="username" value="'.htmlspecialchars($username).'" />
          </p>
          <p class="rex-form-col-b rex-form-textarea">
            <label for="userdesc">'.$I18N->msg('description').'</label>
            <textarea id="userdesc" class="rex-form-textarea" cols="50" rows="4" name="userdesc">'.htmlspecialchars($userdesc).'</textarea>
          </p>
    		</div>

        <div class="rex-form-row">
          <p class="rex-form-col-a rex-form-select">
            <label for="userperm-sprachen">'.$I18N->msg('user_lang_xs').'</label>
            '. $sel_sprachen->get() .'
            <span class="rex-form-notice">'. $I18N->msg('ctrl') .'</span>
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
  });

  //--></script>';

}



// ---------------------------------- Role list

if (isset($SHOW) && $SHOW)
{
  $list = rex_list::factory('SELECT id, name FROM '.$REX['TABLE_PREFIX'].'user_role');
  $list->setCaption($I18N->msg('user_role_caption'));
  $list->addTableAttribute('summary', $I18N->msg('user_role_summary'));
  $list->addTableColumnGroup(array(40, '5%', '*', 153));

  $tdIcon = '<span class="rex-i-element rex-i-user"><span class="rex-i-element-text">###name###</span></span>';
  $thIcon = '<a class="rex-i-element rex-i-user-add" href="'. $list->getUrl(array('FUNC_ADD' => '1')) .'"'. rex_accesskey($I18N->msg('create_user_role'), $REX['ACKEY']['ADD']) .'><span class="rex-i-element-text">'. $I18N->msg('create_user_role') .'</span></a>';
  $list->addColumn($thIcon, $tdIcon, 0, array('<th class="rex-icon">###VALUE###</th>','<td class="rex-icon">###VALUE###</td>'));
  $list->setColumnParams($thIcon, array('id' => '###id###'));

  $list->setColumnLabel('id', 'ID');
  $list->setColumnLayout('id', array('<th class="rex-small">###VALUE###</th>','<td class="rex-small">###VALUE###</td>'));

  $list->setColumnLabel('name', $I18N->msg('name'));
  $list->setColumnParams('name', array('id' => '###id###'));

  $list->addColumn('funcs', $I18N->msg('user_role_delete'));
  $list->setColumnLabel('funcs', $I18N->msg('user_functions'));
  $list->setColumnParams('funcs', array('FUNC_DELETE' => '1', 'id' => '###id###'));
  $list->addLinkAttribute('funcs', 'onclick', 'return confirm(\''.$I18N->msg('delete').' ?\')');

  $list->show();
}