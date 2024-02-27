<?php

rex_sql_table::get(rex::getTable('clang'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('code', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('priority', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'))
    ->ensure();

$sql = rex_sql::factory();
if (!$sql->setQuery('SELECT 1 FROM ' . rex::getTable('clang') . ' LIMIT 1')->getRows()) {
    $sql->setTable(rex::getTable('clang'));
    $sql->setValues(['id' => 1, 'code' => 'de', 'name' => 'deutsch', 'priority' => 1, 'status' => 1]);
    $sql->insert();
}

rex_sql_table::get(rex::getTable('config'))
    ->removeColumn('id')
    ->ensureColumn(new rex_sql_column('namespace', 'varchar(75)'))
    ->ensureColumn(new rex_sql_column('key', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('value', 'text'))
    ->setPrimaryKey(['namespace', 'key'])
    ->ensure();

rex_sql_table::get(rex::getTable('action'))
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('preview', 'text', true))
    ->ensureColumn(new rex_sql_column('presave', 'text', true))
    ->ensureColumn(new rex_sql_column('postsave', 'text', true))
    ->ensureColumn(new rex_sql_column('previewmode', 'tinyint(4)', true))
    ->ensureColumn(new rex_sql_column('presavemode', 'tinyint(4)', true))
    ->ensureColumn(new rex_sql_column('postsavemode', 'tinyint(4)', true))
    ->ensureGlobalColumns()
    ->setPrimaryKey('id')
    ->ensure();

rex_sql_table::get(rex::getTable('article'))
    ->ensureColumn(new rex_sql_column('pid', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('parent_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('catname', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('catpriority', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('startarticle', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('priority', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('path', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('template_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('clang_id', 'int(10) unsigned'))
    ->ensureGlobalColumns()
    ->setPrimaryKey('pid')
    ->ensureIndex(new rex_sql_index('find_articles', ['id', 'clang_id'], rex_sql_index::UNIQUE))
    ->ensureIndex(new rex_sql_index('clang_id', ['clang_id']))
    ->ensureIndex(new rex_sql_index('parent_id', ['parent_id']))
    ->removeIndex('id')
    ->ensure();

rex_sql_table::get(rex::getTable('article_slice'))
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new rex_sql_column('article_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('clang_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('ctype_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('module_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('revision', 'int(11)'))
    ->ensureColumn(new rex_sql_column('priority', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('status', 'tinyint(1)', false, '1'))
    ->ensureColumn(new rex_sql_column('value1', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value2', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value3', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value4', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value5', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value6', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value7', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value8', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value9', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value10', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value11', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value12', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value13', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value14', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value15', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value16', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value17', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value18', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value19', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('value20', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('media1', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media2', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media3', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media4', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media5', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media6', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media7', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media8', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media9', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media10', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('medialist1', 'text', true))
    ->ensureColumn(new rex_sql_column('medialist2', 'text', true))
    ->ensureColumn(new rex_sql_column('medialist3', 'text', true))
    ->ensureColumn(new rex_sql_column('medialist4', 'text', true))
    ->ensureColumn(new rex_sql_column('medialist5', 'text', true))
    ->ensureColumn(new rex_sql_column('medialist6', 'text', true))
    ->ensureColumn(new rex_sql_column('medialist7', 'text', true))
    ->ensureColumn(new rex_sql_column('medialist8', 'text', true))
    ->ensureColumn(new rex_sql_column('medialist9', 'text', true))
    ->ensureColumn(new rex_sql_column('medialist10', 'text', true))
    ->ensureColumn(new rex_sql_column('link1', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link2', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link3', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link4', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link5', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link6', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link7', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link8', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link9', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link10', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('linklist1', 'text', true))
    ->ensureColumn(new rex_sql_column('linklist2', 'text', true))
    ->ensureColumn(new rex_sql_column('linklist3', 'text', true))
    ->ensureColumn(new rex_sql_column('linklist4', 'text', true))
    ->ensureColumn(new rex_sql_column('linklist5', 'text', true))
    ->ensureColumn(new rex_sql_column('linklist6', 'text', true))
    ->ensureColumn(new rex_sql_column('linklist7', 'text', true))
    ->ensureColumn(new rex_sql_column('linklist8', 'text', true))
    ->ensureColumn(new rex_sql_column('linklist9', 'text', true))
    ->ensureColumn(new rex_sql_column('linklist10', 'text', true))
    ->ensureGlobalColumns()
    ->setPrimaryKey('id')
    ->ensureIndex(new rex_sql_index('slice_priority', ['article_id', 'priority', 'module_id']))
    ->ensureIndex(new rex_sql_index('find_slices', ['clang_id', 'article_id']))
    ->removeIndex('clang_id')
    ->removeIndex('article_id')
    ->ensure();

rex_sql_table::get(rex::getTable('article_slice_history'))
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new rex_sql_column('slice_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('history_type', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('history_date', 'datetime'))
    ->ensureColumn(new rex_sql_column('history_user', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('clang_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('ctype_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('priority', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('value1', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value2', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value3', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value4', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value5', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value6', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value7', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value8', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value9', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value10', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value11', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value12', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value13', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value14', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value15', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value16', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value17', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value18', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value19', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value20', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('media1', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media2', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media3', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media4', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media5', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media6', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media7', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media8', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media9', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media10', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('medialist1', 'text'))
    ->ensureColumn(new rex_sql_column('medialist2', 'text'))
    ->ensureColumn(new rex_sql_column('medialist3', 'text'))
    ->ensureColumn(new rex_sql_column('medialist4', 'text'))
    ->ensureColumn(new rex_sql_column('medialist5', 'text'))
    ->ensureColumn(new rex_sql_column('medialist6', 'text'))
    ->ensureColumn(new rex_sql_column('medialist7', 'text'))
    ->ensureColumn(new rex_sql_column('medialist8', 'text'))
    ->ensureColumn(new rex_sql_column('medialist9', 'text'))
    ->ensureColumn(new rex_sql_column('medialist10', 'text'))
    ->ensureColumn(new rex_sql_column('link1', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link2', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link3', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link4', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link5', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link6', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link7', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link8', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link9', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link10', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('linklist1', 'text'))
    ->ensureColumn(new rex_sql_column('linklist2', 'text'))
    ->ensureColumn(new rex_sql_column('linklist3', 'text'))
    ->ensureColumn(new rex_sql_column('linklist4', 'text'))
    ->ensureColumn(new rex_sql_column('linklist5', 'text'))
    ->ensureColumn(new rex_sql_column('linklist6', 'text'))
    ->ensureColumn(new rex_sql_column('linklist7', 'text'))
    ->ensureColumn(new rex_sql_column('linklist8', 'text'))
    ->ensureColumn(new rex_sql_column('linklist9', 'text'))
    ->ensureColumn(new rex_sql_column('linklist10', 'text'))
    ->ensureColumn(new rex_sql_column('article_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('module_id', 'int(10) unsigned'))
    ->ensureGlobalColumns()
    ->ensureColumn(new rex_sql_column('revision', 'int(11)'))
    ->setPrimaryKey('id')
    ->ensureIndex(new rex_sql_index('snapshot', ['article_id', 'clang_id', 'revision', 'history_date']))
    ->ensure();

rex_sql_table::get(rex::getTable('module'))
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new rex_sql_column('key', 'varchar(191)', true))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('output', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('input', 'mediumtext'))
    ->ensureGlobalColumns()
    ->setPrimaryKey('id')
    ->ensureIndex(new rex_sql_index('key', ['key'], rex_sql_index::UNIQUE))
    ->ensure();

rex_sql_table::get(rex::getTable('module_action'))
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new rex_sql_column('module_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('action_id', 'int(10) unsigned'))
    ->setPrimaryKey('id')
    ->ensure();

rex_sql_table::get(rex::getTable('template'))
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new rex_sql_column('key', 'varchar(191)', true))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('content', 'mediumtext', true))
    ->ensureColumn(new rex_sql_column('active', 'tinyint(1)', true))
    ->ensureGlobalColumns()
    ->ensureColumn(new rex_sql_column('attributes', 'text', true))
    ->setPrimaryKey('id')
    ->ensureIndex(new rex_sql_index('key', ['key'], rex_sql_index::UNIQUE))
    ->ensure();

$sql = rex_sql::factory();
$sql->setQuery('UPDATE ' . rex::getTablePrefix() . 'article_slice set revision=0 where revision<1 or revision IS NULL');
$sql->setQuery('SELECT 1 FROM ' . rex::getTable('template') . ' LIMIT 1');
if (!$sql->getRows()) {
    $sql
        ->setTable(rex::getTable('template'))
        ->setValue('id', 1)
        ->setValue('name', 'Default')
        ->setValue('content', 'REX_ARTICLE[]')
        ->setValue('active', 1)
        ->setValue('attributes', '{"ctype":[],"modules":{"1":{"all":"1"}},"categories":{"all":"1"}}')
        ->setRawValue('createdate', 'NOW()')
        ->setRawValue('updatedate', 'NOW()')
        ->insert();
}

rex_sql_table::get(rex::getTable('cronjob'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('description', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('type', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('parameters', 'text', true))
    ->ensureColumn(new rex_sql_column('interval', 'text'))
    ->ensureColumn(new rex_sql_column('nexttime', 'datetime', true))
    ->ensureColumn(new rex_sql_column('environment', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('execution_moment', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('execution_start', 'datetime'))
    ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'))
    ->ensureGlobalColumns()
    ->ensure();

rex_sql_table::get(rex::getTable('media_manager_type'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('status', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('description', 'varchar(255)'))
    ->ensureIndex(new rex_sql_index('name', ['name'], rex_sql_index::UNIQUE))
    ->ensureGlobalColumns()
    ->ensure();

rex_sql_table::get(rex::getTable('media_manager_type_effect'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('type_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('effect', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('parameters', 'text'))
    ->ensureColumn(new rex_sql_column('priority', 'int(10) unsigned'))
    ->ensureGlobalColumns()
    ->ensure();

$data = [
    ['id' => 1, 'name' => 'rex_media_small', 'description' => '200 × 200 px'],
    ['id' => 2, 'name' => 'rex_media_medium', 'description' => '600 × 600 px'],
    ['id' => 3, 'name' => 'rex_media_large', 'description' => '1200 × 1200 px'],
];

$sql = rex_sql::factory();
$sql->setTable(rex::getTable('media_manager_type'));

foreach ($data as $row) {
    $sql->addRecord(static function (rex_sql $record) use ($row) {
        $record
            ->setValues($row)
            ->setValue('status', 1)
            ->addGlobalCreateFields()
            ->addGlobalUpdateFields();
    });
}

$sql->insertOrUpdate();

$data = [
    ['id' => 1, 'type_id' => 1, 'effect' => 'resize', 'parameters' => '{"rex_effect_crop":{"rex_effect_crop_width":"","rex_effect_crop_height":"","rex_effect_crop_offset_width":"","rex_effect_crop_offset_height":"","rex_effect_crop_hpos":"center","rex_effect_crop_vpos":"middle"},"rex_effect_filter_blur":{"rex_effect_filter_blur_amount":"80","rex_effect_filter_blur_radius":"8","rex_effect_filter_blur_threshold":"3"},"rex_effect_filter_sharpen":{"rex_effect_filter_sharpen_amount":"80","rex_effect_filter_sharpen_radius":"0.5","rex_effect_filter_sharpen_threshold":"3"},"rex_effect_flip":{"rex_effect_flip_flip":"X"},"rex_effect_header":{"rex_effect_header_download":"open_media","rex_effect_header_cache":"no_cache"},"rex_effect_insert_image":{"rex_effect_insert_image_brandimage":"","rex_effect_insert_image_hpos":"left","rex_effect_insert_image_vpos":"top","rex_effect_insert_image_padding_x":"-10","rex_effect_insert_image_padding_y":"-10"},"rex_effect_mediapath":{"rex_effect_mediapath_mediapath":""},"rex_effect_mirror":{"rex_effect_mirror_height":"","rex_effect_mirror_set_transparent":"colored","rex_effect_mirror_bg_r":"","rex_effect_mirror_bg_g":"","rex_effect_mirror_bg_b":""},"rex_effect_resize":{"rex_effect_resize_width":"200","rex_effect_resize_height":"200","rex_effect_resize_style":"maximum","rex_effect_resize_allow_enlarge":"not_enlarge"},"rex_effect_workspace":{"rex_effect_workspace_width":"","rex_effect_workspace_height":"","rex_effect_workspace_hpos":"left","rex_effect_workspace_vpos":"top","rex_effect_workspace_set_transparent":"colored","rex_effect_workspace_bg_r":"","rex_effect_workspace_bg_g":"","rex_effect_workspace_bg_b":""}}'],
    ['id' => 2, 'type_id' => 2, 'effect' => 'resize', 'parameters' => '{"rex_effect_crop":{"rex_effect_crop_width":"","rex_effect_crop_height":"","rex_effect_crop_offset_width":"","rex_effect_crop_offset_height":"","rex_effect_crop_hpos":"center","rex_effect_crop_vpos":"middle"},"rex_effect_filter_blur":{"rex_effect_filter_blur_amount":"80","rex_effect_filter_blur_radius":"8","rex_effect_filter_blur_threshold":"3"},"rex_effect_filter_sharpen":{"rex_effect_filter_sharpen_amount":"80","rex_effect_filter_sharpen_radius":"0.5","rex_effect_filter_sharpen_threshold":"3"},"rex_effect_flip":{"rex_effect_flip_flip":"X"},"rex_effect_header":{"rex_effect_header_download":"open_media","rex_effect_header_cache":"no_cache"},"rex_effect_insert_image":{"rex_effect_insert_image_brandimage":"","rex_effect_insert_image_hpos":"left","rex_effect_insert_image_vpos":"top","rex_effect_insert_image_padding_x":"-10","rex_effect_insert_image_padding_y":"-10"},"rex_effect_mediapath":{"rex_effect_mediapath_mediapath":""},"rex_effect_mirror":{"rex_effect_mirror_height":"","rex_effect_mirror_set_transparent":"colored","rex_effect_mirror_bg_r":"","rex_effect_mirror_bg_g":"","rex_effect_mirror_bg_b":""},"rex_effect_resize":{"rex_effect_resize_width":"600","rex_effect_resize_height":"600","rex_effect_resize_style":"maximum","rex_effect_resize_allow_enlarge":"not_enlarge"},"rex_effect_workspace":{"rex_effect_workspace_width":"","rex_effect_workspace_height":"","rex_effect_workspace_hpos":"left","rex_effect_workspace_vpos":"top","rex_effect_workspace_set_transparent":"colored","rex_effect_workspace_bg_r":"","rex_effect_workspace_bg_g":"","rex_effect_workspace_bg_b":""}}'],
    ['id' => 3, 'type_id' => 3, 'effect' => 'resize', 'parameters' => '{"rex_effect_crop":{"rex_effect_crop_width":"","rex_effect_crop_height":"","rex_effect_crop_offset_width":"","rex_effect_crop_offset_height":"","rex_effect_crop_hpos":"center","rex_effect_crop_vpos":"middle"},"rex_effect_filter_blur":{"rex_effect_filter_blur_amount":"80","rex_effect_filter_blur_radius":"8","rex_effect_filter_blur_threshold":"3"},"rex_effect_filter_sharpen":{"rex_effect_filter_sharpen_amount":"80","rex_effect_filter_sharpen_radius":"0.5","rex_effect_filter_sharpen_threshold":"3"},"rex_effect_flip":{"rex_effect_flip_flip":"X"},"rex_effect_header":{"rex_effect_header_download":"open_media","rex_effect_header_cache":"no_cache"},"rex_effect_insert_image":{"rex_effect_insert_image_brandimage":"","rex_effect_insert_image_hpos":"left","rex_effect_insert_image_vpos":"top","rex_effect_insert_image_padding_x":"-10","rex_effect_insert_image_padding_y":"-10"},"rex_effect_mediapath":{"rex_effect_mediapath_mediapath":""},"rex_effect_mirror":{"rex_effect_mirror_height":"","rex_effect_mirror_set_transparent":"colored","rex_effect_mirror_bg_r":"","rex_effect_mirror_bg_g":"","rex_effect_mirror_bg_b":""},"rex_effect_resize":{"rex_effect_resize_width":"1200","rex_effect_resize_height":"1200","rex_effect_resize_style":"maximum","rex_effect_resize_allow_enlarge":"not_enlarge"},"rex_effect_workspace":{"rex_effect_workspace_width":"","rex_effect_workspace_height":"","rex_effect_workspace_hpos":"left","rex_effect_workspace_vpos":"top","rex_effect_workspace_set_transparent":"colored","rex_effect_workspace_bg_r":"","rex_effect_workspace_bg_g":"","rex_effect_workspace_bg_b":""}}'],
];

$sql = rex_sql::factory();
$sql->setTable(rex::getTable('media_manager_type_effect'));

foreach ($data as $row) {
    $sql->addRecord(static function (rex_sql $record) use ($row) {
        $record
            ->setValues($row)
            ->setValue('priority', 1)
            ->addGlobalCreateFields()
            ->addGlobalUpdateFields();
    });
}

$sql->insertOrUpdate();

rex_sql_table::get(rex::getTable('user'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('description', 'text', true))
    ->ensureColumn(new rex_sql_column('login', 'varchar(50)'))
    ->ensureColumn(new rex_sql_column('password', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('email', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('admin', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('language', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('startpage', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('role', 'text', true))
    ->ensureColumn(new rex_sql_column('theme', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('login_tries', 'tinyint(4)', false, '0'))
    ->ensureGlobalColumns()
    ->ensureColumn(new rex_sql_column('password_changed', 'datetime'))
    ->ensureColumn(new rex_sql_column('previous_passwords', 'text'))
    ->ensureColumn(new rex_sql_column('password_change_required', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('lasttrydate', 'datetime'))
    ->ensureColumn(new rex_sql_column('lastlogin', 'datetime', true))
    ->ensureColumn(new rex_sql_column('session_id', 'varchar(255)', true))
    ->ensureIndex(new rex_sql_index('login', ['login'], rex_sql_index::UNIQUE))
    ->removeColumn('cookiekey')
    ->ensure();

rex_sql_table::get(rex::getTable('user_passkey'))
    ->ensureColumn(new rex_sql_column('id', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('user_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('public_key', 'text'))
    ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
    ->setPrimaryKey('id')
    ->ensureForeignKey(new rex_sql_foreign_key(rex::getTable('user_passkey') . '_user_id', rex::getTable('user'), ['user_id' => 'id'], rex_sql_foreign_key::CASCADE, rex_sql_foreign_key::CASCADE))
    ->ensure();

rex_sql_table::get(rex::getTable('user_role'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('description', 'text', true))
    ->ensureColumn(new rex_sql_column('perms', 'text'))
    ->ensureGlobalColumns()
    ->ensure();

rex_sql_table::get(rex::getTable('user_session'))
    ->ensureColumn(new rex_sql_column('session_id', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('user_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('cookie_key', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('passkey_id', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('ip', 'varchar(39)')) // max for ipv6
    ->ensureColumn(new rex_sql_column('useragent', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('starttime', 'datetime'))
    ->ensureColumn(new rex_sql_column('last_activity', 'datetime'))
    ->setPrimaryKey('session_id')
    ->ensureIndex(new rex_sql_index('cookie_key', ['cookie_key'], rex_sql_index::UNIQUE))
    ->ensureForeignKey(new rex_sql_foreign_key(rex::getTable('user_session') . '_user_id', rex::getTable('user'), ['user_id' => 'id'], rex_sql_foreign_key::CASCADE, rex_sql_foreign_key::CASCADE))
    ->ensureForeignKey(new rex_sql_foreign_key(rex::getTable('user_session') . '_passkey_id', rex::getTable('user_passkey'), ['passkey_id' => 'id'], rex_sql_foreign_key::CASCADE, rex_sql_foreign_key::CASCADE))
    ->ensure();

$defaultConfig = [
    'start_article_id' => 1,
    'notfound_article_id' => 1,
    'article_history' => false,
    'article_work_version' => false,
    'be_style_compile' => false,
    'be_style_labelcolor' => '#3bb594',
    'be_style_showlink' => true,
    'media_manager_jpg_quality' => 80,
    'media_manager_png_compression' => 5,
    'media_manager_webp_quality' => 85,
    'media_manager_avif_quality' => 60,
    'media_manager_avif_speed' => 6,
    'media_manager_interlace' => ['jpg'],
    'phpmailer_from' => '',
    'phpmailer_test_address' => '',
    'phpmailer_fromname' => 'Mailer',
    'phpmailer_confirmto' => '',
    'phpmailer_bcc' => '',
    'phpmailer_mailer' => 'smtp',
    'phpmailer_host' => 'localhost',
    'phpmailer_port' => 587,
    'phpmailer_charset' => 'utf-8',
    'phpmailer_wordwrap' => 120,
    'phpmailer_encoding' => '8bit',
    'phpmailer_priority' => 0,
    'phpmailer_security_mode' => false,
    'phpmailer_smtpsecure' => 'tls',
    'phpmailer_smtpauth' => true,
    'phpmailer_username' => '',
    'phpmailer_password' => '',
    'phpmailer_smtp_debug' => '0',
    'phpmailer_logging' => 0,
    'phpmailer_errormail' => 0,
    'phpmailer_archive' => false,
    'phpmailer_detour_mode' => false,
];

rex_config::refresh();
foreach ($defaultConfig as $key => $value) {
    if (!rex::hasConfig($key)) {
        rex::setConfig($key, $value);
    }
}
