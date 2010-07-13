<?php
/**
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$OUT = TRUE;

$function        = rex_request('function', 'string');
$function_action = rex_request('function_action', 'string');
$save            = rex_request('save','string');
$modul_id        = rex_request('modul_id', 'rex-module-id');
$action_id       = rex_request('action_id', 'rex-action-id');
$iaction_id      = rex_request('iaction_id','int'); // id der module-action relation
$mname           = rex_request('mname','string');
$eingabe         = rex_request('eingabe','string');
$ausgabe         = rex_request('ausgabe','string');
$goon            = rex_request('goon','string');
$add_action      = rex_request('add_action','string');

$info = '';
$warning = '';
$warning_block = '';

// ---------------------------- ACTIONSFUNKTIONEN FÜR MODULE
if ($add_action != "")
{
  $action = rex_sql::factory();
  $action->setTable($REX['TABLE_PREFIX'].'module_action');
  $action->setValue('module_id', $modul_id);
  $action->setValue('action_id', $action_id);

  if($action->insert())
  {
    $info = $I18N->msg('action_taken');
    $goon = '1';
  }
  else
  {
    $warning = $action->getErrro();
  }
}
elseif ($function_action == 'delete')
{
  $action = rex_sql::factory();
  $action->setTable($REX['TABLE_PREFIX'].'module_action');
  $action->setWhere('id='. $iaction_id . ' LIMIT 1');
  
  if($action->delete())
  {
     $info = $I18N->msg('action_deleted_from_modul') ;
  }
  else
  {
    $warning = $action->getErrro();
  }
}



// ---------------------------- FUNKTIONEN FÜR MODULE

if ($function == 'delete')
{
  $del = rex_sql::factory();
  $del->setQuery("SELECT ".$REX['TABLE_PREFIX']."article_slice.article_id, ".$REX['TABLE_PREFIX']."article_slice.clang, ".$REX['TABLE_PREFIX']."article_slice.ctype, ".$REX['TABLE_PREFIX']."module.name FROM ".$REX['TABLE_PREFIX']."article_slice
      LEFT JOIN ".$REX['TABLE_PREFIX']."module ON ".$REX['TABLE_PREFIX']."article_slice.modultyp_id=".$REX['TABLE_PREFIX']."module.id
      WHERE ".$REX['TABLE_PREFIX']."article_slice.modultyp_id='$modul_id' GROUP BY ".$REX['TABLE_PREFIX']."article_slice.article_id");

  if ($del->getRows() >0)
  {
    $module_in_use_message = '';
    $modulname = htmlspecialchars($del->getValue($REX['TABLE_PREFIX']."module.name"));
    for ($i=0; $i<$del->getRows(); $i++)
    {
      $aid = $del->getValue($REX['TABLE_PREFIX']."article_slice.article_id");
      $clang_id = $del->getValue($REX['TABLE_PREFIX']."article_slice.clang");
      $ctype = $del->getValue($REX['TABLE_PREFIX']."article_slice.ctype");
      $OOArt = OOArticle::getArticleById($aid, $clang_id);

      $label = $OOArt->getName() .' ['. $aid .']';
      if(count($REX['CLANG']) > 1)
        $label = '('. rex_translate($REX['CLANG'][$clang_id]) .') '. $label;

      $module_in_use_message .= '<li><a href="index.php?page=content&amp;article_id='. $aid .'&clang='. $clang_id .'&ctype='. $ctype .'">'. htmlspecialchars($label) .'</a></li>';
      $del->next();
    }

    if($module_in_use_message != '')
    {
      $warning_block = '<ul>' . $module_in_use_message . '</ul>';
    }

    $warning = $I18N->msg("module_cannot_be_deleted",$modulname);
  } else
  {
    $del->setQuery("DELETE FROM ".$REX['TABLE_PREFIX']."module WHERE id='$modul_id'");
    $del->setQuery("DELETE FROM ".$REX['TABLE_PREFIX']."module_action WHERE module_id='$modul_id'");

    $info = $I18N->msg("module_deleted");
  }
}

if ($function == 'add' or $function == 'edit')
{
  if ($save == '1')
  {
    $modultyp = rex_sql::factory();

    if ($function == 'add')
    {
      // $modultyp->setQuery("INSERT INTO ".$REX['TABLE_PREFIX']."modultyp (category_id, name, eingabe, ausgabe) VALUES ('$category_id', '$mname', '$eingabe', '$ausgabe')");

      $IMOD = rex_sql::factory();
      $IMOD->setTable($REX['TABLE_PREFIX'].'module');
      $IMOD->setValue('name',$mname);
      $IMOD->setValue('eingabe',$eingabe);
      $IMOD->setValue('ausgabe',$ausgabe);
      $IMOD->addGlobalCreateFields();

      if($IMOD->insert())
        $info = $I18N->msg('module_added');
      else
        $warning = $IMOD->getError();

    } else {
      $modultyp->setQuery('select * from '.$REX['TABLE_PREFIX'].'module where id='.$modul_id);
      if ($modultyp->getRows()==1)
      {
        $old_ausgabe = $modultyp->getValue('ausgabe');

        // $modultyp->setQuery("UPDATE ".$REX['TABLE_PREFIX']."modultyp SET name='$mname', eingabe='$eingabe', ausgabe='$ausgabe' WHERE id='$modul_id'");

        $UMOD = rex_sql::factory();
        $UMOD->setTable($REX['TABLE_PREFIX'].'module');
        $UMOD->setWhere('id='. $modul_id);
        $UMOD->setValue('name',$mname);
        $UMOD->setValue('eingabe',$eingabe);
        $UMOD->setValue('ausgabe',$ausgabe);
        $UMOD->addGlobalUpdateFields();

        if($UMOD->update())
          $info = $I18N->msg('module_updated').' | '.$I18N->msg('articel_updated');
        else
          $warning = $UMOD->getError();

        $new_ausgabe = stripslashes($ausgabe);

		if ($old_ausgabe != $new_ausgabe)
		{
          // article updaten - nur wenn ausgabe sich veraendert hat
          $gc = rex_sql::factory();
          $gc->setQuery("SELECT DISTINCT(".$REX['TABLE_PREFIX']."article.id) FROM ".$REX['TABLE_PREFIX']."article
              LEFT JOIN ".$REX['TABLE_PREFIX']."article_slice ON ".$REX['TABLE_PREFIX']."article.id=".$REX['TABLE_PREFIX']."article_slice.article_id
              WHERE ".$REX['TABLE_PREFIX']."article_slice.modultyp_id='$modul_id'");
          for ($i=0; $i<$gc->getRows(); $i++)
          {
          	rex_deleteCacheArticle($gc->getValue($REX['TABLE_PREFIX']."article.id"));
            $gc->next();
          }
        }
      }
    }

    if ($goon != '')
    {
      $save = '0';
    } else
    {
      $function = '';
    }
  }



  if ($save != '1')
  {
    if ($function == 'edit')
    {
      $legend = $I18N->msg('module_edit').' [ID='.$modul_id.']';

      $hole = rex_sql::factory();
      $hole->setQuery('SELECT * FROM '.$REX['TABLE_PREFIX'].'module WHERE id='.$modul_id);
      $category_id  = $hole->getValue('category_id');
      $mname    = $hole->getValue('name');
      $ausgabe  = $hole->getValue('ausgabe');
      $eingabe  = $hole->getValue('eingabe');
    }
    else
    {
      $legend = $I18N->msg('create_module');
    }

    $btn_update = '';
    if ($function != 'add') $btn_update = '<input type="submit" class="rex-form-submit rex-form-submit-2" name="goon" value="'.$I18N->msg("save_module_and_continue").'"'. rex_accesskey($I18N->msg('save_module_and_continue'), $REX['ACKEY']['APPLY']) .' />';

    if ($info != '')
      echo rex_info($info);

    if ($warning != '')
      echo rex_warning($warning);

    if ($warning_block != '')
      echo rex_warning_block($warning_block);

    echo '
			<div class="rex-form rex-form-module-editmode">
      	<form action="index.php" method="post">
        <fieldset class="rex-form-col-1">
          <legend>'. $legend .'</legend>
      	  <div class="rex-form-wrapper">
						<input type="hidden" name="page" value="module" />
						<input type="hidden" name="function" value="'.$function.'" />
						<input type="hidden" name="save" value="1" />
						<input type="hidden" name="category_id" value="0" />
						<input type="hidden" name="modul_id" value="'.$modul_id.'" />
						
						<div class="rex-form-row">
    			  	<p class="rex-form-col-a rex-form-text">
      					<label for="mname">'.$I18N->msg("module_name").'</label>
	      				<input class="rex-form-text" type="text" size="10" id="mname" name="mname" value="'.htmlspecialchars($mname).'" />
  	  			  </p>
    				</div>
						<div class="rex-form-row">
    				  <p class="rex-form-col-a rex-form-textarea">
      					<label for="eingabe">'.$I18N->msg("input").'</label>
      					<textarea class="rex-form-textarea" cols="50" rows="6" name="eingabe" id="eingabe">'.htmlspecialchars($eingabe).'</textarea>
    				  </p>
    				</div>
						<div class="rex-form-row">
    			  	<p class="rex-form-col-a rex-form-textarea">
      					<label for="ausgabe">'.$I18N->msg("output").'</label>
	      				<textarea class="rex-form-textarea" cols="50" rows="6" name="ausgabe" id="ausgabe">'.htmlspecialchars($ausgabe).'</textarea>
  	  			  </p>
    				</div>
              
            <div class="rex-clearer"></div>
    			</div>
        </fieldset>
        
				<fieldset class="rex-form-col-1">
      		<div class="rex-form-wrapper">
						<div class="rex-form-row">
    				  <p class="rex-form-col-a rex-form-submit">
      					<input class="rex-form-submit" type="submit" value="'.$I18N->msg("save_module_and_quit").'"'. rex_accesskey($I18N->msg('save_module_and_quit'), $REX['ACKEY']['SAVE']) .' />
        				'. $btn_update .'
      				</p>
    				</div>
    		  </div>
        </fieldset>
    ';

    if ($function == 'edit')
    {
      // Im Edit Mode Aktionen bearbeiten

      $gaa = rex_sql::factory();
      $gaa->setQuery("SELECT * FROM ".$REX['TABLE_PREFIX']."action ORDER BY name");

      if ($gaa->getRows()>0)
      {
        $gma = rex_sql::factory();
        $gma->setQuery("SELECT * FROM ".$REX['TABLE_PREFIX']."module_action, ".$REX['TABLE_PREFIX']."action WHERE ".$REX['TABLE_PREFIX']."module_action.action_id=".$REX['TABLE_PREFIX']."action.id and ".$REX['TABLE_PREFIX']."module_action.module_id='$modul_id'");
				
				$add_header = '';
				$add_col = '';
				if ($REX['USER']->hasPerm('advancedMode[]'))
				{
					$add_header = '<th class="rex-small">'.$I18N->msg('header_id').'</th>';
					$add_col = '<col width="40" />';
				}
				
        $actions = '';
        for ($i=0; $i<$gma->getRows(); $i++)
        {
          $iaction_id = $gma->getValue($REX['TABLE_PREFIX'].'module_action.id');
          $action_id = $gma->getValue($REX['TABLE_PREFIX'].'module_action.action_id');
          $action_edit_url = 'index.php?page=module&amp;subpage=actions&amp;action_id='.$action_id.'&amp;function=edit';
          $action_name = rex_translate($gma->getValue('name'));

          $actions .= '<tr>
          	<td class="rex-icon"><a class="rex-i-element rex-i-action" href="'. $action_edit_url .'"><span class="rex-i-element-text">' . htmlspecialchars($action_name) . '</span></a></td>';
          	
					if ($REX['USER']->hasPerm('advancedMode[]'))
					{
             $actions .= '<td class="rex-small">' . $gma->getValue("id") . '</td>';
          }
          	
          $actions .= '<td><a href="'. $action_edit_url .'">'. $action_name .'</a></td>
          	<td><a href="index.php?page=module&amp;modul_id='.$modul_id.'&amp;function_action=delete&amp;function=edit&amp;iaction_id='.$iaction_id.'" onclick="return confirm(\''.$I18N->msg('delete').' ?\')">'.$I18N->msg('action_delete').'</a></td>
          </tr>';

          $gma->next();
        }

        if($actions !='')
        {
          $actions = '
  					<table class="rex-table" summary="'.$I18N->msg('actions_added_summary').'">
  						<caption>'.$I18N->msg('actions_added_caption').'</caption>
    					<colgroup>
      				<col width="40" />
      				'.$add_col.'
      				<col width="*" />
      				<col width="153" />
    					</colgroup>
    					<thead>
      					<tr>
        					<th class="rex-icon">&nbsp;</th>
        					'.$add_header.'
        					<th>' . $I18N->msg('action_name') . '</th>
        					<th>' . $I18N->msg('action_functions') . '</th>
      					</tr>
    					</thead>
    				<tbody>
              '. $actions .'
            </tbody>
            </table>
          ';
        }

        $gaa_sel = new rex_select();
        $gaa_sel->setName('action_id');
        $gaa_sel->setId('action_id');
        $gaa_sel->setSize(1);
        $gaa_sel->setStyle('class="rex-form-select"');

        for ($i=0; $i<$gaa->getRows(); $i++)
        {
          $gaa_sel->addOption(rex_translate($gaa->getValue('name'), null, false),$gaa->getValue('id'));
          $gaa->next();
        }

        echo
        $actions .'
				<fieldset class="rex-form-col-1">
          <legend>'.$I18N->msg('action_add').'</legend>
      		<div class="rex-form-wrapper">
						
						<div class="rex-form-row">
							<p class="rex-form-col-a rex-form-select">
								<label for="action_id">'.$I18N->msg('action').'</label>
								'.$gaa_sel->get().'
					  	</p>
					  </div>
					  
						<div class="rex-form-row">
					  	<p class="rex-form-col-a rex-form-submit">
								<input class="rex-form-submit" type="submit" value="'.$I18N->msg('action_add').'" name="add_action" />
					  	</p>
					  </div>
				  </div>
        </fieldset>';
      }
    }

    echo '
    </form></div>
    ';

    $OUT = false;
  }
}

if ($OUT)
{
  if ($info != '')
    echo rex_info($info);

  if ($warning != '')
    echo rex_warning($warning);

  if ($warning_block != '')
    echo rex_warning_block($warning_block);

  $list = rex_list::factory('SELECT id, name FROM '.$REX['TABLE_PREFIX'].'module ORDER BY name');
  $list->setCaption($I18N->msg('module_caption'));
  $list->addTableAttribute('summary', $I18N->msg('module_summary'));
  $list->addTableColumnGroup(array(40, 40, '*', 153));

  $tdIcon = '<span class="rex-i-element rex-i-module"><span class="rex-i-element-text">###name###</span></span>';
  $thIcon = '<a class="rex-i-element rex-i-module-add" href="'. $list->getUrl(array('function' => 'add')) .'"'. rex_accesskey($I18N->msg('create_module'), $REX['ACKEY']['ADD']) .'><span class="rex-i-element-text">'.$I18N->msg('create_module').'</span></a>';
  $list->addColumn($thIcon, $tdIcon, 0, array('<th class="rex-icon">###VALUE###</th>','<td class="rex-icon">###VALUE###</td>'));
  $list->setColumnParams($thIcon, array('function' => 'edit', 'modul_id' => '###id###'));

  $list->setColumnLabel('id', 'ID');
  $list->setColumnLayout('id', array('<th class="rex-small">###VALUE###</th>','<td class="rex-small">###VALUE###</td>'));

  $list->setColumnLabel('name', $I18N->msg('module_description'));
  $list->setColumnParams('name', array('function' => 'edit', 'modul_id' => '###id###'));

  $list->addColumn($I18N->msg('module_functions'), $I18N->msg('delete_module'));
  $list->setColumnParams($I18N->msg('module_functions'), array('function' => 'delete', 'modul_id' => '###id###'));
  $list->addLinkAttribute($I18N->msg('module_functions'), 'onclick', 'return confirm(\''.$I18N->msg('delete').' ?\')');

	$list->setNoRowsMessage($I18N->msg('modules_not_found'));

  $list->show();
}