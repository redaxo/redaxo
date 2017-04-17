<?php

/**
 * @package redaxo5
 */

$OUT = true;

$function = rex_request('function', 'string');
$function_action = rex_request('function_action', 'string');
$save = rex_request('save', 'string');
$module_id = rex_request('module_id', 'int');
$action_id = rex_request('action_id', 'int');
$iaction_id = rex_request('iaction_id', 'int'); // id der module-action relation
$mname = rex_request('mname', 'string');
$eingabe = rex_request('eingabe', 'string');
$ausgabe = rex_request('ausgabe', 'string');
$goon = rex_request('goon', 'string');
$add_action = rex_request('add_action', 'string');

$success = '';
$error = '';

$content = '';
$message = '';

// ---------------------------- ACTIONSFUNKTIONEN FUER MODULE
if ($add_action != '') {
    $action = rex_sql::factory();
    $action->setTable(rex::getTablePrefix() . 'module_action');
    $action->setValue('module_id', $module_id);
    $action->setValue('action_id', $action_id);

    try {
        $action->insert();
        $success = rex_i18n::msg('action_taken');
        $goon = '1';
    } catch (rex_sql_exception $e) {
        $error = $action->getError();
    }
} elseif ($function_action == 'delete') {
    $action = rex_sql::factory();
    $action->setTable(rex::getTablePrefix() . 'module_action');
    $action->setWhere(['id' => $iaction_id]);

    if ($action->delete() && $action->getRows() > 0) {
        $success = rex_i18n::msg('action_deleted_from_modul');
    } else {
        $error = $action->getError();
    }
}

// ---------------------------- FUNKTIONEN FUER MODULE

if ($function == 'delete') {
    $del = rex_sql::factory();
    $del->setQuery('SELECT ' . rex::getTablePrefix() . 'article_slice.article_id, ' . rex::getTablePrefix() . 'article_slice.clang_id, ' . rex::getTablePrefix() . 'article_slice.ctype_id, ' . rex::getTablePrefix() . 'module.name FROM ' . rex::getTablePrefix() . 'article_slice
            LEFT JOIN ' . rex::getTablePrefix() . 'module ON ' . rex::getTablePrefix() . 'article_slice.module_id=' . rex::getTablePrefix() . 'module.id
            WHERE ' . rex::getTablePrefix() . 'article_slice.module_id=? GROUP BY ' . rex::getTablePrefix() . 'article_slice.article_id', [$module_id]);

    if ($del->getRows() > 0) {
        $module_in_use_message = '';
        $modulname = htmlspecialchars($del->getValue(rex::getTablePrefix() . 'module.name'));
        for ($i = 0; $i < $del->getRows(); ++$i) {
            $aid = $del->getValue(rex::getTablePrefix() . 'article_slice.article_id');
            $clang_id = $del->getValue(rex::getTablePrefix() . 'article_slice.clang_id');
            $ctype = $del->getValue(rex::getTablePrefix() . 'article_slice.ctype_id');
            $OOArt = rex_article::get($aid, $clang_id);

            $label = $OOArt->getName() . ' [' . $aid . ']';
            if (rex_clang::count() > 1) {
                $label = '(' . rex_i18n::translate(rex_clang::get($clang_id)->getName()) . ') ' . $label;
            }

            $module_in_use_message .= '<li><a href="' . rex_url::backendPage('content', ['article_id' => $aid, 'clang' => $clang_id, 'ctype' => $ctype]) . '">' . htmlspecialchars($label) . '</a></li>';
            $del->next();
        }

        $error = rex_i18n::msg('module_cannot_be_deleted', $modulname);

        if ($module_in_use_message != '') {
            $error .= '<ul>' . $module_in_use_message . '</ul>';
        }
    } else {
        $del->setQuery('DELETE FROM ' . rex::getTablePrefix() . 'module WHERE id=?', [$module_id]);

        if ($del->getRows() > 0) {
            $del->setQuery('DELETE FROM ' . rex::getTablePrefix() . 'module_action WHERE module_id=?', [$module_id]);
            $success = rex_i18n::msg('module_deleted');
        } else {
            $error = rex_i18n::msg('module_not_found');
        }
    }
}

if ($function == 'add' or $function == 'edit') {
    if ($save == '1') {
        $module = rex_sql::factory();

        try {
            if ($function == 'add') {
                $IMOD = rex_sql::factory();
                $IMOD->setTable(rex::getTablePrefix() . 'module');
                $IMOD->setValue('name', $mname);
                $IMOD->setValue('input', $eingabe);
                $IMOD->setValue('output', $ausgabe);
                $IMOD->addGlobalCreateFields();

                $IMOD->insert();
                $success = rex_i18n::msg('module_added');
            } else {
                $module->setQuery('select * from ' . rex::getTablePrefix() . 'module where id=?', [$module_id]);
                if ($module->getRows() == 1) {
                    $old_ausgabe = $module->getValue('output');

                    // $module->setQuery("UPDATE ".rex::getTablePrefix()."module SET name='$mname', eingabe='$eingabe', ausgabe='$ausgabe' WHERE id='$module_id'");

                    $UMOD = rex_sql::factory();
                    $UMOD->setTable(rex::getTablePrefix() . 'module');
                    $UMOD->setWhere(['id' => $module_id]);
                    $UMOD->setValue('name', $mname);
                    $UMOD->setValue('input', $eingabe);
                    $UMOD->setValue('output', $ausgabe);
                    $UMOD->addGlobalUpdateFields();

                    $UMOD->update();
                    $success = rex_i18n::msg('module_updated') . ' | ' . rex_i18n::msg('articel_updated');

                    $new_ausgabe = $ausgabe;

                    if ($old_ausgabe != $new_ausgabe) {
                        // article updaten - nur wenn ausgabe sich veraendert hat
                        $gc = rex_sql::factory();
                        $gc->setQuery('SELECT DISTINCT(' . rex::getTablePrefix() . 'article.id) FROM ' . rex::getTablePrefix() . 'article
                                LEFT JOIN ' . rex::getTablePrefix() . 'article_slice ON ' . rex::getTablePrefix() . 'article.id=' . rex::getTablePrefix() . 'article_slice.article_id
                                WHERE ' . rex::getTablePrefix() . 'article_slice.module_id=?', [$module_id]);
                        for ($i = 0; $i < $gc->getRows(); ++$i) {
                            rex_article_cache::delete($gc->getValue(rex::getTablePrefix() . 'article.id'));
                            $gc->next();
                        }
                    }
                }
            }
        } catch (rex_sql_exception $e) {
            $error = $e->getMessage();
        }

        if ($goon != '') {
            $save = '0';
        } else {
            $function = '';
        }
    }

    if ($save != '1') {
        if ($function == 'edit') {
            $legend = rex_i18n::msg('module_edit') . ' <small class="rex-primary-id">' . rex_i18n::msg('id') . '=' . $module_id . '</small>';

            $hole = rex_sql::factory();
            $hole->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'module WHERE id=?', [$module_id]);
            $mname = $hole->getValue('name');
            $ausgabe = $hole->getValue('output');
            $eingabe = $hole->getValue('input');
        } else {
            $legend = rex_i18n::msg('create_module');
        }

        $btn_update = '';
        if ($function != 'add') {
            $btn_update = '<button class="btn btn-apply" type="submit" name="goon" value="1"' . rex::getAccesskey(rex_i18n::msg('save_module_and_continue'), 'apply') . '>' . rex_i18n::msg('save_module_and_continue') . '</button>';
        }

        if ($success != '') {
            $message .= rex_view::success($success);
        }

        if ($error != '') {
            $message .= rex_view::error($error);
        }

        $echo = '';
        $content = '';
        $panel = '';
        $panel .= '
                <fieldset>
                        <input type="hidden" name="function" value="' . $function . '" />
                        <input type="hidden" name="save" value="1" />
                        <input type="hidden" name="category_id" value="0" />
                        <input type="hidden" name="module_id" value="' . $module_id . '" />';

        $formElements = [];

        $n = [];
        $n['label'] = '<label for="mname">' . rex_i18n::msg('module_name') . '</label>';
        $n['field'] = '<input class="form-control" id="mname" type="text" name="mname" value="' . htmlspecialchars($mname) . '" />';
        $formElements[] = $n;

        $n = [];
        $n['label'] = '<label for="minput">' . rex_i18n::msg('input') . '</label>';
        $n['field'] = '<textarea class="form-control rex-code" id="minput" name="eingabe">' . htmlspecialchars($eingabe) . '</textarea>';
        $formElements[] = $n;

        $n = [];
        $n['label'] = '<label for="moutput">' . rex_i18n::msg('output') . '</label>';
        $n['field'] = '<textarea class="form-control rex-code" id="moutput" name="ausgabe">' . htmlspecialchars($ausgabe) . '</textarea>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $formElements, false);
        $panel .= $fragment->parse('core/form/form.php');

        $panel .= '</fieldset>';

        $formElements = [];

        $n = [];
        $n['field'] = '<a class="btn btn-abort" href="' . rex_url::currentBackendPage() . '">' . rex_i18n::msg('form_abort') . '</a>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit"' . rex::getAccesskey(rex_i18n::msg('save_module_and_quit'), 'save') . '>' . rex_i18n::msg('save_module_and_quit') . '</button>';
        $formElements[] = $n;

        if ($btn_update != '') {
            $n = [];
            $n['field'] = $btn_update;
            $formElements[] = $n;
        }

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $buttons = $fragment->parse('core/form/submit.php');

        $fragment = new rex_fragment();
        $fragment->setVar('class', 'edit', false);
        $fragment->setVar('title', $legend, false);
        $fragment->setVar('body', $panel, false);
        $fragment->setVar('buttons', $buttons, false);
        $content .= $fragment->parse('core/page/section.php');

        if ($function == 'edit') {
            // Im Edit Mode Aktionen bearbeiten

            $gaa = rex_sql::factory();
            $gaa->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'action ORDER BY name');

            if ($gaa->getRows() > 0) {
                $gma = rex_sql::factory();
                $gma->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'module_action, ' . rex::getTablePrefix() . 'action WHERE ' . rex::getTablePrefix() . 'module_action.action_id=' . rex::getTablePrefix() . 'action.id and ' . rex::getTablePrefix() . 'module_action.module_id=?', [$module_id]);

                $actions = '';
                for ($i = 0; $i < $gma->getRows(); ++$i) {
                    $iaction_id = $gma->getValue(rex::getTablePrefix() . 'module_action.id');
                    $action_id = $gma->getValue(rex::getTablePrefix() . 'module_action.action_id');
                    $action_edit_url = rex_url::backendPage('modules/actions', ['action_id' => $action_id, 'function' => 'edit']);
                    $action_name = rex_i18n::translate($gma->getValue('name'));

                    $actions .= '<tr>
                        <td class="rex-table-icon"><a href="' . $action_edit_url . '" title="' . htmlspecialchars($action_name) . '"><i class="rex-icon rex-icon-action"></i></a></td>
                        <td class="rex-table-id" data-title="' . rex_i18n::msg('id') . '">' . $gma->getValue('id') . '</td>
                        <td data-title="' . rex_i18n::msg('action_name') . '"><a href="' . $action_edit_url . '">' . $action_name . '</a></td>
                        <td class="rex-table-action"><a href="' . $action_edit_url . '"><i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit') . '</a></td>
                        <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['module_id' => $module_id, 'function_action' => 'delete', 'function' => 'edit', 'iaction_id' => $iaction_id]) . '" data-confirm="' . rex_i18n::msg('confirm_delete_action') . '"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete') . '</a></td>
                    </tr>';

                    $gma->next();
                }

                if ($actions != '') {
                    $panel = '
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="rex-table-icon">&nbsp;</th>
                                    <th class="rex-table-id">' . rex_i18n::msg('id') . '</th>
                                    <th>' . rex_i18n::msg('action_name') . '</th>
                                    <th class="rex-table-action" colspan="2">' . rex_i18n::msg('action_functions') . '</th>
                                </tr>
                            </thead>
                        <tbody>
                            ' . $actions . '
                        </tbody>
                        </table>
                    ';

                    $fragment = new rex_fragment();
                    $fragment->setVar('title', rex_i18n::msg('actions_added_caption'), false);
                    $fragment->setVar('content', $panel, false);
                    $content .= $fragment->parse('core/page/section.php');
                }

                $gaa_sel = new rex_select();
                $gaa_sel->setName('action_id');
                $gaa_sel->setId('action_id');
                $gaa_sel->setSize(1);
                $gaa_sel->setAttribute('class', 'form-control');

                for ($i = 0; $i < $gaa->getRows(); ++$i) {
                    $gaa_sel->addOption(rex_i18n::translate($gaa->getValue('name'), false), $gaa->getValue('id'));
                    $gaa->next();
                }

                $panel = '';
                $panel .= '<fieldset>';

                $formElements = [];

                $n = [];
                $n['label'] = '<label for="action_id">' . rex_i18n::msg('action') . '</label>';
                $n['field'] = $gaa_sel->get();
                $formElements[] = $n;

                $fragment = new rex_fragment();
                $fragment->setVar('elements', $formElements, false);
                $panel .= $fragment->parse('core/form/form.php');

                $panel .= '</fieldset>';

                $formElements = [];

                $n = [];
                $n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" value="1" name="add_action">' . rex_i18n::msg('action_add') . '</button>';
                $formElements[] = $n;

                $fragment = new rex_fragment();
                $fragment->setVar('elements', $formElements, false);
                $buttons = $fragment->parse('core/form/submit.php');

                $fragment = new rex_fragment();
                $fragment->setVar('title', rex_i18n::msg('action_add'), false);
                $fragment->setVar('body', $panel, false);
                $fragment->setVar('buttons', $buttons, false);
                $content .= $fragment->parse('core/page/section.php');
            }
        }

        $content = '
            <form action="' . rex_url::currentBackendPage() . '" method="post">
            ' . $content . '
            </form>';

        echo $message;

        echo $content;

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

    $list = rex_list::factory('SELECT id, name FROM ' . rex::getTablePrefix() . 'module ORDER BY name');
    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon rex-icon-module"></i>';
    $thIcon = '<a href="' . $list->getUrl(['function' => 'add']) . '"' . rex::getAccesskey(rex_i18n::msg('create_module'), 'add') . ' title="' . rex_i18n::msg('create_module') . '"><i class="rex-icon rex-icon-add-module"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['function' => 'edit', 'module_id' => '###id###']);

    $list->setColumnLabel('id', rex_i18n::msg('id'));
    $list->setColumnLayout('id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id" data-title="' . rex_i18n::msg('id') . '">###VALUE###</td>']);

    $list->setColumnLabel('name', rex_i18n::msg('module_description'));
    $list->setColumnParams('name', ['function' => 'edit', 'module_id' => '###id###']);
    $list->setColumnFormat('name', 'custom', function ($params) {
        return $params['list']->getColumnLink('name', rex_i18n::translate($params['list']->getValue('name')));
    });

    $list->addColumn(rex_i18n::msg('module_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('module_functions'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('module_functions'), ['function' => 'edit', 'module_id' => '###id###']);

    $list->addColumn(rex_i18n::msg('delete_module'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete_module'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete_module'), ['function' => 'delete', 'module_id' => '###id###']);
    $list->addLinkAttribute(rex_i18n::msg('delete_module'), 'data-confirm', rex_i18n::msg('confirm_delete_module'));

    $list->setNoRowsMessage(rex_i18n::msg('modules_not_found'));
    
    rex_extension::registerPoint(new rex_extension_point('EXTEND_MODULE_FUNCTIONS', $list, [
        $function,
        $function_action,
        $save,
        $module_id,
        $action_id,
        $iaction_id,
        $mname,
        $eingabe,
        $ausgabe,
        $goon,
        $add_action,
    ]));

    $content .= $list->get();

    echo $message;

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('module_caption'), false);
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}
