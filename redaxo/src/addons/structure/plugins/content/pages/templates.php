<?php

/**
 * @package redaxo5
 */

echo rex_view::title(rex_i18n::msg('title_templates'));

$OUT = true;

$function = rex_request('function', 'string');
$template_id = rex_request('template_id', 'int');
$save = rex_request('save', 'string');
$goon = rex_request('goon', 'string');

$success = '';
$error = '';

$content = '';
$message = '';

$templatekey = null;
$templatename = '';
$template = '';
$active = '';
$attributes = [];

$csrfToken = rex_csrf_token::factory('structure_content_template');

if ('delete' == $function) {
    if (!$csrfToken->isValid()) {
        $error = rex_i18n::msg('csrf_token_invalid');
    } else {
        $del = rex_sql::factory();
        $del->setQuery('
            SELECT article.id, article.clang_id, template.name
            FROM ' . rex::getTable('article') . ' article
            LEFT JOIN ' . rex::getTable('template') . ' template ON article.template_id=template.id
            WHERE article.template_id=?
            LIMIT 20
        ', [$template_id]);

        if ($del->getRows() > 0 || rex_template::getDefaultId() == $template_id) {
            $template_in_use_message = '';
            $templatename = $del->getValue('template.name');
            while ($del->hasNext()) {
                $aid = $del->getValue('article.id');
                $clang_id = $del->getValue('article.clang_id');
                $OOArt = rex_article::get($aid, $clang_id);

                $label = $OOArt->getName() . ' [' . $aid . ']';
                if (rex_clang::count() > 1) {
                    $label .= ' [' . rex_clang::get($clang_id)->getCode() . ']';
                }

                $template_in_use_message .= '<li><a href="' . rex_url::backendPage('content', ['article_id' => $aid, 'clang' => $clang_id]) . '">' . rex_escape($label) . '</a></li>';
                $del->next();
            }

            if ('' != $template_in_use_message) {
                $error .= rex_i18n::msg('cant_delete_template_because_its_in_use', $templatename);
                $error .= '<ul>' . $template_in_use_message . '</ul>';
            }

            if (rex_template::getDefaultId() == $template_id) {
                if ('' == $templatename) {
                    $del->setQuery('SELECT name FROM '.rex::getTable('template'). ' WHERE id = '.$template_id);
                    $templatename = $del->getValue('name');
                }
                $error .= rex_i18n::msg('cant_delete_template_because_its_default_template', $templatename);
            }
        } else {
            $del->setQuery('DELETE FROM ' . rex::getTablePrefix() . 'template WHERE id = "' . $template_id . '" LIMIT 1'); // max. ein Datensatz darf loeschbar sein
            rex_file::delete(rex_path::addonCache('templates', $template_id . '.template'));
            $success = rex_i18n::msg('template_deleted');
            $success = rex_extension::registerPoint(new rex_extension_point('TEMPLATE_DELETED', $success, [
                'id' => $template_id,
            ]));
        }
    }
} elseif ('edit' == $function) {
    $hole = rex_sql::factory();
    $hole->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'template WHERE id = "' . $template_id . '"');
    if (1 == $hole->getRows()) {
        $templatekey = $hole->getValue('key');
        $templatename = $hole->getValue('name');
        $template = $hole->getValue('content');
        $active = $hole->getValue('active');
        $attributes = $hole->getArrayValue('attributes');
    } else {
        $function = '';
    }
} else {
    $template_id = '';
}

if ('add' == $function || 'edit' == $function) {
    if ('ja' == $save && !$csrfToken->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
        $save = 'nein';
    }

    if ('ja' == $save) {
        $active = rex_post('active', 'int');
        $templatename = rex_post('templatename', 'string');
        $template = rex_post('content', 'string');

        $templatekey = trim(rex_post('templatekey', 'string'));
        $templatekey = '' === $templatekey ? null : $templatekey;

        $ctypes = rex_post('ctype', 'array');

        $num_ctypes = count($ctypes);
        if ('' == $ctypes[$num_ctypes]) {
            unset($ctypes[$num_ctypes]);
            if (isset($ctypes[$num_ctypes - 1]) && '' == $ctypes[$num_ctypes - 1]) {
                unset($ctypes[$num_ctypes - 1]);
            }
        }

        $categories = rex_post('categories', 'array');
        // leerer eintrag = 0
        if (0 == count($categories) || !isset($categories['all']) || 1 != $categories['all']) {
            $categories['all'] = 0;
        }

        $modules = rex_post('modules', 'array');
        // leerer eintrag = 0
        if (0 == count($modules)) {
            $modules[1]['all'] = 0;
        }

        foreach ($modules as $k => $module) {
            if (!isset($module['all']) || 1 != $module['all']) {
                $modules[$k]['all'] = 0;
            }
        }

        $attributes['ctype'] = $ctypes;
        $attributes['modules'] = $modules;
        $attributes['categories'] = $categories;
        $TPL = rex_sql::factory();
        $TPL->setTable(rex::getTablePrefix() . 'template');
        $TPL->setValue('key', $templatekey);
        $TPL->setValue('name', $templatename);
        $TPL->setValue('active', $active);
        $TPL->setValue('content', $template);
        $TPL->addGlobalCreateFields();

        $TPL->setArrayValue('attributes', $attributes);

        if ('add' == $function) {
            $TPL->addGlobalCreateFields();

            try {
                $TPL->insert();
                $template_id = $TPL->getLastId();
                $success = rex_i18n::msg('template_added');
                $success = rex_extension::registerPoint(new rex_extension_point('TEMPLATE_ADDED', $success, [
                    'id' => $template_id,
                    'key' => $templatekey,
                    'name' => $templatename,
                    'content' => $template,
                    'active' => $active,
                    'ctype' => $ctypes,
                    'modules' => $modules,
                    'categories' => $categories,
                ]));
            } catch (rex_sql_exception $e) {
                if (rex_sql::ERROR_VIOLATE_UNIQUE_KEY == $e->getErrorCode()) {
                    $error = rex_i18n::msg('template_key_exists');
                    $save = 'nein';
                } else {
                    $error = $e->getMessage();
                }
            }
        } else {
            $TPL->setWhere(['id' => $template_id]);
            $TPL->addGlobalUpdateFields();

            try {
                $TPL->update();
                $success = rex_i18n::msg('template_updated');
                $success = rex_extension::registerPoint(new rex_extension_point('TEMPLATE_UPDATED', $success, [
                    'id' => $template_id,
                    'key' => $templatekey,
                    'name' => $templatename,
                    'content' => $template,
                    'active' => $active,
                    'ctype' => $ctypes,
                    'modules' => $modules,
                    'categories' => $categories,
                ]));
            } catch (rex_sql_exception $e) {
                if (rex_sql::ERROR_VIOLATE_UNIQUE_KEY == $e->getErrorCode()) {
                    $error = rex_i18n::msg('template_key_exists');
                    $save = 'nein';
                } else {
                    $error = $e->getMessage();
                }
            }
        }

        rex_dir::delete(rex_path::addonCache('templates'), false);

        if ('' != $goon) {
            $function = 'edit';
            $save = 'nein';
        } else {
            $function = '';
        }
    }

    if (!isset($save) || 'ja' != $save) {
        // Ctype Handling
        $ctypes = $attributes['ctype'] ?? [];
        $modules = $attributes['modules'] ?? [];
        $categories = $attributes['categories'] ?? [];

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
        $modul_select->setAttribute('class', 'form-control');
        $m_sql = rex_sql::factory();
        foreach ($m_sql->getArray('SELECT id, name FROM ' . rex::getTablePrefix() . 'module ORDER BY name') as $m) {
            $modul_select->addOption(rex_i18n::translate($m['name']), $m['id']);
        }

        // Kategorien
        $cat_select = new rex_category_select(false, false, false, false);
        $cat_select->setMultiple(true);
        $cat_select->setSize(10);
        $cat_select->setName('categories[]');
        $cat_select->setId('rex-id-categories-select');
        $cat_select->setAttribute('class', 'form-control');

        if (count($categories) > 0) {
            foreach ($categories as $c => $cc) {
                // typsicherer vergleich, weil (0 != "all") => false
                if ('all' !== $c) {
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
                        if ('all' !== $j) {
                            $modul_select->setSelected($jj);
                        }
                    }
                }

                $ctypes_out .= '<fieldset><legend><small>' . rex_i18n::msg('content_type') . '</small> ' . rex_i18n::msg('id') . '=' . $i . '</legend>';

                $formElements = [];
                $n = [];
                $n['label'] = '<label for="rex-id-ctype' . $i . '">' . rex_i18n::msg('name') . '</label>';
                $n['field'] = '<input class="form-control" id="rex-id-ctype' . $i . '" type="text" name="ctype[' . $i . ']" value="' . rex_escape($name) . '" />';
                $formElements[] = $n;

                $fragment = new rex_fragment();
                $fragment->setVar('flush', true);
                $fragment->setVar('elements', $formElements, false);
                $ctypes_out .= $fragment->parse('core/form/form.php');

                $field = '';
                $field .= '<input id="rex-js-allmodules' . $i . '" type="checkbox" name="modules[' . $i . '][all]" ';
                if (!isset($modules[$i]['all']) || 1 == $modules[$i]['all']) {
                    $field .= ' checked="checked" ';
                }
                $field .= ' value="1" />';

                $formElements = [];
                $n = [];
                $n['label'] = '<label>' . rex_i18n::msg('modules_available_all') . '</label>';
                $n['field'] = $field;
                $formElements[] = $n;

                $fragment = new rex_fragment();
                $fragment->setVar('elements', $formElements, false);
                $ctypes_out .= $fragment->parse('core/form/checkbox.php');

                $formElements = [];
                $n = [];
                $n['id'] = 'rex-js-modules' . $i;
                $n['label'] = '<label for="rex-id-modules-' . $i . '-select">' . rex_formatter::widont(rex_i18n::msg('modules_available')) . '</label>';
                $n['field'] = $modul_select->get();
                $n['note'] = rex_i18n::msg('ctrl');
                $formElements[] = $n;

                $fragment = new rex_fragment();
                $fragment->setVar('flush', true);
                $fragment->setVar('elements', $formElements, false);
                $ctypes_out .= $fragment->parse('core/form/form.php');

                $ctypes_out .= '</fieldset>';

                ++$i;
            }
        }

        $ctypes_out .= '
            <script type="text/javascript">
            <!--
            jQuery(function($) {
        ';

        for ($j = 1; $j <= $i; ++$j) {
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

        $tmpl_active_checked = 1 == $active ? ' checked="checked"' : '';

        if ('' != $success) {
            $message .= rex_view::success($success);
        }

        if ('' != $error) {
            $message .= rex_view::error($error);
        }

        $panel = '';

        $panel .= '
        <div class="tab-content">
            <div class="tab-pane fade" id="rex-form-template-default">
                    <fieldset>
                        <input type="hidden" name="function" value="' . $function . '" />
                        <input type="hidden" name="save" value="ja" />
                        <input type="hidden" name="template_id" value="' . $template_id . '" />
                        <input id="rex-js-form-template-tab" type="hidden" name="template_tab" value="" />';

        $formElements = [];
        $n = [];
        $n['label'] = '<label for="rex-id-templatename">' . rex_i18n::msg('template_name') . '</label>';
        $n['field'] = '<input class="form-control" id="rex-id-templatename" type="text" name="templatename" value="' . rex_escape($templatename) . '" />';
        $n['note'] = rex_i18n::msg('translatable');
        $formElements[] = $n;

        $n = [];
        $n['label'] = '<label for="rex-id-templatekey">' . rex_i18n::msg('template_key') . '</label>';
        $n['field'] = '<input class="form-control" id="rex-id-templatekey" type="text" name="templatekey" value="' . rex_escape($templatekey) . '" />';
        $n['note'] = rex_i18n::msg('template_key_notice');
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $panel .= $fragment->parse('core/form/form.php');

        $formElements = [];
        $n = [];
        $n['label'] = '<label>' . rex_i18n::msg('checkbox_template_active') . '</label>';
        $n['field'] = '<input type="checkbox" id="rex-js-active" name="active" value="1"' . $tmpl_active_checked . '/>';
        $n['note'] = rex_i18n::msg('checkbox_template_active_info');
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $panel .= $fragment->parse('core/form/checkbox.php');

        $formElements = [];
        $n = [];
        $n['label'] = '<label for="rex-id-content">' . rex_i18n::msg('header_template') . '</label>';
        $n['field'] = '<textarea class="form-control rex-code rex-js-code" id="rex-id-content" name="content" spellcheck="false">' . rex_escape($template) . '</textarea>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $panel .= $fragment->parse('core/form/form.php');

        $panel .= '
                </fieldset>
            </div>
            <div class="tab-pane fade" id="rex-form-template-ctype">
                ' . $ctypes_out . '
            </div>

            <div class="tab-pane fade" id="rex-form-template-categories">
                <fieldset>
                     <legend>' . rex_i18n::msg('template_categories') . '</legend>';

        $field = '';
        $field .= '<input id="rex-js-allcategories" type="checkbox" name="categories[all]" ';
        if (!isset($categories['all']) || 1 == $categories['all']) {
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
        $fragment->setVar('elements', $formElements, false);
        $panel .= $fragment->parse('core/form/checkbox.php');

        $formElements = [];
        $n = [];
        $n['id'] = 'rex-id-categories';
        $n['label'] = '<label for="rex-id-categories-select">' . rex_formatter::widont(rex_i18n::msg('template_categories_custom')) . '</label>';
        $n['field'] = $cat_select->get();
        $n['note'] = rex_i18n::msg('ctrl');
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $panel .= $fragment->parse('core/form/form.php');

        $panel .= '
                    </fieldset>
                </div>
            </div>';

        $formElements = [];

        $n = [];
        $n['field'] = '<a class="btn btn-abort" href="' . rex_url::currentBackendPage() . '">' . rex_i18n::msg('form_abort') . '</a>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit"' . rex::getAccesskey(rex_i18n::msg('save_and_close_tooltip'), 'save') . '>' . rex_i18n::msg('save_template_and_quit') . '</button>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="btn btn-apply" type="submit" name="goon" value="1"' . rex::getAccesskey(rex_i18n::msg('save_and_goon_tooltip'), 'apply') . '>' . rex_i18n::msg('save_template_and_continue') . '</button>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $buttons = $fragment->parse('core/form/submit.php');

        $activeTab = rex_request('template_tab', 'string', 'rex-form-template-default');
        $optionTabs = [
            'rex-form-template-default' => rex_i18n::msg('header_template'),
            'rex-form-template-ctype' => rex_i18n::msg('content_types'),
            'rex-form-template-categories' => rex_i18n::msg('template_categories'),
        ];
        $options = '<ul class="nav nav-tabs" id="rex-js-form-template-tabs">';
        foreach ($optionTabs as $optionTabId => $optionTabTitle) {
            $options .= '<li><a href="#' . $optionTabId . '" data-toggle="tab">' . $optionTabTitle . '</a></li>';
        }
        $options .= '</ul>';

        if ('edit' === $function) {
            $legend = rex_i18n::msg('edit_template') . ' <small class="rex-primary-id">' . rex_i18n::msg('id') . ' = ' . $template_id . '</small>';
        } else {
            $legend = rex_i18n::msg('create_template');
        }

        $fragment = new rex_fragment();
        $fragment->setVar('class', 'edit', false);
        $fragment->setVar('title', $legend, false);
        $fragment->setVar('options', $options, false);
        $fragment->setVar('body', $panel, false);
        $fragment->setVar('buttons', $buttons, false);
        $content = $fragment->parse('core/page/section.php');

        $content = '
            <form id="rex-form-template" action="' . rex_url::currentBackendPage(['start' => rex_request('start', 'int')]) . '" method="post">
                ' . $csrfToken->getHiddenField() . '
                ' . $content . '
            </form>

            <script type="text/javascript">
            <!--
            jQuery(function($) {
                // store the currently selected tab in the hidden input#rex-js-form-template-tab
                $("#rex-js-form-template-tabs > li > a").on("shown.bs.tab", function(e) {
                    var id = $(e.target).attr("href").substr(1);
                    $("#rex-js-form-template-tab").val(id);
                });
                $("#rex-js-form-template-tabs a[href=\"#' . $activeTab . '\"]").tab("show");

                $("#rex-js-active").click(function() {
                    $("#rex-js-form-template-tabs a[href=\"#rex-form-template-ctype\"]").toggle("slow");
                    $("#rex-js-form-template-tabs a[href=\"#rex-form-template-categories\"]").toggle("slow");
                });

                if($("#rex-js-active").is(":not(:checked)")) {
                    $("#rex-js-form-template-tabs a[href=\"#rex-form-template-ctype\"]").hide();
                    $("#rex-js-form-template-tabs a[href=\"#rex-form-template-categories\"]").hide();
                }

                $("#rex-js-allcategories").click(function() {
                    $("#rex-id-categories").slideToggle("slow");
                });

                if($("#rex-js-allcategories").is(":checked")) {
                    $("#rex-id-categories").hide();
                }

            });

            //--></script>';

        echo $message;
        echo $content;

        $OUT = false;
    }
}

if ($OUT) {
    if ('' != $success) {
        $message .= rex_view::success($success);
    }

    if ('' != $error) {
        $message .= rex_view::error($error);
    }

    $list = rex_list::factory('SELECT id, `key`, name, active FROM ' . rex::getTablePrefix() . 'template ORDER BY name', 100);
    $list->addParam('start', rex_request('start', 'int'));
    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon rex-icon-template"></i>';
    $thIcon = '<a href="' . $list->getUrl(['function' => 'add']) . '"' . rex::getAccesskey(rex_i18n::msg('create_template'), 'add') . ' title="' . rex_i18n::msg('create_template') . '"><i class="rex-icon rex-icon-add-template"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['function' => 'edit', 'template_id' => '###id###']);

    $list->setColumnLabel('id', rex_i18n::msg('id'));
    $list->setColumnLayout('id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id" data-title="' . rex_i18n::msg('id') . '">###VALUE###</td>']);

    $list->setColumnLabel('key', rex_i18n::msg('header_template_key'));

    $list->setColumnLabel('name', rex_i18n::msg('header_template_description'));
    $list->setColumnParams('name', ['function' => 'edit', 'template_id' => '###id###']);

    $list->setColumnLabel('active', rex_i18n::msg('header_template_active'));
    $list->setColumnFormat('active', 'custom', static function ($params) {
        $list = $params['list'];
        return 1 == $list->getValue('active') ? '<i class="rex-icon rex-icon-active-true"></i> ' . rex_i18n::msg('yes') : '<i class="rex-icon rex-icon-active-false"></i> ' . rex_i18n::msg('no');
    });

    $list->addColumn(rex_i18n::msg('header_template_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('header_template_functions'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('header_template_functions'), ['function' => 'edit', 'template_id' => '###id###']);

    $list->addColumn(rex_i18n::msg('delete_template'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete_template'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete_template'), ['function' => 'delete', 'template_id' => '###id###'] + $csrfToken->getUrlParams());
    $list->addLinkAttribute(rex_i18n::msg('delete_template'), 'data-confirm', rex_i18n::msg('confirm_delete_template'));

    $list->setNoRowsMessage(rex_i18n::msg('templates_not_found'));

    $content .= $list->get();

    echo $message;

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('header_template_caption'), false);
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}
