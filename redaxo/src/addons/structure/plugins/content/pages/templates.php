<?php

/**
 *
 * @package redaxo5
 */

echo rex_view::title(rex_i18n::msg('title_templates'));

$OUT = true;

$function     = rex_request('function', 'string');
$template_id  = rex_request('template_id', 'int');
$save         = rex_request('save', 'string');
$goon         = rex_request('goon', 'string');

$success = '';
$error = '';

$content = '';
$message = '';

if ($function == 'delete') {
    $del = rex_sql::factory();
    $del->setQuery('SELECT ' . rex::getTablePrefix() . 'article.id,' . rex::getTablePrefix() . 'template.name FROM ' . rex::getTablePrefix() . 'article
        LEFT JOIN ' . rex::getTablePrefix() . 'template ON ' . rex::getTablePrefix() . 'article.template_id=' . rex::getTablePrefix() . 'template.id
        WHERE ' . rex::getTablePrefix() . 'article.template_id="' . $template_id . '" LIMIT 0,10');

    if ($del->getRows() > 0  || rex::getProperty('default_template_id') == $template_id) {
        $error = rex_i18n::msg('cant_delete_template_because_its_in_use', rex_i18n::msg('id') . ' = ' . $template_id);

    } else {
        $del->setQuery('DELETE FROM ' . rex::getTablePrefix() . 'template WHERE id = "' . $template_id . '" LIMIT 1'); // max. ein Datensatz darf loeschbar sein
        rex_file::delete(rex_path::addonCache('templates', $template_id . '.template'));
        $success = rex_i18n::msg('template_deleted');
    }

} elseif ($function == 'edit') {

    $legend = rex_i18n::msg('edit_template') . ' <em class="rex-number">' . rex_i18n::msg('id') . ' = ' . $template_id . '</em>';

    $hole = rex_sql::factory();
    $hole->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'template WHERE id = "' . $template_id . '"');
    if ($hole->getRows() == 1) {
        $templatename = $hole->getValue('name');
        $template = $hole->getValue('content');
        $active = $hole->getValue('active');
        $attributes = $hole->getArrayValue('attributes');

    } else {
        $function = '';
    }

} else {
    $templatename = '';
    $template = '';
    $active = '';
    $template_id = '';
    $attributes = [];
    $legend = rex_i18n::msg('create_template');

}

if ($function == 'add' or $function == 'edit') {
    if ($save == 'ja') {
        $active = rex_post('active', 'int');
        $templatename = rex_post('templatename', 'string');
        $template = rex_post('content', 'string');
        $ctypes = rex_post('ctype', 'array');
        $num_ctypes = count($ctypes);
        if ($ctypes[$num_ctypes] == '') {
            unset ($ctypes[$num_ctypes]);
            if (isset ($ctypes[$num_ctypes - 1]) && $ctypes[$num_ctypes - 1] == '') {
                unset ($ctypes[$num_ctypes - 1]);
            }
        }

        $categories = rex_post('categories', 'array');
        // leerer eintrag = 0
        if (count($categories) == 0 || !isset($categories['all']) || $categories['all'] != 1) {
            $categories['all'] = 0;
        }

        $modules = rex_post('modules', 'array');
        // leerer eintrag = 0
        if (count($modules) == 0) {
            $modules[1]['all'] = 0;
        }

        foreach ($modules as $k => $module) {
            if (!isset($module['all']) || $module['all'] != 1) {
                $modules[$k]['all'] = 0;
            }
        }

        $TPL = rex_sql::factory();
        $TPL->setTable(rex::getTablePrefix() . 'template');
        $TPL->setValue('name', $templatename);
        $TPL->setValue('active', $active);
        $TPL->setValue('content', $template);
        $TPL->addGlobalCreateFields();

        $attributes['ctype'] = $ctypes;
        $attributes['modules'] = $modules;
        $attributes['categories'] = $categories;
        $TPL->setArrayValue('attributes', $attributes);

        if ($function == 'add') {
            $TPL->addGlobalCreateFields();

            try {
                $TPL->insert();
                $template_id = $TPL->getLastId();
                $success = rex_i18n::msg('template_added');
            } catch (rex_sql_exception $e) {
                $error = $e->getMessage();
            }
        } else {
            $TPL->setWhere(['id' => $template_id]);
            $TPL->addGlobalUpdateFields();

            try {
                $TPL->update();
                $success = rex_i18n::msg('template_updated');
            } catch (rex_sql_exception $e) {
                $error = $e->getMessage();
            }
        }

        rex_dir::delete(rex_path::addonCache('templates'), false);

        if ($goon != '') {
            $function = 'edit';
            $save = 'nein';
        } else {
            $function = '';
        }
    }

    if (!isset ($save) or $save != 'ja') {

        // Ctype Handling
        $ctypes = isset($attributes['ctype']) ? $attributes['ctype'] : [];
        $modules = isset($attributes['modules']) ? $attributes['modules'] : [];
        $categories = isset($attributes['categories']) ? $attributes['categories'] : [];

        if (!is_array($modules)) {
            $modules = [];
        }

        if (!is_array($categories)) {
            $categories = [];
        }

        // modules[ctype_id][module_id];
        // modules[ctype_id]['all'];

        // Module ...
        $modul_select = new rex_select();
        $modul_select->setMultiple(true);
        $modul_select->setSize(10);
        $m_sql = rex_sql::factory();
        foreach ($m_sql->getArray('SELECT id, name FROM ' . rex::getTablePrefix() . 'module ORDER BY name') as $m) {
            $modul_select->addOption($m['name'], $m['id']);
        }

        // Kategorien
        $cat_select = new rex_category_select(false, false, false, false);
        $cat_select->setMultiple(true);
        $cat_select->setSize(10);
        $cat_select->setName('categories[]');
        $cat_select->setId('rex-id-categories-select');

        if (count($categories) > 0) {
            foreach ($categories as $c => $cc) {
                // typsicherer vergleich, weil (0 != "all") => false
                if ($c !== 'all') {
                    $cat_select->setSelected($cc);
                }
            }
        }




        $ctypes_out = '';
        $i = 1;
        $ctypes[] = ''; // Extra, fuer Neue Spalte

        if (is_array($ctypes)) {

            foreach ($ctypes as $id => $name) {
                $modul_select->setName('modules[' . $i . '][]');
                $modul_select->setId('rex-id-modules-' . $i . '-select');
                $modul_select->resetSelected();
                if (isset($modules[$i]) && count($modules[$i]) > 0) {
                    foreach ($modules[$i] as $j => $jj) {
                        // typsicherer vergleich, weil (0 != "all") => false
                        if ($j !== 'all') {
                            $modul_select->setSelected($jj);
                        }
                    }
                }


                $formElements = [];
                $n = [];
                $n['label'] = '<label for="rex-id-ctype' . $i . '">' . rex_i18n::msg('id') . ' = ' . $i . '</label>';
                $n['field'] = '<input id="rex-id-ctype' . $i . '" type="text" name="ctype[' . $i . ']" value="' . htmlspecialchars($name) . '" />';
                $formElements[] = $n;

                $fragment = new rex_fragment();
                $fragment->setVar('flush', true);
                $fragment->setVar('elements', $formElements, false);
                $ctypes_out .= $fragment->parse('core/form/form.php');


                $field = '';
                $field .= '<input id="rex-js-allmodules' . $i . '" type="checkbox" name="modules[' . $i . '][all]" ';
                if (!isset($modules[$i]['all']) || $modules[$i]['all'] == 1) {
                    $field .= ' checked="checked" ';
                }
                $field .= ' value="1" />';

                $formElements = [];
                $n = [];
                $n['label'] = '<label>' . rex_i18n::msg('modules_available_all') . '</label>';
                $n['field'] = $field;
                $formElements[] = $n;

                $fragment = new rex_fragment();
                $fragment->setVar('flush', true);
                $fragment->setVar('elements', $formElements, false);
                $ctypes_out .= $fragment->parse('core/form/checkbox.php');


                $formElements = [];
                $n = [];
                $n['id']    = 'rex-js-modules' . $i;
                $n['label'] = '<label for="rex-id-modules-' . $i . '-select">' . rex_formatter::widont(rex_i18n::msg('modules_available')) . '</label>';
                $n['field'] = $modul_select->get();
                $n['note'] = rex_i18n::msg('ctrl');
                $formElements[] = $n;

                $fragment = new rex_fragment();
                $fragment->setVar('flush', true);
                $fragment->setVar('elements', $formElements, false);
                $ctypes_out .= $fragment->parse('core/form/form.php');


                $i++;
            }

        }


        $ctypes_out .= '
            <script type="text/javascript">
            <!--
            jQuery(function($) {
        ';

        for ($j = 1; $j <= $i; $j++) {
            $ctypes_out .= '

                $("#rex-js-allmodules' . $j . '").click(function() {
                    $("#rex-js-modules' . $j . '").slideToggle("slow");
                });

                if($("#rex-js-allmodules' . $j . '").is(":checked")) {
                    $("#rex-js-modules' . $j . '").hide();
                }
            ';
        }

            $ctypes_out .= '
            });
            //--></script>';


        $tmpl_active_checked = $active == 1 ? ' checked="checked"' : '';

        if ($success != '') {
            $message .= rex_view::success($success);
        }

        if ($error != '') {
            $message .= rex_view::error($error);
        }



        $content = '';

        $content .= '
            <div class="rex-form" id="rex-form-template">
                <form action="' . rex_url::currentBackendPage() . '" method="post">
                    <fieldset>
                        <h2>' . $legend . '</h2>

                            <input type="hidden" name="function" value="' . $function . '" />
                            <input type="hidden" name="save" value="ja" />
                            <input type="hidden" name="template_id" value="' . $template_id . '" />';

        $formElements = [];
        $n = [];
        $n['label'] = '<label for="rex-id-templatename">' . rex_i18n::msg('template_name') . '</label>';
        $n['field'] = '<input type="text" id="rex-id-templatename" name="templatename" value="' . htmlspecialchars($templatename) . '" />';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/form.php');


        $formElements = [];
        $n = [];
        $n['label'] = '<label>' . rex_i18n::msg('checkbox_template_active') . '</label>';
        $n['field'] = '<input type="checkbox" id="rex-js-active" name="active" value="1"' . $tmpl_active_checked . '/>';
        $n['note']  = rex_i18n::msg('checkbox_template_active_info');
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/checkbox.php');


        $formElements = [];
        $n = [];
        $n['label'] = '<label for="rex-id-content">' . rex_i18n::msg('header_template') . '</label>';
        $n['field'] = '<textarea class="rex-very-long rex-code" name="content" id="rex-id-content" spellcheck="false">' . htmlspecialchars($template) . '</textarea>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/form.php');

        $content .= '
                </fieldset>

                <!-- DIV noetig fuer JQuery slideIn -->
                <div id="rex-form-template-ctype">
                <fieldset>
                    <h2>' . rex_i18n::msg('content_types') . '</h2>
                        ' . $ctypes_out . '
                </fieldset>
                </div>


                 <div id="rex-form-template-categories">
                    <fieldset>
                         <h2>' . rex_i18n::msg('template_categories') . '</h2>';



        $field = '';
        $field .= '<input id="rex-js-allcategories" type="checkbox" name="categories[all]" ';
        if (!isset($categories['all']) || $categories['all'] == 1) {
            $field .= ' checked="checked" ';
        }
        $field .= ' value="1" />';

        $formElements = [];
        $n = [];
        $n['reverse'] = true;
        $n['label'] = '<label>' . rex_i18n::msg('template_categories_all') . '</label>';
        $n['field'] = $field;
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/checkbox.php');


        $formElements = [];
        $n = [];
        $n['id']    = 'rex-id-categories';
        $n['label'] = '<label for="rex-id-categories-select">' . rex_formatter::widont(rex_i18n::msg('template_categories_custom')) . '</label>';
        $n['field'] = $cat_select->get();
        $n['note'] = rex_i18n::msg('ctrl');
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/form.php');

        $content .= '
                    </fieldset>
                </div>';


        $formElements = [];

        $n = [];
        $n['field'] = '<a class="rex-back" href="' . rex_url::currentBackendPage() . '"><span class="rex-icon rex-icon-back"></span>' . rex_i18n::msg('form_abort') . '</a>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="rex-button" type="submit"' . rex::getAccesskey(rex_i18n::msg('save_template_and_quit'), 'save') . '>' . rex_i18n::msg('save_template_and_quit') . '</button>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="rex-button" type="submit" name="goon" value="1"' . rex::getAccesskey(rex_i18n::msg('save_template_and_continue'), 'apply') . '>' . rex_i18n::msg('save_template_and_continue') . '</button>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/submit.php');

        $content .= '

                </form>
            </div>

            <script type="text/javascript">
            <!--

            jQuery(function($) {

                $("#rex-js-active").click(function() {
                    $("#rex-form-template-ctype").slideToggle("slow");
                    $("#rex-form-template-categories").slideToggle("slow");
                });

                if($("#rex-js-active").is(":not(:checked)")) {
                    $("#rex-form-template-ctype").hide();
                    $("#rex-form-template-categories").hide();
                }

                $("#rex-js-allcategories").click(function() {
                    $("#rex-id-categories").slideToggle("slow");
                });

                if($("#rex-js-allcategories").is(":checked")) {
                    $("#rex-id-categories").hide();
                }


            });

            //--></script>';

        $OUT = false;
    }
}

if ($OUT) {
    if ($success != '') {
        $message .= rex_view::success($success);
    }

    if ($error != '') {
        $message .= rex_view::error($error);
    }

    $list = rex_list::factory('SELECT id, name, active FROM ' . rex::getTablePrefix() . 'template ORDER BY name');
    $list->setCaption(rex_i18n::msg('header_template_caption'));
    $list->addTableAttribute('id', 'rex-table-template');
    $list->addTableAttribute('class', 'rex-table-middle rex-table-striped');

    $tdIcon = '<span class="rex-icon rex-icon-template"></span>';
    $thIcon = '<a href="' . $list->getUrl(['function' => 'add']) . '"' . rex::getAccesskey(rex_i18n::msg('create_template'), 'add') . ' title="' . rex_i18n::msg('create_template') . '"><span class="rex-icon rex-icon-add-template"></span></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-slim">###VALUE###</th>', '<td class="rex-slim">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['function' => 'edit', 'template_id' => '###id###']);

    $list->setColumnLabel('id', rex_i18n::msg('id'));
    $list->setColumnLayout('id',  ['<th class="rex-id">###VALUE###</th>', '<td class="rex-id">###VALUE###</td>']);

    $list->setColumnLabel('name', rex_i18n::msg('header_template_description'));
    $list->setColumnLayout('name',  ['<th class="rex-name">###VALUE###</th>', '<td class="rex-name">###VALUE###</td>']);
    $list->setColumnParams('name', ['function' => 'edit', 'template_id' => '###id###']);

    $list->setColumnLabel('active', rex_i18n::msg('header_template_active'));
    $list->setColumnLayout('active',  ['<th>###VALUE###</th>', '<td>###VALUE###</td>']);
    $list->setColumnFormat('active', 'custom', function ($params) {
        $list = $params['list'];
        return $list->getValue('active') == 1 ? '<span class="rex-icon rex-icon-active-true"></span>' . rex_i18n::msg('yes') : '<span class="rex-icon rex-icon-active-false"></span>' . rex_i18n::msg('no');
    });

    $list->addColumn(rex_i18n::msg('header_template_functions'), rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('header_template_functions'),  ['<th class="rex-function" colspan="2">###VALUE###</th>', '<td class="rex-edit">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('header_template_functions'), ['function' => 'edit', 'template_id' => '###id###']);
    $list->addLinkAttribute(rex_i18n::msg('header_template_functions'), 'class', 'rex-edit');

    $list->addColumn(rex_i18n::msg('template_delete'), rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('template_delete'),  ['', '<td class="rex-delete">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('template_delete'), ['function' => 'delete', 'template_id' => '###id###']);
    $list->addLinkAttribute(rex_i18n::msg('template_delete'), 'data-confirm', rex_i18n::msg('confirm_delete_template'));
    $list->addLinkAttribute(rex_i18n::msg('template_delete'), 'class', 'rex-delete');

    $list->setNoRowsMessage(rex_i18n::msg('templates_not_found'));

    $content .= $list->get();
}

echo $message;
echo rex_view::contentBlock($content);
