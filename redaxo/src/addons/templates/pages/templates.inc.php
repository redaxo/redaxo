<?php

/**
 *
 * @package redaxo5
 */

echo rex_view::title(rex_i18n::msg('title_templates'), '');

$OUT = TRUE;

$page         = rex_request('page', 'string');
$function     = rex_request('function', 'string');
$template_id  = rex_request('template_id', 'rex-template-id');
$save         = rex_request('save','string');
$goon         = rex_request('goon', 'string');

$info = '';
$warning = '';

if ($function == "delete")
{
  $del = rex_sql::factory();
  $del->setQuery("SELECT " . rex::getTablePrefix() . "article.id," . rex::getTablePrefix() . "template.name FROM " . rex::getTablePrefix() . "article
    LEFT JOIN " . rex::getTablePrefix() . "template ON " . rex::getTablePrefix() . "article.template_id=" . rex::getTablePrefix() . "template.id
    WHERE " . rex::getTablePrefix() . "article.template_id='$template_id' LIMIT 0,10");

  if ($del->getRows() > 0  || rex::getProperty('default_template_id') == $template_id)
  {
    $warning = rex_i18n::msg("cant_delete_template_because_its_in_use", 'ID = '.$template_id);

  }else
  {
    $del->setQuery("DELETE FROM " . rex::getTablePrefix() . "template WHERE id = '$template_id' LIMIT 1"); // max. ein Datensatz darf loeschbar sein
    rex_file::delete(rex_path::addonCache('templates', $template_id . '.template'));
    $info = rex_i18n::msg("template_deleted");
  }

}elseif ($function == "edit")
{

  $legend = rex_i18n::msg("edit_template") . ' [ID=' . $template_id . ']';

  $hole = rex_sql::factory();
  $hole->setQuery("SELECT * FROM " . rex::getTablePrefix() . "template WHERE id = '$template_id'");
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
  $legend = rex_i18n::msg("create_template");

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
    $TPL->setTable(rex::getTablePrefix() . "template");
    $TPL->setValue("name", $templatename);
    $TPL->setValue("active", $active);
    $TPL->setValue("content", $content);
    $TPL->addGlobalCreateFields();

    if ($function == "add")
    {
      $attributes = rex_setAttributes("ctype", $ctypes, "");
      $attributes = rex_setAttributes("modules", $modules, $attributes);
      $attributes = rex_setAttributes("categories", $categories, $attributes);
      $TPL->setValue("attributes", $attributes);
      $TPL->addGlobalCreateFields();

      try {
        $TPL->insert();
        $template_id = $TPL->getLastId();
        $info = rex_i18n::msg("template_added");
      } catch (rex_sql_exception $e) {
        $warning = $e->getMessage();
      }
    }else
    {
      $attributes = rex_setAttributes("ctype", $ctypes, $attributes);
      $attributes = rex_setAttributes("modules", $modules, $attributes);
      $attributes = rex_setAttributes("categories", $categories, $attributes);
      $TPL->setValue("attributes", $attributes);

      $TPL->setWhere(array('id' => $template_id));
      $TPL->addGlobalUpdateFields();

      try {
        $TPL->update();
        $info = rex_i18n::msg("template_updated");
      } catch (rex_sql_exception $e) {
        $warning = $e->getMessage();
      }
    }

    rex_dir::delete(rex_path::addonCache('templates'), false);

    if ($goon != "") {
      $function = "edit";
      $save = "nein";
    } else {
      $function = "";
    }
  }

  if (!isset ($save) or $save != "ja") {

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
    foreach($m_sql->getArray('SELECT id, name FROM '.rex::getTablePrefix().'module ORDER BY name') as $m)
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
    $ctypes[] = ""; // Extra, fuer Neue Spalte

    if (is_array($ctypes))
    {
      $formElements = array();

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


        $n = array();
        $n['label'] = '<label for="ctype'.$i.'">ID=' . $i . '</label>';
        $n['field'] = '<input id="ctype'.$i.'" type="text" name="ctype[' . $i . ']" value="' . htmlspecialchars($name) . '" />';
        $formElements[] = $n;


        $field = '';
        $field .= '<input id="allmodules'.$i.'" type="checkbox" name="modules[' . $i . '][all]" ';
        if(!isset($modules[$i]['all']) || $modules[$i]['all'] == 1)
          $field .= ' checked="checked" ';
        $field .= ' value="1" />';

        $n = array();
        $n['reverse'] = true;
        $n['label'] = '<label for="allmodules'.$i.'">'.rex_i18n::msg("modules_available_all").'</label>';
        $n['field'] = $field;
        $formElements[] = $n;

        $n = array();
        $n['id']    = 'p_modules'.$i;
        $n['label'] = '<label for="modules_'.$i.'_select">'.rex_i18n::msg("modules_available").'</label>';
        $n['field'] = $modul_select->get();
        $n['after'] = '<span class="rex-form-notice">'. rex_i18n::msg('ctrl') .'</span>';
        $formElements[] = $n;


        $i++;
      }

      $fragment = new rex_fragment();
      $fragment->setVar('elements', $formElements, false);
      $ctypes_out .= $fragment->parse('form.tpl');
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
      echo rex_view::info($info);

    if ($warning != '')
      echo rex_view::warning($warning);



    $content_1 = '';

    $content_1 .= '
      <div class="rex-form" id="rex-form-template">
        <form action="index.php" method="post">
          <fieldset>
            <h2>' . $legend . '</h2>

              <input type="hidden" name="page" value="'. $page .'" />
              <input type="hidden" name="function" value="' . $function . '" />
              <input type="hidden" name="save" value="ja" />
              <input type="hidden" name="template_id" value="' . $template_id . '" />';

      $formElements = array();

        $n = array();
        $n['label'] = '<label for="ltemplatename">' . rex_i18n::msg("template_name") . '</label>';
        $n['field'] = '<input type="text" id="ltemplatename" name="templatename" value="' . htmlspecialchars($templatename) . '" />';
        $formElements[] = $n;

        $n = array();
        $n['reverse'] = true;
        $n['label'] = '<label for="active">' . rex_i18n::msg("checkbox_template_active") . '<span>' . rex_i18n::msg("checkbox_template_active_info") . '</span></label>';
        $n['field'] = '<input type="checkbox" id="active" name="active" value="1"' . $tmpl_active_checked . '/>';
        $formElements[] = $n;

        $n = array();
        $n['label'] = '<label for="content">' . rex_i18n::msg("header_template") . '</label>';
        $n['field'] = '<textarea name="content" id="content" cols="50" rows="6">' . htmlspecialchars($content) . '</textarea>';
        $formElements[] = $n;

      $fragment = new rex_fragment();
      $fragment->setVar('elements', $formElements, false);
      $content_1 .= $fragment->parse('form.tpl');

    $content_1 .= '
        </fieldset>

        <!-- DIV nötig fuer JQuery slideIn -->
        <div id="rex-form-template-ctype">
        <fieldset>
          <h2>'.rex_i18n::msg("content_types").' [ctypes]</h2>
            ' . $ctypes_out . '
        </fieldset>
        </div>


         <div id="rex-form-template-categories">
          <fieldset>
             <h2>'.rex_i18n::msg("template_categories").'</h2>';


            $formElements = array();

              $field = '';
              $field .= '<input id="allcategories" type="checkbox" name="categories[all]" ';
              if(!isset($categories['all']) || $categories['all'] == 1)
                $field .= ' checked="checked" ';
              $field .= ' value="1" />';

              $n = array();
              $n['reverse'] = true;
              $n['label'] = '<label for="allcategories">'.rex_i18n::msg("template_categories_all").'</label>';
              $n['field'] = $field;
              $formElements[] = $n;

              $n = array();
              $n['id']    = 'p_categories';
              $n['label'] = '<label for="categories_select">'.rex_i18n::msg("template_categories_custom").'</label>';
              $n['field'] = $cat_select->get();
              $n['after'] = '<span class="rex-form-notice">'. rex_i18n::msg('ctrl') .'</span>';
              $formElements[] = $n;

            $fragment = new rex_fragment();
            $fragment->setVar('elements', $formElements, false);
            $content_1 .= $fragment->parse('form.tpl');

    $content_1 .= '
          </fieldset>
        </div>

        <fieldset class="rex-form-action">';

          $formElements = array();

            $n = array();
            $n['field'] = '<input type="submit" value="' . rex_i18n::msg("save_template_and_quit") . '"'. rex::getAccesskey(rex_i18n::msg('save_template_and_quit'), 'save') .' />';
            $formElements[] = $n;

            $n = array();
            $n['field'] = '<input type="submit" name="goon" value="' . rex_i18n::msg("save_template_and_continue") . '"'. rex::getAccesskey(rex_i18n::msg('save_template_and_continue'), 'apply') .' />';
            $formElements[] = $n;

          $fragment = new rex_fragment();
          $fragment->setVar('columns', 2, false);
          $fragment->setVar('elements', $formElements, false);
          $content_1 .= $fragment->parse('form.tpl');

    $content_1 .= '
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

    echo rex_view::contentBlock($content_1, '', 'block');

    $OUT = false;
  }
}

if ($OUT)
{
  if ($info != '')
    echo rex_view::info($info);

  if ($warning != '')
    echo rex_view::warning($warning);

  $list = rex_list::factory('SELECT id, name, active FROM '.rex::getTablePrefix().'template ORDER BY name');
  $list->setCaption(rex_i18n::msg('header_template_caption'));
  $list->addTableAttribute('summary', rex_i18n::msg('header_template_summary'));
  $list->addTableAttribute('id', 'rex-template');

  $tdIcon = '<span class="rex-ic-template">###name###</span>';
  $thIcon = '<a class="rex-ic-template rex-ic-add" href="'. $list->getUrl(array('function' => 'add')) .'"'. rex::getAccesskey(rex_i18n::msg('create_template'), 'add') .'>'.rex_i18n::msg('create_template').'</a>';
  $list->addColumn($thIcon, $tdIcon, 0, array('<th class="rex-icon">###VALUE###</th>','<td class="rex-icon">###VALUE###</td>'));
  $list->setColumnParams($thIcon, array('function' => 'edit', 'template_id' => '###id###'));

  $list->setColumnLabel('id', 'ID');
  $list->setColumnLayout('id',  array('<th class="rex-small">###VALUE###</th>','<td class="rex-small">###VALUE###</td>'));

  $list->setColumnLabel('name', rex_i18n::msg('header_template_description'));
  $list->setColumnLayout('name',  array('<th class="rex-name">###VALUE###</th>','<td class="rex-name">###VALUE###</td>'));
  $list->setColumnParams('name', array('function' => 'edit', 'template_id' => '###id###'));

  $list->setColumnLabel('active', rex_i18n::msg('header_template_active'));
  $list->setColumnLayout('active',  array('<th class="rex-small">###VALUE###</th>','<td class="rex-small">###VALUE###</td>'));
  $list->setColumnFormat('active', 'custom', function($params) {
    $list = $params['list'];
    return $list->getValue('active') == 1 ? rex_i18n::msg('yes') : rex_i18n::msg('no');
  });

  $list->addColumn(rex_i18n::msg('header_template_functions'), rex_i18n::msg('delete_template'));
  $list->setColumnLayout(rex_i18n::msg('header_template_functions'),  array('<th class="rex-function">###VALUE###</th>','<td class="rex-delete">###VALUE###</td>'));
  $list->setColumnParams(rex_i18n::msg('header_template_functions'), array('function' => 'delete', 'template_id' => '###id###'));
  $list->addLinkAttribute(rex_i18n::msg('header_template_functions'), 'onclick', 'return confirm(\''.rex_i18n::msg('delete').' ?\')');

  $list->setNoRowsMessage(rex_i18n::msg('templates_not_found'));

  $list->show();
}
