<?php

/**
 * MetaForm Addon
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

$title = '';
$content = '';

//------------------------------> Parameter
if (empty($prefix)) {
    throw new rex_exception('Fehler: Prefix nicht definiert!');
}

if (empty($metaTable)) {
    throw new rex_exception('Fehler: metaTable nicht definiert!');
}

$Basedir = __DIR__;
$field_id = rex_request('field_id', 'int');

//------------------------------> Feld loeschen
if ($func == 'delete') {
    $field_id = rex_request('field_id', 'int', 0);
    if ($field_id != 0) {
        if (rex_metainfo_delete_field($field_id)) {
            echo rex_view::success(rex_i18n::msg('minfo_field_successfull_deleted'));
        } else {
            echo rex_view::error(rex_i18n::msg('minfo_field_error_deleted'));
        }
    }
    $func = '';
}

//------------------------------> Eintragsliste
if ($func == '') {
    $title = rex_i18n::msg('minfo_field_list_caption');

    // replace LIKE wildcards
    $likePrefix = str_replace(['_', '%'], ['\_', '\%'], $prefix);

    $list = rex_list::factory('SELECT id, name FROM ' . rex::getTablePrefix() . 'metainfo_field WHERE `name` LIKE "' . $likePrefix . '%" ORDER BY priority');
    $list->addTableAttribute('class', 'table-striped');
    
    $tdIcon = '<i class="rex-icon rex-icon-metainfo"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"><i class="rex-icon rex-icon-add-metainfo"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th>###VALUE###</th>', '<td>###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'field_id' => '###id###']);

    $list->removeColumn('id');

    $list->setColumnLabel('id', rex_i18n::msg('minfo_field_label_id'));
    $list->setColumnLayout('id',  ['<th class="rex-id">###VALUE###</th>', '<td class="rex-id">###VALUE###</td>']);

    $list->setColumnLabel('name', rex_i18n::msg('minfo_field_label_name'));
    $list->setColumnLayout('name',  ['<th>###VALUE###</th>', '<td>###VALUE###</td>']);
    $list->setColumnParams('name', ['func' => 'edit', 'field_id' => '###id###']);


    $list->addColumn(rex_i18n::msg('minfo_field_label_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('minfo_field_label_functions'),  ['<th colspan="2">###VALUE###</th>', '<td>###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('minfo_field_label_functions'), ['func' => 'edit', 'field_id' => '###id###']);
    $list->addLinkAttribute(rex_i18n::msg('minfo_field_label_functions'), 'class', 'rex-edit');

    $list->addColumn('delete', '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout('delete',  ['', '<td>###VALUE###</td>']);
    $list->setColumnParams('delete', ['func' => 'delete', 'field_id' => '###id###']);
    $list->addLinkAttribute('delete', 'data-confirm', rex_i18n::msg('delete') . ' ?');
    $list->addLinkAttribute('delete', 'class', 'rex-delete');



    $list->setNoRowsMessage(rex_i18n::msg('minfo_metainfos_not_found'));

    $content .= $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', $title);
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');
}
//------------------------------> Formular
elseif ($func == 'edit' || $func == 'add') {
    $title = rex_i18n::msg('minfo_field_fieldset');
    $form = new rex_metainfo_table_expander($prefix, $metaTable, rex::getTablePrefix() . 'metainfo_field', '', 'id=' . $field_id);

    if ($func == 'edit') {
        $form->addParam('field_id', $field_id);
    }

    $content .= $form->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', $title);
    $fragment->setVar('body', $content, false);
    $content = $fragment->parse('core/page/section.php');
}




echo $content;
