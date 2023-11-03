<?php

/**
 * media_manager Addon.
 *
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 */

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
