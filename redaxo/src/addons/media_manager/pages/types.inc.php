<?php

$content = '';

$Basedir = dirname(__FILE__);

$type_id = rex_request('type_id', 'int');
$func = rex_request('func', 'string');

$info = '';
$warning = '';

//-------------- delete cache on type_name change or type deletion
if ((rex_post('func') == 'edit' || $func == 'delete') && $type_id > 0) {
  $counter = rex_media_manager::deleteCacheByType($type_id);
  //  $info = rex_i18n::msg('media_manager_cache_files_removed', $counter);
}

//-------------- delete type
if ($func == 'delete' && $type_id > 0) {
  $sql = rex_sql::factory();
  //  $sql->debugsql = true;
  $sql->setTable(rex::getTablePrefix() . 'media_manager_types');
  $sql->setWhere(array('id' => $type_id));

  if ($sql->delete()) {
    $info = rex_i18n::msg('media_manager_type_deleted') ;
  } else {
    $warning = $sql->getError();
  }
  $func = '';
}

//-------------- delete cache by type-id
if ($func == 'delete_cache' && $type_id > 0) {
  $counter = rex_media_manager::deleteCacheByType($type_id);
  $info = rex_i18n::msg('media_manager_cache_files_removed', $counter);
  $func = '';
}

//-------------- output messages
if ($info != '')
  $content .= rex_view::info($info);

if ($warning != '')
  $content .= rex_view::warning($warning);

if ($func == '') {
  // Nach Status sortieren, damit Systemtypen immer zuletzt stehen
  // (werden am seltesten bearbeitet)
  $query = 'SELECT * FROM ' . rex::getTablePrefix() . 'media_manager_types ORDER BY status';

  $list = rex_list::factory($query);
  $list->setNoRowsMessage(rex_i18n::msg('media_manager_type_no_types'));
  $list->setCaption(rex_i18n::msg('media_manager_type_caption'));
  $list->addTableAttribute('summary', rex_i18n::msg('media_manager_type_summary'));
  $list->addTableColumnGroup(array(40, 100, '*', 120, 120, 120));

  $list->removeColumn('id');
  $list->removeColumn('status');
  $list->setColumnLabel('name', rex_i18n::msg('media_manager_type_name'));
  $list->setColumnParams('name', array('func' => 'edit', 'type_id' => '###id###'));
  $list->setColumnLabel('description', rex_i18n::msg('media_manager_type_description'));

  // icon column
  $thIcon = '<a class="rex-i-element rex-i-generic-add" href="' . $list->getUrl(array('func' => 'add')) . '"><span class="rex-i-element-text">' . rex_i18n::msg('media_manager_type_create') . '</span></a>';
  $tdIcon = '<span class="rex-i-element rex-i-generic"><span class="rex-i-element-text">###name###</span></span>';
  $list->addColumn($thIcon, $tdIcon, 0, array('<th class="rex-icon">###VALUE###</th>', '<td class="rex-icon">###VALUE###</td>'));
  $list->setColumnParams($thIcon, array('func' => 'edit', 'type_id' => '###id###'));

  // functions column spans 2 data-columns
  $funcs = rex_i18n::msg('media_manager_type_functions');
  $list->addColumn($funcs, rex_i18n::msg('media_manager_type_effekts_edit'), -1, array('<th colspan="3">###VALUE###</th>', '<td>###VALUE###</td>'));
  $list->setColumnParams($funcs, array('type_id' => '###id###', 'subpage' => 'effects'));

  $list->addColumn('deleteCache', rex_i18n::msg('media_manager_type_cache_delete'), -1, array('', '<td>###VALUE###</td>'));
  $list->setColumnParams('deleteCache', array('type_id' => '###id###', 'func' => 'delete_cache'));
  $list->addLinkAttribute('deleteCache', 'data-confirm', rex_i18n::msg('media_manager_type_cache_delete') . ' ?');

  // remove delete link on internal types (status == 1)
  $list->addColumn('deleteType', '', -1, array('', '<td>###VALUE###</td>'));
  $list->setColumnParams('deleteType', array('type_id' => '###id###', 'func' => 'delete'));
  $list->addLinkAttribute('deleteType', 'data-confirm', rex_i18n::msg('delete') . ' ?');
  $list->setColumnFormat('deleteType', 'custom', function ($params) {
    $list = $params['list'];
    if ($list->getValue('status') == 1) {
      return rex_i18n::msg('media_manager_type_system');
    }
    return $list->getColumnLink('deleteType', rex_i18n::msg('media_manager_type_delete'));
  });

  $content .= $list->get();

} elseif ($func == 'add' || $func == 'edit' && $type_id > 0) {
  if ($func == 'edit') {
    $formLabel = rex_i18n::msg('media_manager_type_edit');
  } elseif ($func == 'add') {
    $formLabel = rex_i18n::msg('media_manager_type_create');
  }

  function rex_media_manager_handle_form_control_fields($params)
  {
    $controlFields = $params['subject'];
    $form = $params['form'];
    $sql  = $form->getSql();

    // remove delete button on internal types (status == 1)
    if ($sql->getRows() > 0 && $sql->hasValue('status') && $sql->getValue('status') == 1) {
      $controlFields['delete'] = '';
    }
    return $controlFields;
  }

  rex_extension::register('REX_FORM_CONTROL_FIELDS', 'rex_media_manager_handle_form_control_fields');
  $form = rex_form::factory(rex::getTablePrefix() . 'media_manager_types', $formLabel, 'id=' . $type_id);

  $form->addErrorMessage(REX_FORM_ERROR_VIOLATE_UNIQUE_KEY, rex_i18n::msg('media_manager_error_type_name_not_unique'));

  $field = $form->addTextField('name');
  $field->setLabel(rex_i18n::msg('media_manager_type_name'));

  $field = $form->addTextareaField('description');
  $field->setLabel(rex_i18n::msg('media_manager_type_description'));

  if ($func == 'edit') {
    $form->addParam('type_id', $type_id);
  }

  $content .= $form->get();
}


echo rex_view::contentBlock($content);
