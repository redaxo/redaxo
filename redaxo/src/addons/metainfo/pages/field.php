<?php

/**
 * MetaForm Addon
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

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
            echo rex_view::info(rex_i18n::msg('minfo_field_successfull_deleted'));
        } else {
            echo rex_view::warning(rex_i18n::msg('minfo_field_error_deleted'));
        }
    }
    $func = '';
}

//------------------------------> Eintragsliste
if ($func == '') {
    // replace LIKE wildcards
    $likePrefix = str_replace(['_', '%'], ['\_', '\%'], $prefix);

    $list = rex_list::factory('SELECT id, name FROM ' . rex::getTablePrefix() . 'metainfo_field WHERE `name` LIKE "' . $likePrefix . '%" ORDER BY priority');

    $list->setCaption(rex_i18n::msg('minfo_field_list_caption'));
    $imgHeader = '<a class="rex-ic-metainfo rex-ic-add" href="' . $list->getUrl(['func' => 'add']) . '">' . rex_i18n::msg('add') . '</a>';
    $list->addColumn($imgHeader, '<span class="rex-ic-metainfo">' . rex_i18n::msg('edit') . '</span>', 0, ['<th class="rex-icon">###VALUE###</th>', '<td class="rex-icon">###VALUE###</td>']);
    $list->setColumnParams($imgHeader, ['func' => 'edit', 'field_id' => '###id###']);

    $list->removeColumn('id');

    $list->setColumnLabel('id', rex_i18n::msg('minfo_field_label_id'));
    $list->setColumnLayout('id',  ['<th class="rex-id">###VALUE###</th>', '<td class="rex-id">###VALUE###</td>']);

    $list->setColumnLabel('name', rex_i18n::msg('minfo_field_label_name'));
    $list->setColumnLayout('name',  ['<th class="rex-name">###VALUE###</th>', '<td class="rex-name">###VALUE###</td>']);
    $list->setColumnParams('name', ['func' => 'edit', 'field_id' => '###id###']);

    $list->addColumn('delete', rex_i18n::msg('delete'), -1, ['<th class="rex-function">' . rex_i18n::msg('minfo_field_label_function') . '</th>', '<td class="rex-delete">###VALUE###</td>']);
    $list->setColumnParams('delete', ['func' => 'delete', 'field_id' => '###id###']);
    $list->addLinkAttribute('delete', 'data-confirm', rex_i18n::msg('delete') . ' ?');

    $list->setNoRowsMessage(rex_i18n::msg('minfo_metainfos_not_found'));

    $list->show();
}
//------------------------------> Formular
elseif ($func == 'edit' || $func == 'add') {
    $form = new rex_metainfo_table_expander($prefix, $metaTable, rex::getTablePrefix() . 'metainfo_field', rex_i18n::msg('minfo_field_fieldset'), 'id=' . $field_id);

    if ($func == 'edit') {
        $form->addParam('field_id', $field_id);
    }

    $form->show();
}
