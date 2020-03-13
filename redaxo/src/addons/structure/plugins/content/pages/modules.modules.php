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
$mname = trim(rex_request('mname', 'string'));
$mkey = trim(rex_request('mkey', 'string'));
$mkey = '' === $mkey ? null : $mkey;
$eingabe = rex_request('eingabe', 'string');
$ausgabe = rex_request('ausgabe', 'string');
$goon = rex_request('goon', 'string');
$add_action = rex_request('add_action', 'string');

$success = '';
$error = '';

$content = '';
$message = '';

$csrfToken = rex_csrf_token::factory('structure_content_module');

// ---------------------------- ACTIONSFUNKTIONEN FUER MODULE
if (('' != $add_action || 'delete' == $function_action) && !$csrfToken->isValid()) {
    $error = rex_i18n::msg('csrf_token_invalid');
} elseif ('' != $add_action) {
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
} elseif ('delete' == $function_action) {
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

if ('delete' == $function && !$csrfToken->isValid()) {
    $error = rex_i18n::msg('csrf_token_invalid');
} elseif ('delete' == $function) {
    $del = rex_sql::factory();
    $del->setQuery('
        SELECT slice.article_id, slice.clang_id, slice.ctype_id, module.name
        FROM ' . rex::getTable('article_slice') . ' slice
        LEFT JOIN ' . rex::getTable('module') . ' module ON slice.module_id=module.id
        WHERE slice.module_id=?
        GROUP BY slice.article_id, slice.clang_id
        ORDER BY slice.article_id, slice.clang_id
        LIMIT 20
    ', [$module_id]);

    if ($del->getRows() > 0) {
        $module_in_use_message = '';
        $modulname = $del->getValue('module.name');
        for ($i = 0; $i < $del->getRows(); ++$i) {
            $aid = $del->getValue('article_id');
            $clang_id = $del->getValue('clang_id');
            $ctype = $del->getValue('ctype_id');
            $OOArt = rex_article::get($aid, $clang_id);

            $label = $OOArt->getName() . ' [' . $aid . ']';
            if (rex_clang::count() > 1) {
                $label .= ' [' . rex_clang::get($clang_id)->getCode() . ']';
            }

            $module_in_use_message .= '<li><a href="' . rex_url::backendPage('content', ['article_id' => $aid, 'clang' => $clang_id, 'ctype' => $ctype]) . '">' . rex_escape($label) . '</a></li>';
            $del->next();
        }

        $error = rex_i18n::msg('module_cannot_be_deleted', $modulname);

        if ('' != $module_in_use_message) {
            $error .= '<ul>' . $module_in_use_message . '</ul>';
        }
    } else {
        $del->setQuery('DELETE FROM ' . rex::getTablePrefix() . 'module WHERE id=?', [$module_id]);

        if ($del->getRows() > 0) {
            $del->setQuery('DELETE FROM ' . rex::getTablePrefix() . 'module_action WHERE module_id=?', [$module_id]);
            $success = rex_i18n::msg('module_deleted');
            $success = rex_extension::registerPoint(new rex_extension_point('MODULE_DELETED', $success, [
                'id' => $module_id,
            ]));
        } else {
            $error = rex_i18n::msg('module_not_found');
        }
    }
}

if ('add' == $function || 'edit' == $function) {
    if ('1' == $save && !$csrfToken->isValid()) {
        $error = rex_i18n::msg('csrf_token_invalid');
        $save = '0';
    } elseif ('1' == $save) {
        $module = rex_sql::factory();

        try {
            if ('add' == $function) {
                $IMOD = rex_sql::factory();
                $IMOD->setTable(rex::getTablePrefix() . 'module');
                $IMOD->setValue('name', $mname);
                $IMOD->setValue('key', $mkey);
                $IMOD->setValue('input', $eingabe);
                $IMOD->setValue('output', $ausgabe);
                $IMOD->addGlobalCreateFields();

                $IMOD->insert();
                $success = rex_i18n::msg('module_added');
                $success = rex_extension::registerPoint(new rex_extension_point('MODULE_ADDED', $success, [
                    'id' => $IMOD->getLastId(),
                    'name' => $mname,
                    'key' => $mkey,
                    'input' => $eingabe,
                    'output' => $ausgabe,
                ]));
            } else {
                $module->setQuery('select * from ' . rex::getTablePrefix() . 'module where id=?', [$module_id]);
                if (1 == $module->getRows()) {
                    $old_ausgabe = $module->getValue('output');

                    $UMOD = rex_sql::factory();
                    $UMOD->setTable(rex::getTablePrefix() . 'module');
                    $UMOD->setWhere(['id' => $module_id]);
                    $UMOD->setValue('name', $mname);
                    $UMOD->setValue('key', $mkey);
                    $UMOD->setValue('input', $eingabe);
                    $UMOD->setValue('output', $ausgabe);
                    $UMOD->addGlobalUpdateFields();

                    $UMOD->update();
                    $success = rex_i18n::msg('module_updated') . ' | ' . rex_i18n::msg('articel_updated');
                    $success = rex_extension::registerPoint(new rex_extension_point('MODULE_UPDATED', $success, [
                        'id' => $module_id,
                        'name' => $mname,
                        'key' => $mkey,
                        'input' => $eingabe,
                        'output' => $ausgabe,
                    ]));

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
            if (rex_sql::ERROR_VIOLATE_UNIQUE_KEY === $e->getErrorCode()) {
                $error = rex_i18n::msg('module_key_exists');
                $save = '0';
            } else {
                $error = $e->getMessage();
            }
        }

        if ('' != $goon) {
            $save = '0';
        } else {
            $function = '';
        }
    }

    if ('1' != $save) {
        if ('edit' == $function) {
            $legend = rex_i18n::msg('module_edit') . ' <small class="rex-primary-id">' . rex_i18n::msg('id') . '=' . $module_id . '</small>';

            $hole = rex_sql::factory();
            $hole->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'module WHERE id=?', [$module_id]);
            $mname = $hole->getValue('name');
            $mkey = $hole->getValue('key');
            $ausgabe = $hole->getValue('output');
            $eingabe = $hole->getValue('input');
        } else {
            $legend = rex_i18n::msg('create_module');
        }

        $btn_update = '';
        if ('add' != $function) {
            $btn_update = '<button class="btn btn-apply" type="submit" name="goon" value="1"' . rex::getAccesskey(rex_i18n::msg('save_and_goon_tooltip'), 'apply') . '>' . rex_i18n::msg('save_module_and_continue') . '</button>';
        }

        if ('' != $success) {
            $message .= rex_view::success($success);
        }

        if ('' != $error) {
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
        $n['field'] = '<input class="form-control" id="mname" type="text" name="mname" value="' . rex_escape($mname) . '" />';
        $n['note'] = rex_i18n::msg('translatable');
        $formElements[] = $n;

        $n = [];
        $n['label'] = '<label for="mkey">' . rex_i18n::msg('module_key') . '</label>';
        $n['field'] = '<input class="form-control" id="mkey" type="text" name="mkey" value="' . rex_escape($mkey) . '" />';
        $n['note'] = rex_i18n::msg('module_key_notice');
        $formElements[] = $n;

        $n = [];
        $n['label'] = '<label for="minput">' . rex_i18n::msg('input') . '</label>';
        $n['field'] = '<textarea class="form-control rex-code rex-js-code" id="minput" name="eingabe" spellcheck="false">' . rex_escape($eingabe) . '</textarea>';
        $formElements[] = $n;

        $n = [];
        $n['label'] = '<label for="moutput">' . rex_i18n::msg('output') . '</label>';
        $n['field'] = '<textarea class="form-control rex-code rex-js-code" id="moutput" name="ausgabe" spellcheck="false">' . rex_escape($ausgabe) . '</textarea>';
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
        $n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit"' . rex::getAccesskey(rex_i18n::msg('save_and_close_tooltip'), 'save') . '>' . rex_i18n::msg('save_module_and_quit') . '</button>';
        $formElements[] = $n;

        if ('' != $btn_update) {
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

        if ('edit' == $function) {
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
                        <td class="rex-table-icon"><a href="' . $action_edit_url . '" title="' . rex_escape($action_name) . '"><i class="rex-icon rex-icon-action"></i></a></td>
                        <td class="rex-table-id" data-title="' . rex_i18n::msg('id') . '">' . $gma->getValue('id') . '</td>
                        <td data-title="' . rex_i18n::msg('action_name') . '"><a href="' . $action_edit_url . '">' . $action_name . '</a></td>
                        <td class="rex-table-action"><a href="' . $action_edit_url . '"><i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit') . '</a></td>
                        <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['module_id' => $module_id, 'function_action' => 'delete', 'function' => 'edit', 'iaction_id' => $iaction_id] + $csrfToken->getUrlParams()) . '" data-confirm="' . rex_i18n::msg('confirm_delete_action') . '"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete') . '</a></td>
                    </tr>';

                    $gma->next();
                }

                if ('' != $actions) {
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
                $gaa_sel->setAttribute('class', 'form-control selectpicker');

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
            <form action="' . rex_url::currentBackendPage(['start' => rex_request('start', 'int')]) . '" method="post">
            ' . $csrfToken->getHiddenField() . '
            ' . $content . '
            </form>';

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

    $list = rex_list::factory('SELECT id, `key`, name FROM ' . rex::getTablePrefix() . 'module ORDER BY name', 100);
    $list->addParam('start', rex_request('start', 'int'));
    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon rex-icon-module"></i>';
    $thIcon = '<a href="' . $list->getUrl(['function' => 'add']) . '"' . rex::getAccesskey(rex_i18n::msg('create_module'), 'add') . ' title="' . rex_i18n::msg('create_module') . '"><i class="rex-icon rex-icon-add-module"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['function' => 'edit', 'module_id' => '###id###']);

    $list->setColumnLabel('id', rex_i18n::msg('id'));
    $list->setColumnLayout('id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id" data-title="' . rex_i18n::msg('id') . '">###VALUE###</td>']);

    $list->setColumnLabel('key', rex_i18n::msg('module_key'));

    $list->setColumnLabel('name', rex_i18n::msg('module_description'));
    $list->setColumnParams('name', ['function' => 'edit', 'module_id' => '###id###']);
    $list->setColumnFormat('name', 'custom', static function ($params) {
        return $params['list']->getColumnLink('name', rex_i18n::translate($params['list']->getValue('name')));
    });

    $slices = rex_sql::factory()->getArray('SELECT `module_id` FROM '.rex::getTable('article_slice').' GROUP BY `module_id`');
    if (count($slices) > 0) {
        $usedIds = array_flip(array_map(static function ($slice) {
            return $slice['module_id'];
        }, $slices));

        $list->addColumn('use', '');
        $list->setColumnLabel('use', rex_i18n::msg('module_in_use'));
        $list->setColumnFormat('use', 'custom', static function ($params) use ($usedIds) {
            /** @var rex_list $list */
            $list = $params['list'];
            return isset($usedIds[$list->getValue('id')]) ? '<i class="rex-icon rex-icon-active-true"></i> ' . rex_i18n::msg('yes') : '<i class="rex-icon rex-icon-active-false"></i> ' . rex_i18n::msg('no');
        });
    }

    $list->addColumn(rex_i18n::msg('module_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('module_functions'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('module_functions'), ['function' => 'edit', 'module_id' => '###id###']);

    $list->addColumn(rex_i18n::msg('delete_module'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete_module'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete_module'), ['function' => 'delete', 'module_id' => '###id###'] + $csrfToken->getUrlParams());
    $list->addLinkAttribute(rex_i18n::msg('delete_module'), 'data-confirm', rex_i18n::msg('confirm_delete_module'));

    $list->setNoRowsMessage(rex_i18n::msg('modules_not_found'));

    $content .= $list->get();

    echo $message;

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('module_caption'), false);
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}
