<?php

/**
 * @package redaxo5
 */

$subpage = rex_be_controller::getCurrentPagePart(2);

$media_method = rex_request('media_method', 'string');
$media_name = rex_request('media_name', 'string');
$csrf = rex_csrf_token::factory('mediapool');

// *************************************** CONFIG

$media_manager = rex_addon::get('media_manager')->isAvailable();

// *************************************** KATEGORIEN CHECK UND AUSWAHL

$sel_media = new rex_media_category_select($check_perm = false);
$sel_media->setId('rex_file_category');
$sel_media->setName('rex_file_category');
$sel_media->setSize(1);
$sel_media->setSelected($rex_file_category);
$sel_media->setAttribute('onchange', 'this.form.submit();');
$sel_media->setAttribute('class', 'selectpicker');
$sel_media->setAttribute('data-live-search', 'true');

if (rex::getUser()->getComplexPerm('media')->hasAll()) {
    $sel_media->addOption(rex_i18n::msg('pool_kats_no'), '0');
}

// ----- EXTENSION POINT
echo rex_extension::registerPoint(new rex_extension_point('PAGE_MEDIAPOOL_HEADER', '', [
    'subpage' => $subpage,
    'category_id' => $rex_file_category,
]));

$formElements = [];
$n = [];
$n['field'] = '<input class="form-control" type="text" name="media_name" id="be_search-media-name" value="' . rex_escape($media_name, 'html_attr') . '" />';
$n['before'] = $sel_media->get();
$n['right'] = '<button class="btn btn-search" type="submit"><i class="rex-icon rex-icon-search"></i></button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$toolbar = '<div class="rex-truncate-dropdown">' . $fragment->parse('core/form/input_group.php') . '</div>';

$toolbar = '
<div class="navbar-form navbar-right">
<form action="' . rex_url::currentBackendPage() . '" method="post">
    ' . $arg_fields . '
    <div class="form-group">
    ' . $toolbar . '
    </div>
</form>
</div>';

$context = new rex_context([
    'page' => rex_be_controller::getCurrentPage(),
]);

// ----- EXTENSION POINT
$toolbar = rex_extension::registerPoint(new rex_extension_point('MEDIA_LIST_TOOLBAR', $toolbar, [
    'subpage' => $subpage,
    'category_id' => $rex_file_category,
]));

// *************************************** Subpage: Media

if ($file_id) {
    require __DIR__ .'/media.detail.php';
}

// *************************************** EXTRA FUNCTIONS

$hasCategoryPerm = rex::getUser()->getComplexPerm('media')->hasCategoryPerm($rex_file_category);

if ($hasCategoryPerm && $media_method == 'updatecat_selectedmedia') {
    if (!$csrf->isValid()) {
        $error = rex_i18n::msg('csrf_token_invalid');
    } else {
        $selectedmedia = rex_post('selectedmedia', 'array');
        if (isset($selectedmedia[0]) && $selectedmedia[0] != '') {
            foreach ($selectedmedia as $file_name) {
                $db = rex_sql::factory();
                // $db->setDebug();
                $db->setTable(rex::getTablePrefix() . 'media');
                $db->setWhere(['filename' => $file_name]);
                $db->setValue('category_id', $rex_file_category);
                $db->addGlobalUpdateFields();
                try {
                    $db->update();
                    $success = rex_i18n::msg('pool_selectedmedia_moved');
                    rex_media_cache::delete($file_name);
                } catch (rex_sql_exception $e) {
                    $error = rex_i18n::msg('pool_selectedmedia_error');
                }
            }
        } else {
            $error = rex_i18n::msg('pool_selectedmedia_error');
        }
    }
}

if ($hasCategoryPerm && $media_method == 'delete_selectedmedia') {
    if (!$csrf->isValid()) {
        $error = rex_i18n::msg('csrf_token_invalid');
    } else {
        $selectedmedia = rex_post('selectedmedia', 'array');
        if (count($selectedmedia) != 0) {
            $error = [];
            $success = [];

            $countDeleted = 0;
            foreach ($selectedmedia as $file_name) {
                $media = rex_media::get($file_name);
                if ($media) {
                    if (rex::getUser()->getComplexPerm('media')->hasCategoryPerm($media->getCategoryId())) {
                        $return = rex_mediapool_deleteMedia($file_name);
                        if ($return['ok']) {
                            ++$countDeleted;
                        } else {
                            $error[] = $return['msg'];
                        }
                    } else {
                        $error[] = rex_i18n::msg('no_permission');
                    }
                } else {
                    $error[] = rex_i18n::msg('pool_file_not_found');
                }
            }
            if ($countDeleted) {
                $success[] = rex_i18n::msg('pool_files_deleted', $countDeleted);
            }
        } else {
            $error = rex_i18n::msg('pool_selectedmedia_error');
        }
    }
}

// *************************************** SUBPAGE: "" -> MEDIEN ANZEIGEN

if (!$file_id) {
    require __DIR__.'/media.list.php';
}
