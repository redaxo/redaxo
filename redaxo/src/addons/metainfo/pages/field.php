<?php

/**
 * MetaForm Addon.
 *
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
$func = rex_request('func', 'string');
$field_id = rex_request('field_id', 'int');

//------------------------------> Feld loeschen
if ('delete' == $func) {
    $field_id = rex_request('field_id', 'int', 0);
    if (0 != $field_id) {
        if (rex_metainfo_delete_field($field_id)) {
            echo rex_view::success(rex_i18n::msg('minfo_field_successfull_deleted'));
        } else {
            echo rex_view::error(rex_i18n::msg('minfo_field_error_deleted'));
        }
    }
    $func = '';
}

//------------------------------> Eintragsliste
if ('' == $func) {
    echo rex_api_function::getMessage();

    $title = rex_i18n::msg('minfo_field_list_caption');

    // replace LIKE wildcards
    $likePrefix = str_replace(['_', '%'], ['\_', '\%'], $prefix);

    $list = rex_list::factory('SELECT id, name FROM ' . rex::getTablePrefix() . 'metainfo_field WHERE `name` LIKE "' . $likePrefix . '%" ORDER BY priority');
    $list->addTableAttribute('class', 'table-striped');

    $tdIcon = '<i class="rex-icon rex-icon-metainfo"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"><i class="rex-icon rex-icon-add-metainfo"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'field_id' => '###id###']);

    $list->removeColumn('id');

    $list->setColumnLabel('id', rex_i18n::msg('minfo_field_label_id'));
    $list->setColumnLayout('id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id" data-title="' . rex_i18n::msg('minfo_field_label_id') . '">###VALUE###</td>']);

    $list->setColumnLabel('name', rex_i18n::msg('minfo_field_label_name'));
    $list->setColumnParams('name', ['func' => 'edit', 'field_id' => '###id###']);

    $list->addColumn(rex_i18n::msg('minfo_field_label_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('minfo_field_label_functions'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('minfo_field_label_functions'), ['func' => 'edit', 'field_id' => '###id###']);
    $list->addLinkAttribute(rex_i18n::msg('minfo_field_label_functions'), 'class', 'rex-edit');

    $list->addColumn('delete', '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout('delete', ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('delete', ['func' => 'delete', 'field_id' => '###id###']);
    $list->addLinkAttribute('delete', 'data-confirm', rex_i18n::msg('delete') . ' ?');
    $list->addLinkAttribute('delete', 'class', 'rex-delete');

    $list->setNoRowsMessage(rex_i18n::msg('minfo_metainfos_not_found'));

    $content .= $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', $title);

    if (in_array($prefix, ['art_', 'med_'])) {
        $defaultFields = sprintf(
            '<div class="btn-group btn-group-xs"><a href="%s" class="btn btn-default">%s</a></div>',
            rex_url::currentBackendPage(['type' => rex_be_controller::getCurrentPagePart(2)] + rex_api_metainfo_default_fields_create::getUrlParams()),
            rex_i18n::msg('minfo_default_fields_create')
        );
        $fragment->setVar('options', $defaultFields, false);
    }

    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');
}
//------------------------------> Formular
elseif ('edit' == $func || 'add' == $func) {
    $title = rex_i18n::msg('minfo_field_fieldset');
    $form = new rex_metainfo_table_expander($prefix, $metaTable, rex::getTablePrefix().'metainfo_field', 'id='.$field_id);

    if ('edit' == $func) {
        $form->addParam('field_id', $field_id);
    }

    $content .= $form->get();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $title);
    $fragment->setVar('body', $content, false);
    $content = $fragment->parse('core/page/section.php');
}

echo $content;
