<?php

$content = '';

$Basedir = __DIR__;

$type_id = rex_request('type_id', 'int');
$func = rex_request('func', 'string');

if (rex_request('effects', 'boolean')) {
    include __DIR__ . '/effects.php';
    return;
}

$success = '';
$error = '';

//-------------- delete cache on type_name change or type deletion
if ((rex_request('func') == 'edit' || $func == 'delete') && $type_id > 0) {
    $counter = rex_media_manager::deleteCacheByType($type_id);
    //  $info = rex_i18n::msg('media_manager_cache_files_removed', $counter);
}

//-------------- delete type
if ($func == 'delete' && $type_id > 0) {
    $sql = rex_sql::factory();
    //  $sql->setDebug();

    try {
        $sql->transactional(function () use ($sql, $type_id) {
            $sql->setTable(rex::getTablePrefix() . 'media_manager_type');
            $sql->setWhere(['id' => $type_id]);
            $sql->delete();

            $sql->setTable(rex::getTablePrefix() . 'media_manager_type_effect');
            $sql->setWhere(['type_id' => $type_id]);
            $sql->delete();
        });

        $success = rex_i18n::msg('media_manager_type_deleted');
    } catch (rex_sql_exception $e) {
        $error = $sql->getError();
    }
    $func = '';
}

//-------------- delete cache by type-id
if ($func == 'delete_cache' && $type_id > 0) {
    $counter = rex_media_manager::deleteCacheByType($type_id);
    $success = rex_i18n::msg('media_manager_cache_files_removed', $counter);
    $func = '';
}

//-------------- copy type
if ($func == 'copy' && $type_id > 0) {
    $sql = rex_sql::factory();

    try {
        $sql->setQuery('INSERT INTO '.rex::getTablePrefix() . 'media_manager_type (status, name, description) SELECT 0, CONCAT(name, \' '.rex_i18n::msg('media_manager_type_name_copy').'\'), description FROM '.rex::getTablePrefix() . 'media_manager_type WHERE id = ?', [$type_id]);
        $newTypeId = $sql->getLastId();
        $sql->setQuery('INSERT INTO '.rex::getTablePrefix() . 'media_manager_type_effect (type_id, effect, parameters, priority, updatedate, updateuser, createdate, createuser) SELECT ?, effect, parameters, priority, ?, ?, ?, ? FROM '.rex::getTablePrefix() . 'media_manager_type_effect WHERE type_id = ?', [$newTypeId, date('Y-m-d H:i:s'), rex::getUser()->getLogin(), date('Y-m-d H:i:s'), rex::getUser()->getLogin(), $type_id]);

        $success = rex_i18n::msg('media_manager_type_copied');
    } catch (rex_sql_exception $e) {
        $error = $sql->getError();
    }

    $func = '';
}

//-------------- output messages
if ($success != '') {
    echo rex_view::success($success);
}

if ($error != '') {
    echo rex_view::error($error);
}

if ($func == '') {
    echo '<style>
<!--[if IE ]><style>.rex-media-manager-list{table-layout: fixed;}</style><![endif]-->
<style>
.rex-helper-line dt,dd{display:inline-block;}
.rex-helper-line dt{width:65%;padding-right:3%;vertical-align:top;}
.rex-helper-line dd{width:35%;}
.rex-helper-line td{border-top:0px!important;}
.rex-helper-line .rex-mediamanager-list-effect{display:-ms-flexbox;display:flex;-ms-flex-flow:row wrap;flex-wrap:wrap;}
.rex-helper-line .rex-mediamanager-list-effect > div{width:20em;margin:5px 10px 5px 0;}
.rex-helper-line .rex-mediamanager-list-effect .panel-heading{padding:5px 15px; background-color: transparent; }
.rex-helper-line .rex-mediamanager-list-effect .panel-body{padding:0 15px;}
.rex-helper-line .rex-mediamanager-list-effect dl{padding:0;margin:0}
</style>
<script>
function rex_mediamanager_toggle(dieses){
    var dies = jQuery(dieses);
    var das = jQuery(dies.data("target"));

    if( dies.hasClass("active") ) {
        dies.removeClass("active");
        das.addClass("hidden");
    } else {
        dies.addClass("active");
        das.removeClass("hidden");
    }
}
function rex_mediamanager_toggleAll(dieses){
    var dies = jQuery(dieses);
    var das = jQuery(dies.data("target"));
    var btn = jQuery(dies.data("button"));

    if( dies.hasClass("active") ) {
        dies.removeClass("active");
        das.addClass("hidden");
        btn.removeClass("active");
    } else {
        dies.addClass("active");
        das.removeClass("hidden");
        btn.addClass("active");
    }
}
</script>';
    // Nach Status sortieren, damit Systemtypen immer zuletzt stehen
    // (werden am seltesten bearbeitet)
    $query = 'SELECT * FROM ' . rex::getTablePrefix() . 'media_manager_type ORDER BY status, name';

    $list = rex_list::factory($query);
    $list->addTableAttribute('class', 'table-striped rex-media-manager-list');
    $list->setNoRowsMessage(rex_i18n::msg('media_manager_type_no_types'));

    $list->removeColumn('id');
    $list->removeColumn('status');
    $list->removeColumn('description');

    $list->setColumnLabel('name', rex_i18n::msg('media_manager_type_name'));
    $list->setColumnFormat('name', 'custom', function ($params) {
        $list = $params['list'];
        $name = '<b>' . rex_escape($list->getValue('name')) . '</b>';
        $name .= '<div class="pull-right">
            <button
                onclick="rex_mediamanager_toggle(this)"
                type="button"
                class="btn btn btn-xs rex-mediamanager-list-effect-btn"
                data-target=".rex-mediamanager-list-'.rex_escape($list->getValue('name'), 'html_attr').'-effect .panel-body">
                <i class="rex-icon  fa-search-plus"></i>
            </button>&nbsp;
            <button
                onclick="rex_mediamanager_toggle(this)"
                type="button"
                class="btn btn btn-xs rex-mediamanager-list-link-btn"
                data-target=".rex-mediamanager-list-'.rex_escape($list->getValue('name'), 'html_attr').'-link">
                <i class="rex-icon fa-link"></i>
            </button>
            </div>';
        $name .= ($list->getValue('description') != '') ? '<br /><span class="rex-note">' . rex_escape($list->getValue('description')) . '</span>' : '';
        return $name;
    });

    // icon column
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="' . rex_i18n::msg('media_manager_type_create') . '"><i class="rex-icon rex-icon-add-mediatype"></i></a>';
    $tdIcon = '<i class="rex-icon rex-icon-mediatype"></i>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'type_id' => '###id###']);

    // functions column spans 5 data-columns
    $funcs = rex_i18n::msg('media_manager_type_functions');

    $list->addColumn($funcs, '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('media_manager_type_effekts_edit'), -1, ['<th class="rex-table-action" colspan="5">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams($funcs, ['type_id' => '###id###', 'effects' => 1]);

    $list->addColumn('deleteCache', '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('media_manager_type_cache_delete'), -1, ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('deleteCache', ['type_id' => '###id###', 'func' => 'delete_cache']);
    $list->addLinkAttribute('deleteCache', 'data-confirm', rex_i18n::msg('media_manager_type_cache_delete') . ' ?');

    $list->addColumn('editType', '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('media_manager_type_edit'), -1, ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('editType', ['func' => 'edit', 'type_id' => '###id###']);

    $list->addColumn('copyType', '<i class="rex-icon rex-icon-duplicate"></i> ' . rex_i18n::msg('media_manager_type_copy'), -1, ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('copyType', ['func' => 'copy', 'type_id' => '###id###']);

    // remove delete link on internal types (status == 1)
    $list->addColumn('deleteType', '', -1, ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('deleteType', ['type_id' => '###id###', 'func' => 'delete']);
    $list->addLinkAttribute('deleteType', 'data-confirm', rex_i18n::msg('delete') . ' ?');
    $list->setColumnFormat('deleteType', 'custom', function ($params) {
        $list = $params['list'];
        $qry = 'SELECT effect,parameters FROM '.rex::getTable('media_manager_type_effect').' WHERE type_id=? ORDER BY priority';
        $effects = rex_sql::factory()->getArray($qry, [$params['list']->getValue('id')]);
        foreach ($effects as $k => $v) {
            $effectClass = "rex_effect_{$v['effect']}";
            $effectParams = json_decode($v['parameters'], true);
            $instance = new $effectClass();
            $effectLabels = [];
            if (isset($effectParams[$effectClass])) {
                $effectParams = $effectParams[$effectClass];
                $effectLabels = array_column($instance->getParams(), 'name', 'label');
                foreach ($effectLabels as $ek => $ev) {
                    $value = "{$effectClass}_$ev";
                    $effectLabels[$ek] = isset($effectParams[$value]) ? $effectParams[$value] : '?';
                }
            }
            $effects[$k] = ['label' => $instance->getName(), 'effects' => $effectLabels];
        }
        $fragment = new rex_fragment();
        $fragment->setVar('content', $effects, false);
        $zusatzzeile = '</td></tr><tr class="rex-helper-line"><td></td><td colspan="6">'.
                       '<div class="hidden rex-mediamanager-list-link rex-mediamanager-list-'.rex_escape($list->getValue('name'), 'html_attr').'-link">Link: <i>index.php?rex_media_type='.rex_escape($list->getValue('name'), 'html_attr').'&rex_media_file=</i></div>'.
                       '<div class="rex-mediamanager-list-effect rex-mediamanager-list-'.rex_escape($list->getValue('name'), 'html_attr').'-effect">'.$fragment->parse('mmeffectslist.php').'</div>';
        if ($list->getValue('status') == 1) {
            return '<small class="text-muted">' . rex_i18n::msg('media_manager_type_system') . '</small>'.$zusatzzeile;
        }
        return $list->getColumnLink('deleteType', '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('media_manager_type_delete')).$zusatzzeile;
    });

    $content .= $list->get();

    $button = '<div class="pull-right">
    <button
        onclick="rex_mediamanager_toggleAll(this)"
        type="button"
        class="btn btn btn-xs"
        data-target=".rex-mediamanager-list-effect .panel-body"
        data-button=".rex-mediamanager-list-effect-btn">
        <i class="rex-icon fa-search-plus"></i>
    </button>&nbsp;
    <button
        onclick="rex_mediamanager_toggleAll(this)"
        type="button"
        class="btn btn btn-xs"
        data-target=".rex-mediamanager-list-link"
        data-button=".rex-mediamanager-list-link-btn">
        <i class="rex-icon fa-link"></i>
    </button>
    </div>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('media_manager_type_caption').$button, false);
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;
} elseif ($func == 'add' || $func == 'edit' && $type_id > 0) {
    if ($func == 'edit') {
        $formLabel = rex_i18n::msg('media_manager_type_edit');
    } elseif ($func == 'add') {
        $formLabel = rex_i18n::msg('media_manager_type_create');
    }

    rex_extension::register('REX_FORM_CONTROL_FIELDS', function (rex_extension_point $ep) {
        $controlFields = $ep->getSubject();
        $form = $ep->getParam('form');
        $sql = $form->getSql();

        // remove delete button on internal types (status == 1)
        if ($sql->getRows() > 0 && $sql->hasValue('status') && $sql->getValue('status') == 1) {
            $controlFields['delete'] = '';
        }
        return $controlFields;
    });

    $form = rex_form::factory(rex::getTablePrefix() . 'media_manager_type', '', 'id = ' . $type_id);
    $form->addParam('type_id', $type_id);
    if ($func == 'edit') {
        $form->setEditMode($func == 'edit');
    }

    $form->addErrorMessage(REX_FORM_ERROR_VIOLATE_UNIQUE_KEY, rex_i18n::msg('media_manager_error_type_name_not_unique'));

    $field = $form->addTextField('name');
    $field->setLabel(rex_i18n::msg('media_manager_type_name'));
    $field->setAttribute('maxlength', 255);
    $field->getValidator()
        ->add('notEmpty', rex_i18n::msg('media_manager_error_name'))
        ->add('notMatch', rex_i18n::msg('media_manager_error_type_name_invalid'), '{[/\\\\]}');

    $field = $form->addTextareaField('description');
    $field->setLabel(rex_i18n::msg('media_manager_type_description'));
    $field->setAttribute('maxlength', 255);

    $content .= $form->get();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $formLabel, false);
    $fragment->setVar('body', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;
}
