<?php

use Redaxo\Core\Core;
use Redaxo\Core\Database\Column;
use Redaxo\Core\Database\ForeignKey;
use Redaxo\Core\Database\Index;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Database\Table;

Table::get(Core::getTable('clang'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new Column('code', 'varchar(255)'))
    ->ensureColumn(new Column('name', 'varchar(255)'))
    ->ensureColumn(new Column('priority', 'int(10) unsigned'))
    ->ensureColumn(new Column('status', 'tinyint(1)'))
    ->ensure();

$sql = Sql::factory();
if (!$sql->setQuery('SELECT 1 FROM ' . Core::getTable('clang') . ' LIMIT 1')->getRows()) {
    $sql->setTable(Core::getTable('clang'));
    $sql->setValues(['id' => 1, 'code' => 'de', 'name' => 'deutsch', 'priority' => 1, 'status' => 1]);
    $sql->insert();
}

Table::get(Core::getTable('config'))
    ->removeColumn('id')
    ->ensureColumn(new Column('namespace', 'varchar(75)'))
    ->ensureColumn(new Column('key', 'varchar(255)'))
    ->ensureColumn(new Column('value', 'text'))
    ->setPrimaryKey(['namespace', 'key'])
    ->ensure();

Table::get(Core::getTable('action'))
    ->ensureColumn(new Column('id', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new Column('name', 'varchar(255)'))
    ->ensureColumn(new Column('preview', 'text', true))
    ->ensureColumn(new Column('presave', 'text', true))
    ->ensureColumn(new Column('postsave', 'text', true))
    ->ensureColumn(new Column('previewmode', 'tinyint(4)', true))
    ->ensureColumn(new Column('presavemode', 'tinyint(4)', true))
    ->ensureColumn(new Column('postsavemode', 'tinyint(4)', true))
    ->ensureGlobalColumns()
    ->setPrimaryKey('id')
    ->ensure();

Table::get(Core::getTable('article'))
    ->ensureColumn(new Column('pid', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new Column('id', 'int(10) unsigned'))
    ->ensureColumn(new Column('parent_id', 'int(10) unsigned'))
    ->ensureColumn(new Column('name', 'varchar(255)'))
    ->ensureColumn(new Column('catname', 'varchar(255)'))
    ->ensureColumn(new Column('catpriority', 'int(10) unsigned'))
    ->ensureColumn(new Column('startarticle', 'tinyint(1)'))
    ->ensureColumn(new Column('priority', 'int(10) unsigned'))
    ->ensureColumn(new Column('path', 'varchar(255)'))
    ->ensureColumn(new Column('status', 'tinyint(1)'))
    ->ensureColumn(new Column('template_id', 'int(10) unsigned'))
    ->ensureColumn(new Column('clang_id', 'int(10) unsigned'))
    ->ensureGlobalColumns()
    ->setPrimaryKey('pid')
    ->ensureIndex(new Index('find_articles', ['id', 'clang_id'], Index::UNIQUE))
    ->ensureIndex(new Index('clang_id', ['clang_id']))
    ->ensureIndex(new Index('parent_id', ['parent_id']))
    ->removeIndex('id')
    ->ensure();

Table::get(Core::getTable('article_slice'))
    ->ensureColumn(new Column('id', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new Column('article_id', 'int(10) unsigned'))
    ->ensureColumn(new Column('clang_id', 'int(10) unsigned'))
    ->ensureColumn(new Column('ctype_id', 'int(10) unsigned'))
    ->ensureColumn(new Column('module_id', 'int(10) unsigned'))
    ->ensureColumn(new Column('revision', 'int(11)'))
    ->ensureColumn(new Column('priority', 'int(10) unsigned'))
    ->ensureColumn(new Column('status', 'tinyint(1)', false, '1'))
    ->ensureColumn(new Column('value1', 'mediumtext', true))
    ->ensureColumn(new Column('value2', 'mediumtext', true))
    ->ensureColumn(new Column('value3', 'mediumtext', true))
    ->ensureColumn(new Column('value4', 'mediumtext', true))
    ->ensureColumn(new Column('value5', 'mediumtext', true))
    ->ensureColumn(new Column('value6', 'mediumtext', true))
    ->ensureColumn(new Column('value7', 'mediumtext', true))
    ->ensureColumn(new Column('value8', 'mediumtext', true))
    ->ensureColumn(new Column('value9', 'mediumtext', true))
    ->ensureColumn(new Column('value10', 'mediumtext', true))
    ->ensureColumn(new Column('value11', 'mediumtext', true))
    ->ensureColumn(new Column('value12', 'mediumtext', true))
    ->ensureColumn(new Column('value13', 'mediumtext', true))
    ->ensureColumn(new Column('value14', 'mediumtext', true))
    ->ensureColumn(new Column('value15', 'mediumtext', true))
    ->ensureColumn(new Column('value16', 'mediumtext', true))
    ->ensureColumn(new Column('value17', 'mediumtext', true))
    ->ensureColumn(new Column('value18', 'mediumtext', true))
    ->ensureColumn(new Column('value19', 'mediumtext', true))
    ->ensureColumn(new Column('value20', 'mediumtext', true))
    ->ensureColumn(new Column('media1', 'varchar(255)', true))
    ->ensureColumn(new Column('media2', 'varchar(255)', true))
    ->ensureColumn(new Column('media3', 'varchar(255)', true))
    ->ensureColumn(new Column('media4', 'varchar(255)', true))
    ->ensureColumn(new Column('media5', 'varchar(255)', true))
    ->ensureColumn(new Column('media6', 'varchar(255)', true))
    ->ensureColumn(new Column('media7', 'varchar(255)', true))
    ->ensureColumn(new Column('media8', 'varchar(255)', true))
    ->ensureColumn(new Column('media9', 'varchar(255)', true))
    ->ensureColumn(new Column('media10', 'varchar(255)', true))
    ->ensureColumn(new Column('medialist1', 'text', true))
    ->ensureColumn(new Column('medialist2', 'text', true))
    ->ensureColumn(new Column('medialist3', 'text', true))
    ->ensureColumn(new Column('medialist4', 'text', true))
    ->ensureColumn(new Column('medialist5', 'text', true))
    ->ensureColumn(new Column('medialist6', 'text', true))
    ->ensureColumn(new Column('medialist7', 'text', true))
    ->ensureColumn(new Column('medialist8', 'text', true))
    ->ensureColumn(new Column('medialist9', 'text', true))
    ->ensureColumn(new Column('medialist10', 'text', true))
    ->ensureColumn(new Column('link1', 'varchar(10)', true))
    ->ensureColumn(new Column('link2', 'varchar(10)', true))
    ->ensureColumn(new Column('link3', 'varchar(10)', true))
    ->ensureColumn(new Column('link4', 'varchar(10)', true))
    ->ensureColumn(new Column('link5', 'varchar(10)', true))
    ->ensureColumn(new Column('link6', 'varchar(10)', true))
    ->ensureColumn(new Column('link7', 'varchar(10)', true))
    ->ensureColumn(new Column('link8', 'varchar(10)', true))
    ->ensureColumn(new Column('link9', 'varchar(10)', true))
    ->ensureColumn(new Column('link10', 'varchar(10)', true))
    ->ensureColumn(new Column('linklist1', 'text', true))
    ->ensureColumn(new Column('linklist2', 'text', true))
    ->ensureColumn(new Column('linklist3', 'text', true))
    ->ensureColumn(new Column('linklist4', 'text', true))
    ->ensureColumn(new Column('linklist5', 'text', true))
    ->ensureColumn(new Column('linklist6', 'text', true))
    ->ensureColumn(new Column('linklist7', 'text', true))
    ->ensureColumn(new Column('linklist8', 'text', true))
    ->ensureColumn(new Column('linklist9', 'text', true))
    ->ensureColumn(new Column('linklist10', 'text', true))
    ->ensureGlobalColumns()
    ->setPrimaryKey('id')
    ->ensureIndex(new Index('slice_priority', ['article_id', 'priority', 'module_id']))
    ->ensureIndex(new Index('find_slices', ['clang_id', 'article_id']))
    ->removeIndex('clang_id')
    ->removeIndex('article_id')
    ->ensure();

Table::get(Core::getTable('article_slice_history'))
    ->ensureColumn(new Column('id', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new Column('slice_id', 'int(10) unsigned'))
    ->ensureColumn(new Column('history_type', 'varchar(255)'))
    ->ensureColumn(new Column('history_date', 'datetime'))
    ->ensureColumn(new Column('history_user', 'varchar(255)'))
    ->ensureColumn(new Column('clang_id', 'int(10) unsigned'))
    ->ensureColumn(new Column('ctype_id', 'int(10) unsigned'))
    ->ensureColumn(new Column('priority', 'int(10) unsigned'))
    ->ensureColumn(new Column('value1', 'mediumtext'))
    ->ensureColumn(new Column('value2', 'mediumtext'))
    ->ensureColumn(new Column('value3', 'mediumtext'))
    ->ensureColumn(new Column('value4', 'mediumtext'))
    ->ensureColumn(new Column('value5', 'mediumtext'))
    ->ensureColumn(new Column('value6', 'mediumtext'))
    ->ensureColumn(new Column('value7', 'mediumtext'))
    ->ensureColumn(new Column('value8', 'mediumtext'))
    ->ensureColumn(new Column('value9', 'mediumtext'))
    ->ensureColumn(new Column('value10', 'mediumtext'))
    ->ensureColumn(new Column('value11', 'mediumtext'))
    ->ensureColumn(new Column('value12', 'mediumtext'))
    ->ensureColumn(new Column('value13', 'mediumtext'))
    ->ensureColumn(new Column('value14', 'mediumtext'))
    ->ensureColumn(new Column('value15', 'mediumtext'))
    ->ensureColumn(new Column('value16', 'mediumtext'))
    ->ensureColumn(new Column('value17', 'mediumtext'))
    ->ensureColumn(new Column('value18', 'mediumtext'))
    ->ensureColumn(new Column('value19', 'mediumtext'))
    ->ensureColumn(new Column('value20', 'mediumtext'))
    ->ensureColumn(new Column('media1', 'varchar(255)', true))
    ->ensureColumn(new Column('media2', 'varchar(255)', true))
    ->ensureColumn(new Column('media3', 'varchar(255)', true))
    ->ensureColumn(new Column('media4', 'varchar(255)', true))
    ->ensureColumn(new Column('media5', 'varchar(255)', true))
    ->ensureColumn(new Column('media6', 'varchar(255)', true))
    ->ensureColumn(new Column('media7', 'varchar(255)', true))
    ->ensureColumn(new Column('media8', 'varchar(255)', true))
    ->ensureColumn(new Column('media9', 'varchar(255)', true))
    ->ensureColumn(new Column('media10', 'varchar(255)', true))
    ->ensureColumn(new Column('medialist1', 'text'))
    ->ensureColumn(new Column('medialist2', 'text'))
    ->ensureColumn(new Column('medialist3', 'text'))
    ->ensureColumn(new Column('medialist4', 'text'))
    ->ensureColumn(new Column('medialist5', 'text'))
    ->ensureColumn(new Column('medialist6', 'text'))
    ->ensureColumn(new Column('medialist7', 'text'))
    ->ensureColumn(new Column('medialist8', 'text'))
    ->ensureColumn(new Column('medialist9', 'text'))
    ->ensureColumn(new Column('medialist10', 'text'))
    ->ensureColumn(new Column('link1', 'varchar(10)', true))
    ->ensureColumn(new Column('link2', 'varchar(10)', true))
    ->ensureColumn(new Column('link3', 'varchar(10)', true))
    ->ensureColumn(new Column('link4', 'varchar(10)', true))
    ->ensureColumn(new Column('link5', 'varchar(10)', true))
    ->ensureColumn(new Column('link6', 'varchar(10)', true))
    ->ensureColumn(new Column('link7', 'varchar(10)', true))
    ->ensureColumn(new Column('link8', 'varchar(10)', true))
    ->ensureColumn(new Column('link9', 'varchar(10)', true))
    ->ensureColumn(new Column('link10', 'varchar(10)', true))
    ->ensureColumn(new Column('linklist1', 'text'))
    ->ensureColumn(new Column('linklist2', 'text'))
    ->ensureColumn(new Column('linklist3', 'text'))
    ->ensureColumn(new Column('linklist4', 'text'))
    ->ensureColumn(new Column('linklist5', 'text'))
    ->ensureColumn(new Column('linklist6', 'text'))
    ->ensureColumn(new Column('linklist7', 'text'))
    ->ensureColumn(new Column('linklist8', 'text'))
    ->ensureColumn(new Column('linklist9', 'text'))
    ->ensureColumn(new Column('linklist10', 'text'))
    ->ensureColumn(new Column('article_id', 'int(10) unsigned'))
    ->ensureColumn(new Column('module_id', 'int(10) unsigned'))
    ->ensureGlobalColumns()
    ->ensureColumn(new Column('revision', 'int(11)'))
    ->setPrimaryKey('id')
    ->ensureIndex(new Index('snapshot', ['article_id', 'clang_id', 'revision', 'history_date']))
    ->ensure();

Table::get(Core::getTable('module'))
    ->ensureColumn(new Column('id', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new Column('key', 'varchar(191)', true))
    ->ensureColumn(new Column('name', 'varchar(255)'))
    ->ensureColumn(new Column('output', 'mediumtext'))
    ->ensureColumn(new Column('input', 'mediumtext'))
    ->ensureGlobalColumns()
    ->setPrimaryKey('id')
    ->ensureIndex(new Index('key', ['key'], Index::UNIQUE))
    ->ensure();

Table::get(Core::getTable('module_action'))
    ->ensureColumn(new Column('id', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new Column('module_id', 'int(10) unsigned'))
    ->ensureColumn(new Column('action_id', 'int(10) unsigned'))
    ->setPrimaryKey('id')
    ->ensure();

Table::get(Core::getTable('template'))
    ->ensureColumn(new Column('id', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new Column('key', 'varchar(191)', true))
    ->ensureColumn(new Column('name', 'varchar(255)', true))
    ->ensureColumn(new Column('content', 'mediumtext', true))
    ->ensureColumn(new Column('active', 'tinyint(1)', true))
    ->ensureGlobalColumns()
    ->ensureColumn(new Column('attributes', 'text', true))
    ->setPrimaryKey('id')
    ->ensureIndex(new Index('key', ['key'], Index::UNIQUE))
    ->ensure();

$sql = Sql::factory();
$sql->setQuery('UPDATE ' . Core::getTablePrefix() . 'article_slice set revision=0 where revision<1 or revision IS NULL');
$sql->setQuery('SELECT 1 FROM ' . Core::getTable('template') . ' LIMIT 1');
if (!$sql->getRows()) {
    $sql
        ->setTable(Core::getTable('template'))
        ->setValue('id', 1)
        ->setValue('name', 'Default')
        ->setValue('content', 'REX_ARTICLE[]')
        ->setValue('active', 1)
        ->setValue('attributes', '{"ctype":[],"modules":{"1":{"all":"1"}},"categories":{"all":"1"}}')
        ->addGlobalCreateFields()
        ->addGlobalUpdateFields()
        ->insert();
}

Table::get(Core::getTable('cronjob'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new Column('name', 'varchar(255)', true))
    ->ensureColumn(new Column('description', 'varchar(255)', true))
    ->ensureColumn(new Column('type', 'varchar(255)', true))
    ->ensureColumn(new Column('parameters', 'text', true))
    ->ensureColumn(new Column('interval', 'text'))
    ->ensureColumn(new Column('nexttime', 'datetime', true))
    ->ensureColumn(new Column('environment', 'varchar(255)'))
    ->ensureColumn(new Column('execution_moment', 'tinyint(1)'))
    ->ensureColumn(new Column('execution_start', 'datetime'))
    ->ensureColumn(new Column('status', 'tinyint(1)'))
    ->ensureGlobalColumns()
    ->ensure();

Table::get(Core::getTable('media'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new Column('category_id', 'int(10) unsigned'))
    ->ensureColumn(new Column('filetype', 'varchar(255)', true))
    ->ensureColumn(new Column('filename', 'varchar(255)', true))
    ->ensureColumn(new Column('originalname', 'varchar(255)', true))
    ->ensureColumn(new Column('filesize', 'varchar(255)', true))
    ->ensureColumn(new Column('width', 'int(10) unsigned', true))
    ->ensureColumn(new Column('height', 'int(10) unsigned', true))
    ->ensureColumn(new Column('title', 'varchar(255)', true))
    ->ensureGlobalColumns()
    ->ensureIndex(new Index('category_id', ['category_id']))
    ->ensureIndex(new Index('filename', ['filename'], Index::UNIQUE))
    ->ensure();

Table::get(Core::getTable('media_category'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new Column('name', 'varchar(255)'))
    ->ensureColumn(new Column('parent_id', 'int(10) unsigned'))
    ->ensureColumn(new Column('path', 'varchar(255)'))
    ->ensureGlobalColumns()
    ->ensureIndex(new Index('parent_id', ['parent_id']))
    ->ensure();

Table::get(Core::getTable('media_manager_type'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new Column('status', 'tinyint(1) unsigned', false, '0'))
    ->ensureColumn(new Column('name', 'varchar(255)'))
    ->ensureColumn(new Column('description', 'varchar(255)'))
    ->ensureIndex(new Index('name', ['name'], Index::UNIQUE))
    ->ensureGlobalColumns()
    ->ensure();

Table::get(Core::getTable('media_manager_type_effect'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new Column('type_id', 'int(10) unsigned'))
    ->ensureColumn(new Column('effect', 'varchar(255)'))
    ->ensureColumn(new Column('parameters', 'text'))
    ->ensureColumn(new Column('priority', 'int(10) unsigned'))
    ->ensureGlobalColumns()
    ->ensure();

$data = [
    ['id' => 1, 'name' => 'rex_media_small', 'description' => '200 × 200 px'],
    ['id' => 2, 'name' => 'rex_media_medium', 'description' => '600 × 600 px'],
    ['id' => 3, 'name' => 'rex_media_large', 'description' => '1200 × 1200 px'],
];

$sql = Sql::factory();
$sql->setTable(Core::getTable('media_manager_type'));

foreach ($data as $row) {
    $sql->addRecord(static function (Sql $record) use ($row) {
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

$sql = Sql::factory();
$sql->setTable(Core::getTable('media_manager_type_effect'));

foreach ($data as $row) {
    $sql->addRecord(static function (Sql $record) use ($row) {
        $record
            ->setValues($row)
            ->setValue('priority', 1)
            ->addGlobalCreateFields()
            ->addGlobalUpdateFields();
    });
}

$sql->insertOrUpdate();

Table::get(Core::getTable('metainfo_type'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new Column('label', 'varchar(255)', true))
    ->ensureColumn(new Column('dbtype', 'varchar(255)'))
    ->ensureColumn(new Column('dblength', 'int(11)'))
    ->ensure();

Table::get(Core::getTable('metainfo_field'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new Column('title', 'varchar(255)', true))
    ->ensureColumn(new Column('name', 'varchar(255)', true))
    ->ensureColumn(new Column('priority', 'int(10) unsigned'))
    ->ensureColumn(new Column('attributes', 'text'))
    ->ensureColumn(new Column('type_id', 'int(10) unsigned', true))
    ->ensureColumn(new Column('default', 'varchar(255)'))
    ->ensureColumn(new Column('params', 'text', true))
    ->ensureColumn(new Column('validate', 'text', true))
    ->ensureColumn(new Column('callback', 'text', true))
    ->ensureColumn(new Column('restrictions', 'text', true))
    ->ensureColumn(new Column('templates', 'text', true))
    ->ensureGlobalColumns()
    ->ensureIndex(new Index('name', ['name'], Index::UNIQUE))
    ->ensure();

Table::get(Core::getTable('user'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new Column('name', 'varchar(255)', true))
    ->ensureColumn(new Column('description', 'text', true))
    ->ensureColumn(new Column('login', 'varchar(50)'))
    ->ensureColumn(new Column('password', 'varchar(255)', true))
    ->ensureColumn(new Column('email', 'varchar(255)', true))
    ->ensureColumn(new Column('status', 'tinyint(1)'))
    ->ensureColumn(new Column('admin', 'tinyint(1)', false, '0'))
    ->ensureColumn(new Column('language', 'varchar(255)', true))
    ->ensureColumn(new Column('startpage', 'varchar(255)', true))
    ->ensureColumn(new Column('role', 'text', true))
    ->ensureColumn(new Column('theme', 'varchar(255)', true))
    ->ensureColumn(new Column('login_tries', 'tinyint(4)', false, '0'))
    ->ensureGlobalColumns()
    ->ensureColumn(new Column('password_changed', 'datetime'))
    ->ensureColumn(new Column('previous_passwords', 'text'))
    ->ensureColumn(new Column('password_change_required', 'tinyint(1)', false, '0'))
    ->ensureColumn(new Column('lasttrydate', 'datetime', true))
    ->ensureColumn(new Column('lastlogin', 'datetime', true))
    ->ensureColumn(new Column('session_id', 'varchar(255)', true))
    ->ensureIndex(new Index('login', ['login'], Index::UNIQUE))
    ->removeColumn('cookiekey')
    ->ensure();

Table::get(Core::getTable('user_passkey'))
    ->ensureColumn(new Column('id', 'varchar(255)'))
    ->ensureColumn(new Column('user_id', 'int(10) unsigned'))
    ->ensureColumn(new Column('public_key', 'text'))
    ->ensureColumn(new Column('createdate', 'datetime'))
    ->setPrimaryKey('id')
    ->ensureForeignKey(new ForeignKey(Core::getTable('user_passkey') . '_user_id', Core::getTable('user'), ['user_id' => 'id'], ForeignKey::CASCADE, ForeignKey::CASCADE))
    ->ensure();

Table::get(Core::getTable('user_role'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new Column('name', 'varchar(255)', true))
    ->ensureColumn(new Column('description', 'text', true))
    ->ensureColumn(new Column('perms', 'text'))
    ->ensureGlobalColumns()
    ->ensure();

Table::get(Core::getTable('user_session'))
    ->ensureColumn(new Column('session_id', 'varchar(255)'))
    ->ensureColumn(new Column('user_id', 'int(10) unsigned'))
    ->ensureColumn(new Column('cookie_key', 'varchar(255)', true))
    ->ensureColumn(new Column('passkey_id', 'varchar(255)', true))
    ->ensureColumn(new Column('ip', 'varchar(39)')) // max for ipv6
    ->ensureColumn(new Column('useragent', 'varchar(255)'))
    ->ensureColumn(new Column('starttime', 'datetime'))
    ->ensureColumn(new Column('last_activity', 'datetime'))
    ->setPrimaryKey('session_id')
    ->ensureIndex(new Index('cookie_key', ['cookie_key'], Index::UNIQUE))
    ->ensureForeignKey(new ForeignKey(Core::getTable('user_session') . '_user_id', Core::getTable('user'), ['user_id' => 'id'], ForeignKey::CASCADE, ForeignKey::CASCADE))
    ->ensureForeignKey(new ForeignKey(Core::getTable('user_session') . '_passkey_id', Core::getTable('user_passkey'), ['passkey_id' => 'id'], ForeignKey::CASCADE, ForeignKey::CASCADE))
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
    if (!Core::hasConfig($key)) {
        Core::setConfig($key, $value);
    }
}

$data = [
    ['id' => rex_metainfo_default_type::TEXT, 'label' => 'text', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => rex_metainfo_default_type::TEXTAREA, 'label' => 'textarea', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => rex_metainfo_default_type::SELECT, 'label' => 'select', 'dbtype' => 'varchar', 'dblength' => 255],
    ['id' => rex_metainfo_default_type::RADIO, 'label' => 'radio', 'dbtype' => 'varchar', 'dblength' => 255],
    ['id' => rex_metainfo_default_type::CHECKBOX, 'label' => 'checkbox', 'dbtype' => 'varchar', 'dblength' => 255],
    [
        'id' => rex_metainfo_default_type::REX_MEDIA_WIDGET,
        'label' => 'REX_MEDIA_WIDGET',
        'dbtype' => 'varchar',
        'dblength' => 255,
    ],
    [
        'id' => rex_metainfo_default_type::REX_MEDIALIST_WIDGET,
        'label' => 'REX_MEDIALIST_WIDGET',
        'dbtype' => 'text',
        'dblength' => 0,
    ],
    [
        'id' => rex_metainfo_default_type::REX_LINK_WIDGET,
        'label' => 'REX_LINK_WIDGET',
        'dbtype' => 'varchar',
        'dblength' => 255,
    ],
    [
        'id' => rex_metainfo_default_type::REX_LINKLIST_WIDGET,
        'label' => 'REX_LINKLIST_WIDGET',
        'dbtype' => 'text',
        'dblength' => 0,
    ],
    ['id' => rex_metainfo_default_type::DATE, 'label' => 'date', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => rex_metainfo_default_type::DATETIME, 'label' => 'datetime', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => rex_metainfo_default_type::LEGEND, 'label' => 'legend', 'dbtype' => 'text', 'dblength' => 0],
    ['id' => rex_metainfo_default_type::TIME, 'label' => 'time', 'dbtype' => 'text', 'dblength' => 0],
    // XXX neue konstanten koennen hier nicht verwendet werden, da die updates mit der vorherigen version der klasse ausgefuehrt werden
];

$sql = Sql::factory();
$sql->setTable(Core::getTable('metainfo_type'));
foreach ($data as $row) {
    $sql->addRecord(static function (Sql $record) use ($row) {
        $record->setValues($row);
    });
}
$sql->insertOrUpdate();

$tablePrefixes = ['article' => ['art_', 'cat_'], 'media' => ['med_'], 'clang' => ['clang_']];
$columns = ['article' => [], 'media' => [], 'clang' => []];
foreach ($tablePrefixes as $table => $prefixes) {
    foreach (Sql::showColumns(Core::getTable($table)) as $column) {
        $column = $column['name'];
        if (in_array(substr($column, 0, 4), $prefixes)) {
            $columns[$table][$column] = true;
        }
    }
}

$sql = Sql::factory();
$sql->setQuery('SELECT p.name, p.default, t.dbtype, t.dblength FROM ' . Core::getTable('metainfo_field') . ' p, ' . Core::getTable('metainfo_type') . ' t WHERE p.type_id = t.id');
$managers = [
    'article' => new rex_metainfo_table_manager(Core::getTable('article')),
    'media' => new rex_metainfo_table_manager(Core::getTable('media')),
    'clang' => new rex_metainfo_table_manager(Core::getTable('clang')),
];
for ($i = 0; $i < $sql->getRows(); ++$i) {
    $column = (string) $sql->getValue('name');
    if (str_starts_with($column, 'med_')) {
        $table = 'media';
    } elseif (str_starts_with($column, 'clang_')) {
        $table = 'clang';
    } else {
        $table = 'article';
    }

    $default = $sql->getValue('default');
    $default = null === $default ? $default : (string) $default;

    if (isset($columns[$table][$column])) {
        $managers[$table]->editColumn($column, $column, (string) $sql->getValue('dbtype'), (int) $sql->getValue('dblength'), $default);
    } else {
        $managers[$table]->addColumn($column, (string) $sql->getValue('dbtype'), (int) $sql->getValue('dblength'), $default);
    }

    unset($columns[$table][$column]);
    $sql->next();
}
