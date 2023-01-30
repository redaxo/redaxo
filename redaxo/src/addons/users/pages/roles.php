<?php

$func = rex_request('func', 'string');
$id = rex_request('id', 'int');

$message = '';
$content = '';

if ('delete' == $func) {
    if (!rex_csrf_token::factory('user_role_delete')->isValid()) {
        $message = rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        $sql = rex_sql::factory();
        $sql->setQuery('DELETE FROM ' . rex::getTable('user_role') . ' WHERE id = ? LIMIT 1', [$id]);
        $message = rex_view::info(rex_i18n::msg('user_role_deleted'));
    }

    $func = '';
}

if ('' == $func) {
    $title = rex_i18n::msg('user_role_caption');

    $list = rex_list::factory('SELECT id, name FROM ' . rex::getTablePrefix() . 'user_role ORDER BY name', 100);
    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon rex-icon-userrole"></i>';
    $thIcon = '<a class="rex-link-expanded" href="' . $list->getUrl(['func' => 'add', 'default_value' => 1]) . '"' . rex::getAccesskey(rex_i18n::msg('create_user_role'), 'add') . ' title="' . rex_i18n::msg('create_user_role') . '"><i class="rex-icon rex-icon-add-userrole"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);

    $list->setColumnLabel('id', rex_i18n::msg('id'));
    $list->setColumnLayout('id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id">###VALUE###</td>']);

    $list->setColumnLabel('name', rex_i18n::msg('name'));
    $list->setColumnParams('name', ['func' => 'edit', 'id' => '###id###']);

    $list->addColumn('edit', '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLabel('edit', rex_i18n::msg('user_functions'));
    $list->setColumnLayout('edit', ['<th class="rex-table-action" colspan="3">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('edit', ['func' => 'edit', 'id' => '###id###']);

    $list->addColumn('duplicate', '<i class="rex-icon rex-icon-duplicate"></i> ' . rex_i18n::msg('user_role_duplicate'));
    $list->setColumnLabel('duplicate', rex_i18n::msg('user_functions'));
    $list->setColumnLayout('duplicate', ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('duplicate', ['func' => 'duplicate', 'id' => '###id###']);

    $list->addColumn('funcs', '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('user_role_delete'));
    $list->setColumnLabel('funcs', rex_i18n::msg('user_functions'));
    $list->setColumnLayout('funcs', ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('funcs', ['func' => 'delete', 'id' => '###id###'] + rex_csrf_token::factory('user_role_delete')->getUrlParams());
    $list->addLinkAttribute('funcs', 'data-confirm', rex_i18n::msg('delete') . ' ?');

    $content .= $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', $title);
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');
} else {
    $title = 'edit' == $func ? rex_i18n::msg('edit_user_role') : rex_i18n::msg('add_user_role');

    $form = rex_form::factory(rex::getTablePrefix() . 'user_role', '', 'id = ' . $id);
    $form->addParam('id', $id);
    $form->setApplyUrl(rex_url::currentBackendPage());
    $form->setEditMode('edit' == $func);

    $field = $form->addTextField('name');
    $field->setLabel(rex_i18n::msg('name'));
    $field->getValidator()->add('notEmpty');

    $field = $form->addTextAreaField('description');
    $field->setLabel(rex_i18n::msg('description'));

    $fieldContainer = $form->addContainerField('perms');
    $fieldContainer->setMultiple(false);
    $group = 'all';
    $fieldContainer->setActive($group);

    // Check all page permissions and add them to rex_perm if not already registered
    $registerImplicitePagePermissions = static function ($pages) use (&$registerImplicitePagePermissions) {
        foreach ($pages as $page) {
            foreach ($page->getRequiredPermissions() as $perm) {
                // ignore admin perm and complex perms (with "/")
                if ($perm && !in_array($perm, ['isAdmin', 'admin', 'admin[]']) && !str_contains($perm, '/') && !rex_perm::has($perm)) {
                    rex_perm::register($perm);
                }
            }
            $registerImplicitePagePermissions($page->getSubpages());
        }
    };
    $registerImplicitePagePermissions(rex_be_controller::getPages());

    foreach ([rex_perm::GENERAL, rex_perm::OPTIONS, rex_perm::EXTRAS] as $permgroup) {
        /** @var rex_form_select_element $field */
        $field = $fieldContainer->addGroupedField($group, 'select', $permgroup);
        $field->setLabel(rex_i18n::msg('user_' . $permgroup));
        $select = $field->getSelect();
        $select->setMultiple(true);
        $perms = rex_perm::getAll($permgroup);
        asort($perms);
        $select->setSize(min(20, max(3, count($perms))));
        $select->addArrayOptions($perms);
    }

    rex_extension::register('REX_FORM_INPUT_CLASS', static function (rex_extension_point $ep) {
        return 'perm_select' == $ep->getParam('inputType') ? rex_form_perm_select_element::class : null;
    });

    $fieldIds = [];
    foreach (rex_complex_perm::getAll() as $key => $class) {
        $params = $class::getFieldParams();
        if (!empty($params)) {
            /** @var rex_form_perm_select_element $field */
            $field = $fieldContainer->addGroupedField($group, 'perm_select', $key);
            $field->setLabel($params['label']);
            $field->setCheckboxLabel($params['all_label']);
            $fieldIds[] = rex_escape($field->getAttribute('id'), 'js');
            if (rex_request('default_value', 'boolean')) {
                $field->setValue(rex_complex_perm::ALL);
            }
            if (isset($params['select'])) {
                $field->setSelect($params['select']);
            }
            $select = $field->getSelect();
            $select->setMultiple(true);
            if (isset($params['options'])) {
                $select->addArrayOptions($params['options']);
            }
            if (isset($params['sql_options'])) {
                $select->addSqlOptions($params['sql_options']);
            }
            $select->get();
            $select->setSize(min(20, max(3, $select->countOptions())));
        }
    }

    $content .= $form->get();

    if ($fieldIds) {
        $content .= '
            <script type="text/javascript" nonce="' . rex_response::getNonce() . '">
            <!--

            jQuery(function($) {

                function check_perm_field(field, duration) {
                    var id = field.attr("id").substr(0, field.attr("id").length - 4);
                    if(field.is(":checked"))
                        $("#"+id).closest(".rex-form-group").hide(duration).find(":input").prop("disabled", true);
                    else
                        $("#"+id).closest(".rex-form-group").show(duration).find(":input").prop("disabled", false);
                }
                $("#' . implode('-all, #', $fieldIds) . '-all").change(function(){
                    check_perm_field($(this), "slow");
                });

                $("#' . implode('-all, #', $fieldIds) . '-all").each(function(){
                    check_perm_field($(this), 0);
                });

            });

            //--></script>
        ';
    }

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $title);
    $fragment->setVar('body', $content, false);
    $content = $fragment->parse('core/page/section.php');
}

echo $message;
echo $content;
