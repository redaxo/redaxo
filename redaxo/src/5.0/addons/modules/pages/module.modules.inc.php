<?php
/**
 *
 * @package redaxo5
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
  $action->setTable(rex::getTablePrefix().'module_action');
  $action->setValue('module_id', $modul_id);
  $action->setValue('action_id', $action_id);

  if($action->insert())
  {
    $info = rex_i18n::msg('action_taken');
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
  $action->setTable(rex::getTablePrefix().'module_action');
  $action->setWhere('id='. $iaction_id . ' LIMIT 1');

  if($action->delete())
  {
     $info = rex_i18n::msg('action_deleted_from_modul') ;
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
  $del->setQuery("SELECT ".rex::getTablePrefix()."article_slice.article_id, ".rex::getTablePrefix()."article_slice.clang, ".rex::getTablePrefix()."article_slice.ctype, ".rex::getTablePrefix()."module.name FROM ".rex::getTablePrefix()."article_slice
      LEFT JOIN ".rex::getTablePrefix()."module ON ".rex::getTablePrefix()."article_slice.modultyp_id=".rex::getTablePrefix()."module.id
      WHERE ".rex::getTablePrefix()."article_slice.modultyp_id='$modul_id' GROUP BY ".rex::getTablePrefix()."article_slice.article_id");

  if ($del->getRows() >0)
  {
    $module_in_use_message = '';
    $modulname = htmlspecialchars($del->getValue(rex::getTablePrefix()."module.name"));
    for ($i=0; $i<$del->getRows(); $i++)
    {
      $aid = $del->getValue(rex::getTablePrefix()."article_slice.article_id");
      $clang_id = $del->getValue(rex::getTablePrefix()."article_slice.clang");
      $ctype = $del->getValue(rex::getTablePrefix()."article_slice.ctype");
      $OOArt = rex_ooArticle::getArticleById($aid, $clang_id);

      $label = $OOArt->getName() .' ['. $aid .']';
      if(rex_clang::count() > 1)
        $label = '('. rex_i18n::translate(rex_clang::getName($clang_id)) .') '. $label;

      $module_in_use_message .= '<li><a href="index.php?page=content&amp;article_id='. $aid .'&clang='. $clang_id .'&ctype='. $ctype .'">'. htmlspecialchars($label) .'</a></li>';
      $del->next();
    }

    if($module_in_use_message != '')
    {
      $warning_block = '<ul>' . $module_in_use_message . '</ul>';
    }

    $warning = rex_i18n::msg("module_cannot_be_deleted",$modulname);
  } else
  {
    $del->setQuery("DELETE FROM ".rex::getTablePrefix()."module WHERE id='$modul_id'");
    $del->setQuery("DELETE FROM ".rex::getTablePrefix()."module_action WHERE module_id='$modul_id'");

    $info = rex_i18n::msg("module_deleted");
  }
}

if ($function == 'add' or $function == 'edit')
{
  if ($save == '1')
  {
    $modultyp = rex_sql::factory();

    try {
      if ($function == 'add')
      {
        $IMOD = rex_sql::factory();
        $IMOD->setTable(rex::getTablePrefix().'module');
        $IMOD->setValue('name',$mname);
        $IMOD->setValue('input',$eingabe);
        $IMOD->setValue('output',$ausgabe);
        $IMOD->addGlobalCreateFields();
  
        $IMOD->insert();
        $info = rex_i18n::msg('module_added');
  
      } else {
        $modultyp->setQuery('select * from '.rex::getTablePrefix().'module where id='.$modul_id);
        if ($modultyp->getRows()==1)
        {
          $old_ausgabe = $modultyp->getValue('output');
  
          // $modultyp->setQuery("UPDATE ".rex::getTablePrefix()."modultyp SET name='$mname', eingabe='$eingabe', ausgabe='$ausgabe' WHERE id='$modul_id'");
  
          $UMOD = rex_sql::factory();
          $UMOD->setTable(rex::getTablePrefix().'module');
          $UMOD->setWhere('id='. $modul_id);
          $UMOD->setValue('name',$mname);
          $UMOD->setValue('input',$eingabe);
          $UMOD->setValue('output',$ausgabe);
          $UMOD->addGlobalUpdateFields();
  
          $UMOD->update();
          $info = rex_i18n::msg('module_updated').' | '.rex_i18n::msg('articel_updated');
  
          $new_ausgabe = $ausgabe;
  
      		if ($old_ausgabe != $new_ausgabe)
      		{
            // article updaten - nur wenn ausgabe sich veraendert hat
            $gc = rex_sql::factory();
            $gc->setQuery("SELECT DISTINCT(".rex::getTablePrefix()."article.id) FROM ".rex::getTablePrefix()."article
                LEFT JOIN ".rex::getTablePrefix()."article_slice ON ".rex::getTablePrefix()."article.id=".rex::getTablePrefix()."article_slice.article_id
                WHERE ".rex::getTablePrefix()."article_slice.modultyp_id='$modul_id'");
            for ($i=0; $i<$gc->getRows(); $i++)
            {
            	rex_article_cache::delete($gc->getValue(rex::getTablePrefix()."article.id"));
              $gc->next();
            }
          }
        }
      }
    } catch (rex_sql_exception $e) {
      $warning = $e->getMessage();
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
      $legend = rex_i18n::msg('module_edit').' [ID='.$modul_id.']';

      $hole = rex_sql::factory();
      $hole->setQuery('SELECT * FROM '.rex::getTablePrefix().'module WHERE id='.$modul_id);
      $category_id  = $hole->getValue('category_id');
      $mname    = $hole->getValue('name');
      $ausgabe  = $hole->getValue('output');
      $eingabe  = $hole->getValue('input');
    }
    else
    {
      $legend = rex_i18n::msg('create_module');
    }

    $btn_update = '';
    if ($function != 'add') $btn_update = '<input type="submit" class="rex-form-submit rex-form-submit-2" name="goon" value="'.rex_i18n::msg("save_module_and_continue").'"'. rex::getAccesskey(rex_i18n::msg('save_module_and_continue'), 'apply') .' />';

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
						<input type="hidden" name="page" value="modules" />
						<input type="hidden" name="function" value="'.$function.'" />
						<input type="hidden" name="save" value="1" />
						<input type="hidden" name="category_id" value="0" />
						<input type="hidden" name="modul_id" value="'.$modul_id.'" />

						<div class="rex-form-row">
    			  	<p class="rex-form-col-a rex-form-text">
      					<label for="mname">'.rex_i18n::msg("module_name").'</label>
	      				<input class="rex-form-text" type="text" size="10" id="mname" name="mname" value="'.htmlspecialchars($mname).'" />
  	  			  </p>
    				</div>
						<div class="rex-form-row">
    				  <p class="rex-form-col-a rex-form-textarea">
      					<label for="eingabe">'.rex_i18n::msg("input").'</label>
      					<textarea class="rex-form-textarea" cols="50" rows="6" name="eingabe" id="eingabe">'.htmlspecialchars($eingabe).'</textarea>
    				  </p>
    				</div>
						<div class="rex-form-row">
    			  	<p class="rex-form-col-a rex-form-textarea">
      					<label for="ausgabe">'.rex_i18n::msg("output").'</label>
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
      					<input class="rex-form-submit" type="submit" value="'.rex_i18n::msg("save_module_and_quit").'"'. rex::getAccesskey(rex_i18n::msg('save_module_and_quit'), 'save') .' />
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
      $gaa->setQuery("SELECT * FROM ".rex::getTablePrefix()."action ORDER BY name");

      if ($gaa->getRows()>0)
      {
        $gma = rex_sql::factory();
        $gma->setQuery("SELECT * FROM ".rex::getTablePrefix()."module_action, ".rex::getTablePrefix()."action WHERE ".rex::getTablePrefix()."module_action.action_id=".rex::getTablePrefix()."action.id and ".rex::getTablePrefix()."module_action.module_id='$modul_id'");

				$add_header = '';
				$add_col = '';
				if (rex::getUser()->hasPerm('advancedMode[]'))
				{
					$add_header = '<th class="rex-small">'.rex_i18n::msg('header_id').'</th>';
					$add_col = '<col width="40" />';
				}

        $actions = '';
        for ($i=0; $i<$gma->getRows(); $i++)
        {
          $iaction_id = $gma->getValue(rex::getTablePrefix().'module_action.id');
          $action_id = $gma->getValue(rex::getTablePrefix().'module_action.action_id');
          $action_edit_url = 'index.php?page=modules&amp;subpage=actions&amp;action_id='.$action_id.'&amp;function=edit';
          $action_name = rex_i18n::translate($gma->getValue('name'));

          $actions .= '<tr>
          	<td class="rex-icon"><a class="rex-i-element rex-i-action" href="'. $action_edit_url .'"><span class="rex-i-element-text">' . htmlspecialchars($action_name) . '</span></a></td>';

					if (rex::getUser()->hasPerm('advancedMode[]'))
					{
             $actions .= '<td class="rex-small">' . $gma->getValue("id") . '</td>';
          }

          $actions .= '<td><a href="'. $action_edit_url .'">'. $action_name .'</a></td>
          	<td><a href="index.php?page=modules&amp;modul_id='.$modul_id.'&amp;function_action=delete&amp;function=edit&amp;iaction_id='.$iaction_id.'" onclick="return confirm(\''.rex_i18n::msg('delete').' ?\')">'.rex_i18n::msg('action_delete').'</a></td>
          </tr>';

          $gma->next();
        }

        if($actions !='')
        {
          $actions = '
  					<table class="rex-table" summary="'.rex_i18n::msg('actions_added_summary').'">
  						<caption>'.rex_i18n::msg('actions_added_caption').'</caption>
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
        					<th>' . rex_i18n::msg('action_name') . '</th>
        					<th>' . rex_i18n::msg('action_functions') . '</th>
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
          $gaa_sel->addOption(rex_i18n::translate($gaa->getValue('name'), null, false),$gaa->getValue('id'));
          $gaa->next();
        }

        echo
        $actions .'
				<fieldset class="rex-form-col-1">
          <legend>'.rex_i18n::msg('action_add').'</legend>
      		<div class="rex-form-wrapper">

						<div class="rex-form-row">
							<p class="rex-form-col-a rex-form-select">
								<label for="action_id">'.rex_i18n::msg('action').'</label>
								'.$gaa_sel->get().'
					  	</p>
					  </div>

						<div class="rex-form-row">
					  	<p class="rex-form-col-a rex-form-submit">
								<input class="rex-form-submit" type="submit" value="'.rex_i18n::msg('action_add').'" name="add_action" />
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

  $list = rex_list::factory('SELECT id, name FROM '.rex::getTablePrefix().'module ORDER BY name');
  $list->setCaption(rex_i18n::msg('module_caption'));
  $list->addTableAttribute('summary', rex_i18n::msg('module_summary'));
  $list->addTableColumnGroup(array(40, 40, '*', 153));

  $tdIcon = '<span class="rex-i-element rex-i-module"><span class="rex-i-element-text">###name###</span></span>';
  $thIcon = '<a class="rex-i-element rex-i-module-add" href="'. $list->getUrl(array('function' => 'add')) .'"'. rex::getAccesskey(rex_i18n::msg('create_module'), 'add') .'><span class="rex-i-element-text">'.rex_i18n::msg('create_module').'</span></a>';
  $list->addColumn($thIcon, $tdIcon, 0, array('<th class="rex-icon">###VALUE###</th>','<td class="rex-icon">###VALUE###</td>'));
  $list->setColumnParams($thIcon, array('function' => 'edit', 'modul_id' => '###id###'));

  $list->setColumnLabel('id', 'ID');
  $list->setColumnLayout('id', array('<th class="rex-small">###VALUE###</th>','<td class="rex-small">###VALUE###</td>'));

  $list->setColumnLabel('name', rex_i18n::msg('module_description'));
  $list->setColumnParams('name', array('function' => 'edit', 'modul_id' => '###id###'));

  $list->addColumn(rex_i18n::msg('module_functions'), rex_i18n::msg('delete_module'));
  $list->setColumnParams(rex_i18n::msg('module_functions'), array('function' => 'delete', 'modul_id' => '###id###'));
  $list->addLinkAttribute(rex_i18n::msg('module_functions'), 'onclick', 'return confirm(\''.rex_i18n::msg('delete').' ?\')');

	$list->setNoRowsMessage(rex_i18n::msg('modules_not_found'));

  $list->show();
}