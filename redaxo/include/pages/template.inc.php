<?php

/**
 *
 * @package redaxo4
 * @version svn:$Id$
 */

rex_title($I18N->msg('title_templates'), '');

$OUT = TRUE;

$function     = rex_request('function', 'string');
$template_id  = rex_request('template_id', 'rex-template-id');
$save         = rex_request('save','string');
$goon         = rex_request('goon', 'string');

$info = '';
$warning = '';

if ($function == "delete")
{
  $del = rex_sql::factory();
  $del->setQuery("SELECT " . $REX['TABLE_PREFIX'] . "article.id," . $REX['TABLE_PREFIX'] . "template.name FROM " . $REX['TABLE_PREFIX'] . "article
    LEFT JOIN " . $REX['TABLE_PREFIX'] . "template ON " . $REX['TABLE_PREFIX'] . "article.template_id=" . $REX['TABLE_PREFIX'] . "template.id
    WHERE " . $REX['TABLE_PREFIX'] . "article.template_id='$template_id' LIMIT 0,10");

  if ($del->getRows() > 0  || $REX['DEFAULT_TEMPLATE_ID'] == $template_id) 
  {
    $warning = $I18N->msg("cant_delete_template_because_its_in_use", 'ID = '.$template_id);

  }else
  {
    $del->setQuery("DELETE FROM " . $REX['TABLE_PREFIX'] . "template WHERE id = '$template_id' LIMIT 1"); // max. ein Datensatz darf loeschbar sein
    rex_deleteDir($REX['INCLUDE_PATH'] . "/generated/templates/" . $template_id . ".template", 0);
    $info = $I18N->msg("template_deleted");
  }

}elseif ($function == "edit")
{

  $legend = $I18N->msg("edit_template") . ' [ID=' . $template_id . ']';

  $hole = rex_sql::factory();
  $hole->setQuery("SELECT * FROM " . $REX['TABLE_PREFIX'] . "template WHERE id = '$template_id'");
  if($hole->getRows() == 1)
  {
    $templatename = $hole->getValue("name");
    $content = $hole->getValue("content");
    $active = $hole->getValue("active");
    $attributes = $hole->getValue("attributes");

  }else
  {
    $function = '';
  }

}else
{
  $templatename = '';
  $content = '';
  $active = '';
  $template_id = '';
  $attributes = '';
  $legend = $I18N->msg("create_template");

}

if ($function == "add" or $function == "edit")
{
  if ($save == "ja")
  {
    $active = rex_post("active", "int");
    $templatename = rex_post("templatename", "string");
    $content = rex_post("content", "string");
    $ctypes = rex_post("ctype", "array");
    $num_ctypes = count($ctypes);
    if ($ctypes[$num_ctypes] == "")
    {
      unset ($ctypes[$num_ctypes]);
      if (isset ($ctypes[$num_ctypes -1]) && $ctypes[$num_ctypes -1] == '')
      {
        unset ($ctypes[$num_ctypes -1]);
      }
    }

    // Daten wieder in den Rohzustand versetzen, da für serialize()/unserialize()
    // keine Zeichen escaped werden dürfen
    for($i=1;$i<count($ctypes)+1;$i++)
    {
      $ctypes[$i] = stripslashes($ctypes[$i]);
    }

    $categories = rex_post("categories", "array");
    // leerer eintrag = 0
    if(count($categories) == 0 || !isset($categories["all"]) || $categories["all"] != 1)
    {
      $categories["all"] = 0;
    }
    
    $modules = rex_post("modules", "array");
    // leerer eintrag = 0
    if(count($modules) == 0)
    {
      $modules[1]["all"] = 0;
    }
    
    foreach($modules as $k => $module)
    {
      if(!isset($module["all"]) ||$module["all"] != 1)
      {
        $modules[$k]["all"] = 0;
      }
    }

    $TPL = rex_sql::factory();
    $TPL->setTable($REX['TABLE_PREFIX'] . "template");
    $TPL->setValue("name", $templatename);
    $TPL->setValue("active", $active);
    $TPL->setValue("content", $content);
    $attributes = rex_setAttributes("ctype", $ctypes, "");
    $attributes = rex_setAttributes("modules", $modules, "");
    $attributes = rex_setAttributes("categories", $categories, "");
    $TPL->setValue("attributes", addslashes($attributes));
    $TPL->addGlobalCreateFields();

    if ($function == "add") 
    {
      $attributes = rex_setAttributes("ctype", $ctypes, "");
      $attributes = rex_setAttributes("modules", $modules, $attributes);
      $attributes = rex_setAttributes("categories", $categories, $attributes);
      $TPL->setValue("attributes", addslashes($attributes));
      $TPL->addGlobalCreateFields();

      if($TPL->insert())
      {
        $template_id = $TPL->getLastId();
        $info = $I18N->msg("template_added");
      }else
      {
        $warning = $TPL->getError();
      }
    }else
    {
      $attributes = rex_setAttributes("ctype", $ctypes, $attributes);
      $attributes = rex_setAttributes("modules", $modules, $attributes);
      $attributes = rex_setAttributes("categories", $categories, $attributes);
      
      $TPL->setWhere("id='$template_id'");
      $TPL->setValue("attributes", addslashes($attributes));
      $TPL->addGlobalUpdateFields();

      if($TPL->update())
        $info = $I18N->msg("template_updated");
      else
        $warning = $TPL->getError();
    }
    // werte werden direkt wieder ausgegeben
    $templatename = stripslashes($templatename);
    $content = stripslashes($content);

    rex_deleteDir($REX['INCLUDE_PATH']."/generated/templates", 0);

    if ($goon != "") {
      $function = "edit";
      $save = "nein";
    } else {
      $function = "";
    }
  }

  if (!isset ($save) or $save != "ja") {
    echo '<a name="edit"></a>';

    // Ctype Handling
    $ctypes = rex_getAttributes("ctype", $attributes);
    $modules = rex_getAttributes("modules", $attributes);
    $categories = rex_getAttributes("categories", $attributes);
    
    if(!is_array($modules))
      $modules = array();

    if(!is_array($categories))
    	$categories = array();
      
    // modules[ctype_id][module_id];
    // modules[ctype_id]['all'];

    // Module ...
    $modul_select = new rex_select();
    $modul_select->setMultiple(TRUE);
    $modul_select->setStyle('class="rex-form-select"');
    $modul_select->setSize(10);
    $m_sql = rex_sql::factory();
    $m_sql->setQuery('SELECT id, name FROM '.$REX['TABLE_PREFIX'].'module ORDER BY name');
    foreach($m_sql->getArray() as $m)
      $modul_select->addOption($m["name"],$m["id"]);

    // Kategorien
		$cat_select = new rex_category_select(false, false, false, false);
		$cat_select->setMultiple(true);
		$cat_select->setStyle('class="rex-form-select"');
		$cat_select->setSize(10);
		$cat_select->setName('categories[]');
		$cat_select->setId('categories');
		
		if(count($categories)>0)
    {
      foreach($categories as $c => $cc)
      {
        // typsicherer vergleich, weil (0 != "all") => false 
        if($c !== "all")
        { 
          $cat_select->setSelected($cc);
        }
      }
    }
		
		
		
      
    $ctypes_out = '';
    $i = 1;
    $ctypes[] = ""; // Extra, fŸr Neue Spalte
    
    if (is_array($ctypes)) {
      foreach ($ctypes as $id => $name) 
      {
        $modul_select->setName('modules['.$i.'][]');
        $modul_select->setId('modules_'.$i.'_select');
        $modul_select->resetSelected();
        if(isset($modules[$i]) && count($modules[$i])>0)
        {
          foreach($modules[$i] as $j => $jj)
          {
            // typsicherer vergleich, weil (0 != "all") => false
            if($j !== 'all')
            { 
              $modul_select->setSelected($jj);
            }
          }
        }

        $ctypes_out .= '
        <div class="rex-form-row">
        <p class="rex-form-col-a rex-form-text">
          <label for="ctype'.$i.'">ID=' . $i . '</label> 
          <input class="rex-form-text" id="ctype'.$i.'" type="text" name="ctype[' . $i . ']" value="' . htmlspecialchars($name) . '" />
        </p>
        <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
          <input class="rex-form-checkbox" id="allmodules'.$i.'" type="checkbox" name="modules[' . $i . '][all]" ';
        if(!isset($modules[$i]['all']) || $modules[$i]['all'] == 1)
          $ctypes_out .= ' checked="checked" ';
        $ctypes_out .= ' value="1" />
          <label for="allmodules'.$i.'">'.$I18N->msg("modules_available_all").'</label> 
        </p>
        <p class="rex-form-col-a rex-form-select" id="p_modules'.$i.'">
          <label for="modules_'.$i.'_select">'.$I18N->msg("modules_available").'</label> 
          '.$modul_select->get().'
          <span class="rex-form-notice">'. $I18N->msg('ctrl') .'</span>
        </p>
        </div>';
        $i++;
      }
    }


    $ctypes_out .= '
      <script type="text/javascript">
      <!--
      jQuery(function($) {
    ';
    
    for($j=1;$j<=$i;$j++)
    {
      $ctypes_out .= '

        $("#allmodules'.$j.'").click(function() {
          $("#p_modules'.$j.'").slideToggle("slow");
        });
        
        if($("#allmodules'.$j.'").is(":checked")) {
          $("#p_modules'.$j.'").hide();
        }
      ';
    }

      $ctypes_out .= '
      });
      //--></script>';


    $tmpl_active_checked = $active == 1 ? ' checked="checked"' : '';

    if ($info != '')
      echo rex_info($info);

    if ($warning != '')
      echo rex_warning($warning);

    echo '
      <div class="rex-form rex-form-template-editmode">
        <form action="index.php" method="post">
          <fieldset class="rex-form-col-1">
            <legend>' . $legend . '</legend>

            <div class="rex-form-wrapper">
              <input type="hidden" name="page" value="template" />
              <input type="hidden" name="function" value="' . $function . '" />
              <input type="hidden" name="save" value="ja" />
              <input type="hidden" name="template_id" value="' . $template_id . '" />
              
              <div class="rex-form-row">
                <p class="rex-form-col-a rex-form-text">
                  <label for="ltemplatename">' . $I18N->msg("template_name") . '</label>
                  <input class="rex-form-text" type="text" size="10" id="ltemplatename" name="templatename" value="' . htmlspecialchars($templatename) . '" />
                </p>
              </div>
      
              <div class="rex-form-row">
                <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
                  <input class="rex-form-checkbox" type="checkbox" id="active" name="active" value="1"' . $tmpl_active_checked . '/>
                  <label for="active">' . $I18N->msg("checkbox_template_active") . '<span>' . $I18N->msg("checkbox_template_active_info") . '</span></label>
                </p>
              </div>

              
              
              <div class="rex-form-row">
                <p class="rex-form-col-a rex-form-textarea">
                  <label for="content">' . $I18N->msg("header_template") . '</label>
                  <textarea class="rex-form-textarea" name="content" id="content" cols="50" rows="6">' . htmlspecialchars($content) . '</textarea>
                </p>
              </div>
              
              <div class="rex-clearer"></div>
            </div>
        </fieldset>

        <!-- DIV nötig fuer JQuery slideIn -->
        <div id="rex-form-template-ctype">
        <fieldset class="rex-form-col-1">
          <legend>'.$I18N->msg("content_types").' [ctypes]</legend>

          <div class="rex-form-wrapper">
            ' . $ctypes_out . '
          </div>
        </fieldset>
        </div>

        
         <div id="rex-form-template-categories">
        	<fieldset class="rex-form-col-1">
   			    <legend>'.$I18N->msg("template_categories").'</legend>
              <div class="rex-form-wrapper">

              	<div class="rex-form-row">
              	<p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
				          <input class="rex-form-checkbox" id="allcategories" type="checkbox" name="categories[all]" ';
				        if(!isset($categories['all']) || $categories['all'] == 1)
				          echo ' checked="checked" ';
				        echo ' value="1" />
				          <label for="allcategories">'.$I18N->msg("template_categories_all").'</label> 
				        </p>
				        </div>

              	<div class="rex-form-row" id="p_categories">
		        		<p class="rex-form-col-a rex-form-select">
				          <label for="categories_select">'.$I18N->msg("template_categories_custom").'</label> 
				          '.$cat_select->get().'
				          <span class="rex-form-notice">'. $I18N->msg('ctrl') .'</span>
				        </p>
				      </div>
            </div>
        	</fieldset>
				</div>        
        
        <fieldset class="rex-form-col-1">
          <div class="rex-form-wrapper">
            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-submit">
                <input class="rex-form-submit" type="submit" value="' . $I18N->msg("save_template_and_quit") . '"'. rex_accesskey($I18N->msg('save_template_and_quit'), $REX['ACKEY']['SAVE']) .' />
                <input class="rex-form-submit rex-form-submit-2" type="submit" name="goon" value="' . $I18N->msg("save_template_and_continue") . '"'. rex_accesskey($I18N->msg('save_template_and_continue'), $REX['ACKEY']['APPLY']) .' />
              </p>
            </div>
          </div>
        </fieldset>

        </form>
      </div>

      <script type="text/javascript">
      <!--

      jQuery(function($) {

        $("#active").click(function() {
          $("#rex-form-template-ctype").slideToggle("slow");
          $("#rex-form-template-categories").slideToggle("slow");
        });
        
        if($("#active").is(":not(:checked)")) {
          $("#rex-form-template-ctype").hide();
          $("#rex-form-template-categories").hide();
  			}
        
        $("#allcategories").click(function() {
          $("#p_categories").slideToggle("slow");
        });

        if($("#allcategories").is(":checked")) {
          $("#p_categories").hide();
  			}
        
        
      });

      //--></script>';

    $OUT = false;
  }
}

if ($OUT)
{
  if ($info != '')
    echo rex_info($info);

  if ($warning != '')
    echo rex_warning($warning);

  $list = rex_list::factory('SELECT id, name, active FROM '.$REX['TABLE_PREFIX'].'template ORDER BY name');
  $list->setCaption($I18N->msg('header_template_caption'));
  $list->addTableAttribute('summary', $I18N->msg('header_template_summary'));
  $list->addTableColumnGroup(array(40, 40, '*', 153, 153));

  $tdIcon = '<span class="rex-i-element rex-i-template"><span class="rex-i-element-text">###name###</span></span>';
  $thIcon = '<a class="rex-i-element rex-i-template-add" href="'. $list->getUrl(array('function' => 'add')) .'"'. rex_accesskey($I18N->msg('create_template'), $REX['ACKEY']['ADD']) .'><span class="rex-i-element-text">'.$I18N->msg('create_template').'</span></a>';
  $list->addColumn($thIcon, $tdIcon, 0, array('<th class="rex-icon">###VALUE###</th>','<td class="rex-icon">###VALUE###</td>'));
  $list->setColumnParams($thIcon, array('function' => 'edit', 'template_id' => '###id###'));

  $list->setColumnLabel('id', 'ID');
  $list->setColumnLayout('id',  array('<th class="rex-small">###VALUE###</th>','<td class="rex-small">###VALUE###</td>'));

  $list->setColumnLabel('name', $I18N->msg('header_template_description'));
  $list->setColumnParams('name', array('function' => 'edit', 'template_id' => '###id###'));

  $list->setColumnLabel('active', $I18N->msg('header_template_active'));
  $list->setColumnFormat('active', 'custom', create_function('$params', 'global $I18N; $list = $params["list"]; return $list->getValue("active") == 1 ? $I18N->msg("yes") : $I18N->msg("no");'));

  $list->addColumn($I18N->msg('header_template_functions'), $I18N->msg('delete_template'));
  $list->setColumnParams($I18N->msg('header_template_functions'), array('function' => 'delete', 'template_id' => '###id###'));
  $list->addLinkAttribute($I18N->msg('header_template_functions'), 'onclick', 'return confirm(\''.$I18N->msg('delete').' ?\')');

  $list->setNoRowsMessage($I18N->msg('templates_not_found'));

  $list->show();
}