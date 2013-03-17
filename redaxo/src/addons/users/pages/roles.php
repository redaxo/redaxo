<?php

$content = '';

if ($func == 'delete') {
    $sql = rex_sql::factory();
    $sql->setQuery('DELETE FROM ' . rex::getTable('user_role') . ' WHERE id = ? LIMIT 1', [$id]);
    $content .= rex_view::info(rex_i18n::msg('user_role_deleted'));
    $func = '';
}

if ($func == '') {

    $list = rex_list::factory('SELECT id, name FROM ' . rex::getTablePrefix() . 'user_role');
    $list->setCaption(rex_i18n::msg('user_role_caption'));
    $list->addTableAttribute('summary', rex_i18n::msg('user_role_summary'));
    $list->addTableAttribute('class', 'rex-table-middle rex-table-striped');

    $tdIcon = '<span class="rex-icon rex-icon-userrole"></span>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add', 'default_value' => 1]) . '"' . rex::getAccesskey(rex_i18n::msg('create_user_role'), 'add') . ' title="' . rex_i18n::msg('create_user_role') . '"><span class="rex-icon rex-icon-add-userrole"></span></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-slim">###VALUE###</th>', '<td class="rex-slim">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);

    $list->setColumnLabel('id', 'ID');
    $list->setColumnLayout('id', ['<th class="rex-id">###VALUE###</th>', '<td class="rex-id">###VALUE###</td>']);

    $list->setColumnLabel('name', rex_i18n::msg('name'));
    $list->setColumnLayout('name', ['<th class="rex-name">###VALUE###</th>', '<td class="rex-name">###VALUE###</td>']);
    $list->setColumnParams('name', ['func' => 'edit', 'id' => '###id###']);

    $list->addColumn('funcs', rex_i18n::msg('user_role_delete'));
    $list->setColumnLabel('funcs', rex_i18n::msg('user_functions'));
    $list->setColumnLayout('funcs', ['<th class="rex-function">###VALUE###</th>', '<td class="rex-delete">###VALUE###</td>']);
    $list->setColumnParams('funcs', ['func' => 'delete', 'id' => '###id###']);
    $list->addLinkAttribute('funcs', 'data-confirm', rex_i18n::msg('delete') . ' ?');
    $list->addLinkAttribute('funcs', 'class', 'rex-delete');

    $content .= $list->get();

} else {
    $label = $func == 'edit' ? rex_i18n::msg('edit_user_role') : rex_i18n::msg('add_user_role');
    $form = rex_form::factory(rex::getTablePrefix() . 'user_role', $label, 'id = ' . $id);
    $form->addParam('id', $id);
    $form->setApplyUrl(rex_url::currentBackendPage());
    $form->setEditMode($func == 'edit');

    $field = $form->addTextField('name');
    $field->setLabel(rex_i18n::msg('name'));

    $field = $form->addTextAreaField('description');
    $field->setLabel(rex_i18n::msg('description'));

    $fieldContainer = $form->addContainerField('perms');
    $fieldContainer->setMultiple(false);
    $group = 'all';
    $fieldContainer->setActive($group);

    // Check all page permissions and add them to rex_perm if not already registered
    $registerImplicitePagePermissions = function ($pages) use (&$registerImplicitePagePermissions) {
        foreach ($pages as $page) {
            foreach ($page->getRequiredPermissions() as $perm) {
                // ignore admin perm and complex perms (with "/")
                if ($perm && !in_array($perm, ['isAdmin', 'admin', 'admin[]']) && strpos($perm, '/') === false && !rex_perm::has($perm)) {
                    rex_perm::register($perm);
                }
            }
            $registerImplicitePagePermissions($page->getSubpages());
        }
    };
    $registerImplicitePagePermissions(rex_be_controller::getPages());

    foreach ([rex_perm::GENERAL, rex_perm::OPTIONS, rex_perm::EXTRAS] as $permgroup) {
        $field = $fieldContainer->addGroupedField($group, 'select', $permgroup);
        $field->setLabel(rex_i18n::msg('user_' . $permgroup));
        $select = $field->getSelect();
        $select->setMultiple(true);
        $perms = rex_perm::getAll($permgroup);
        $select->setSize(min(10, max(3, count($perms))));
        $select->addArrayOptions($perms);
    }

    rex_extension::register('REX_FORM_INPUT_CLASS', function (rex_extension_point $ep) {
        return $ep->getParam('inputType') == 'perm_select' ? 'rex_form_perm_select_element' : null;
    });

    $fieldIds = [];
    foreach (rex_complex_perm::getAll() as $key => $class) {
        $params = $class::getFieldParams();
        if (!empty($params)) {
            $field = $fieldContainer->addGroupedField($group, 'perm_select', $key);
            $field->setLabel($params['label']);
            $field->setCheckboxLabel($params['all_label']);
            $fieldIds[] = $field->getAttribute('id');
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
            $select->setSize(min(10, max(3, $select->countOptions())));
        }
    }

    $content .= $form->get();

    if ($fieldIds) {
        $content .= '
            <script type="text/javascript">
            <!--

            jQuery(function($) {

                function check_perm_field(field, duration) {
                    var id = field.attr("id").substr(0, field.attr("id").length - 4);
                    if(field.is(":checked"))
                        $("#"+id).closest(".rex-form").hide(duration);
                    else
                        $("#"+id).closest(".rex-form").show(duration);
                }
                $("#' . implode('_all, #', $fieldIds) . '_all").change(function(){
                    check_perm_field($(this), "slow");
                });

                $("#' . implode('_all, #', $fieldIds) . '_all").each(function(){
                    check_perm_field($(this), 0);
                });

            });

            //--></script>
        ';
    }

}

echo rex_view::contentBlock($content);
