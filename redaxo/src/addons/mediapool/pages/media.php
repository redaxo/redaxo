<?php

/**
 * @package redaxo5
 */

$subpage = rex_be_controller::getCurrentPagePart(2);

$media_method = rex_request('media_method', 'string');
$media_name = rex_request('media_name', 'string');

// *************************************** CONFIG

$media_manager = rex_addon::get('media_manager')->isAvailable();

// *************************************** KATEGORIEN CHECK UND AUSWAHL

// ***** kategorie auswahl
$db = rex_sql::factory();
$file_cat = $db->getArray('SELECT * FROM ' . rex::getTablePrefix() . 'media_category ORDER BY name ASC');

// ***** select bauen
$sel_media = new rex_media_category_select($check_perm = false);
$sel_media->setId('rex_file_category');
$sel_media->setName('rex_file_category');
$sel_media->setSize(1);
$sel_media->setSelected($rex_file_category);
$sel_media->setAttribute('onchange', 'this.form.submit();');
$sel_media->setAttribute('class', 'selectpicker');
$sel_media->addOption(rex_i18n::msg('pool_kats_no'), '0');

// ----- EXTENSION POINT
echo rex_extension::registerPoint(new rex_extension_point('PAGE_MEDIAPOOL_HEADER', '', [
    'subpage' => $subpage,
    'category_id' => $rex_file_category,
]));

$formElements = [];
$n = [];
$n['field'] = '<input class="form-control" type="text" name="media_name" id="be_search-media-name" value="' . htmlspecialchars($media_name) . '" />';
$n['before'] = $sel_media->get();
$n['right'] = '<button class="btn btn-search" type="submit"><i class="rex-icon rex-icon-search"></i></button>';
//$n['right'] = $dropdown;
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

/*
$view_buttons = [];

// symbol view
$button = [];
$button['url'] = $context->getUrl(['func' => 'media-view-icon']);
$button['attributes']['class'][] = 'btn-default';
$button['attributes']['title'] = rex_i18n::msg('mediapool_view_icon');
$button['icon'] = 'view-icon';
$view_buttons[] = $button;

// list view
$button = [];
$button['url'] = $context->getUrl(['func' => 'media-view-list']);
$button['attributes']['class'][] = 'btn-default';
$button['attributes']['title'] = rex_i18n::msg('mediapool_view_list');
$button['icon'] = 'view-list';
$view_buttons[] = $button;

if (count($view_buttons) > 0) {
    $fragment = new rex_fragment();
    $fragment->setVar('buttons', $view_buttons, false);
    $fragment->setVar('size', 'xs', false);
    $toolbar .= $fragment->parse('core/buttons/button_group.php');
}
*/
// ----- EXTENSION POINT
$toolbar = rex_extension::registerPoint(new rex_extension_point('MEDIA_LIST_TOOLBAR', $toolbar, [
    'subpage' => $subpage,
    'category_id' => $rex_file_category,
]));

// *************************************** Subpage: Media

if ($file_id && rex_post('btn_delete', 'string')) {
    $sql = rex_sql::factory()->setQuery('SELECT filename FROM ' . rex::getTable('media') . ' WHERE id = ?', [$file_id]);
    $media = null;
    if ($sql->getRows() == 1) {
        $media = rex_media::get($sql->getValue('filename'));
    }

    if ($media) {
        $filename = $media->getFileName();
        if ($PERMALL || rex::getUser()->getComplexPerm('media')->hasCategoryPerm($media->getCategoryId())) {
            $return = rex_mediapool_deleteMedia($filename);
            if ($return['ok']) {
                $success = $return['msg'];
            } else {
                $error = $return['msg'];
            }
            $file_id = 0;
        } else {
            $error = rex_i18n::msg('no_permission');
        }
    } else {
        $error = rex_i18n::msg('pool_file_not_found');
        $file_id = 0;
    }
}

if ($file_id && rex_post('btn_update', 'string')) {
    $gf = rex_sql::factory();
    $gf->setQuery('select * from ' . rex::getTablePrefix() . 'media where id=?', [$file_id]);
    if ($gf->getRows() == 1) {
        if ($PERMALL || (rex::getUser()->getComplexPerm('media')->hasCategoryPerm($gf->getValue('category_id')) && rex::getUser()->getComplexPerm('media')->hasCategoryPerm($rex_file_category))) {
            $FILEINFOS = [];
            $FILEINFOS['rex_file_category'] = $rex_file_category;
            $FILEINFOS['file_id'] = $file_id;
            $FILEINFOS['title'] = rex_request('ftitle', 'string');
            $FILEINFOS['filetype'] = $gf->getValue('filetype');
            $FILEINFOS['filename'] = $gf->getValue('filename');

            $return = rex_mediapool_updateMedia($_FILES['file_new'], $FILEINFOS, rex::getUser()->getValue('login'));

            if ($return['ok'] == 1) {
                $success = $return['msg'];
                // ----- EXTENSION POINT
                 // rex_extension::registerPoint(new rex_extension_point('MEDIA_UPDATED','',array('id' => $file_id, 'type' => $FILEINFOS["filetype"], 'filename' => $FILEINFOS["filename"] )));
                 rex_extension::registerPoint(new rex_extension_point('MEDIA_UPDATED', '', $return));
            } else {
                $error = $return['msg'];
            }
        } else {
            $error = rex_i18n::msg('no_permission');
        }
    } else {
        $error = rex_i18n::msg('pool_file_not_found');
        $file_id = 0;
    }
}

if ($file_id) {
    $gf = rex_sql::factory();
    $gf->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media WHERE id = ?', [$file_id]);
    if ($gf->getRows() == 1) {
        $TPERM = false;
        if ($PERMALL || rex::getUser()->hasPerm('media[' . $gf->getValue('category_id') . ']')) {
            $TPERM = true;
        }

        $ftitle = $gf->getValue('title');
        $fname = $gf->getValue('filename');
        $ffiletype = $gf->getValue('filetype');
        $ffile_size = $gf->getValue('filesize');
        $ffile_size = rex_formatter::bytes($ffile_size);
        $rex_file_category = $gf->getValue('category_id');

        $isImage = rex_media::isImageType(rex_file::extension($fname));
        if ($isImage) {
            $fwidth = $gf->getValue('width');
            $fheight = $gf->getValue('height');

            if ($fwidth > 199) {
                $rfwidth = 200;
            } else {
                $rfwidth = $fwidth;
            }
        }

        $add_image = '';
        $add_ext_info = '';
        $encoded_fname = urlencode($fname);
        if ($isImage) {
            $e = [];
            $e['label'] = '<label>' . rex_i18n::msg('pool_img_width') . ' / ' . rex_i18n::msg('pool_img_height') . '</label>';
            $e['field'] = '<p class="form-control-static">' . $fwidth . 'px / ' . $fheight . 'px</p>';

            $fragment = new rex_fragment();
            $fragment->setVar('elements', [$e], false);
            $add_ext_info = $fragment->parse('core/form/form.php');

            $imgn = rex_url::media($fname) . '" width="' . $rfwidth;
            $img_max = rex_url::media($fname);

            if ($media_manager && rex_file::extension($fname) != 'svg') {
                $imgn = rex_url::backendController(['rex_media_type' => 'rex_mediapool_detail', 'rex_media_file' => $encoded_fname]);
                $img_max = rex_url::backendController(['rex_media_type' => 'rex_mediapool_maximized', 'rex_media_file' => $encoded_fname]);
            }

            if (!file_exists(rex_path::media($fname))) {
                $add_image = '<i class="rex-mime rex-mime-error"></i><span class="sr-only">' . $fname . '</span>';
            } else {
                $add_image = '
                        <a href="' . $img_max . '">
                            <img class="img-responsive" src="' . $imgn . '" alt="' . htmlspecialchars($ftitle) . '" title="' . htmlspecialchars($ftitle) . '" />
                        </a>';
            }
        }

        if ($error != '') {
            echo rex_view::error($error);
            $error = '';
        }
        if ($success != '') {
            echo rex_view::success($success);
            $success = '';
        }

        if ($opener_input_field != '') {
            $opener_link = '<a class="btn btn-xs btn-select" href="javascript:selectMedia(\'' . $encoded_fname . '\', \'' . addslashes(htmlspecialchars($gf->getValue('title'))) . '\');">' . rex_i18n::msg('pool_file_get') . '</a>';
            if (substr($opener_input_field, 0, 14) == 'REX_MEDIALIST_') {
                $opener_link = '<a class="btn btn-xs btn-select" href="javascript:selectMedialist(\'' . $encoded_fname . '\');">' . rex_i18n::msg('pool_file_get') . '</a>';
            }
        }

        if ($opener_link != '') {
            $opener_link = ' | ' . $opener_link;
        }

        if ($TPERM) {
            $panel = '';

            $cats_sel = new rex_media_category_select();
            $cats_sel->setStyle('class="form-control"');
            $cats_sel->setSize(1);
            $cats_sel->setName('rex_file_category');
            $cats_sel->setId('rex-mediapool-category');
            $cats_sel->addOption(rex_i18n::msg('pool_kats_no'), '0');
            $cats_sel->setSelected($rex_file_category);

            $formElements = [];

            $e = [];
            $e['label'] = '<label for="rex-mediapool-title">' . rex_i18n::msg('pool_file_title') . '</label>';
            $e['field'] = '<input class="form-control" type="text" id="rex-mediapool-title" name="ftitle" value="' . htmlspecialchars($ftitle) . '" />';
            $formElements[] = $e;

            $e = [];
            $e['label'] = '<label for="rex-mediapool-category">' . rex_i18n::msg('pool_file_category') . '</label>';
            $e['field'] = $cats_sel->get();
            $formElements[] = $e;

            $fragment = new rex_fragment();
            $fragment->setVar('elements', $formElements, false);
            $panel .= $fragment->parse('core/form/form.php');

            $panel .= rex_extension::registerPoint(new rex_extension_point('MEDIA_FORM_EDIT', '', ['id' => $file_id, 'media' => $gf]));

            $panel .= $add_ext_info;

            $formElements = [];

            $e = [];
            $e['label'] = '<label>' . rex_i18n::msg('pool_filename') . '</label>';
            $e['field'] = '<p class="form-control-static"><a href="' . rex_url::media($encoded_fname) . '">' . htmlspecialchars($fname) . '</a> <span class="rex-filesize">' . $ffile_size . '</span></p>';
            $formElements[] = $e;

            $e = [];
            $e['label'] = '<label>' . rex_i18n::msg('pool_last_update') . '</label>';
            $e['field'] = '<p class="form-control-static">' . strftime(rex_i18n::msg('datetimeformat'), strtotime($gf->getValue('updatedate'))) . ' <span class="rex-author">' . $gf->getValue('updateuser') . '</span></p>';
            $formElements[] = $e;

            $e = [];
            $e['label'] = '<label>' . rex_i18n::msg('pool_created') . '</label>';
            $e['field'] = '<p class="form-control-static">' . strftime(rex_i18n::msg('datetimeformat'), strtotime($gf->getValue('createdate'))) . ' <span class="rex-author">' . $gf->getValue('createuser') . '</span></p>';
            $formElements[] = $e;

            $e = [];
            $e['label'] = '<label>' . rex_i18n::msg('pool_file_exchange') . '</label>';
            $e['field'] = '<input type="file" name="file_new" />';
            $formElements[] = $e;

            $fragment = new rex_fragment();
            $fragment->setVar('elements', $formElements, false);
            $panel .= $fragment->parse('core/form/form.php');

            $formElements = [];

            $e = [];
            $e['field'] = '<button class="btn btn-apply rex-form-aligned" type="submit" value="' . rex_i18n::msg('pool_file_update') . '" name="btn_update">' . rex_i18n::msg('pool_file_update') . '</button>';
            $formElements[] = $e;
            $e = [];
            $e['field'] = '<button class="btn btn-delete" type="submit" value="' . rex_i18n::msg('pool_file_delete') . '" name="btn_delete" data-confirm="' . rex_i18n::msg('delete') . ' ?">' . rex_i18n::msg('delete') . '</button>';
            $formElements[] = $e;

            $fragment = new rex_fragment();
            $fragment->setVar('elements', $formElements, false);
            $buttons = $fragment->parse('core/form/submit.php');

            if ($add_image != '') {
                $fragment = new rex_fragment();
                $fragment->setVar('content', [$panel, $add_image], false);
                $fragment->setVar('classes', ['col-sm-8', 'col-sm-4'], false);
                $panel = $fragment->parse('core/page/grid.php');
            }

            $body = '
                <form action="' . rex_url::currentBackendPage() . '" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="file_id" value="' . $file_id . '" />
                    ' . $arg_fields . '
                    ' . $panel . '
                    ' . $buttons . '
                </form>';

            $fragment = new rex_fragment();
            $fragment->setVar('class', 'edit', false);
            $fragment->setVar('title', rex_i18n::msg('pool_file_edit') . $opener_link, false);
            $fragment->setVar('options', $toolbar, false);
            $fragment->setVar('body', $body, false);
            $content = $fragment->parse('core/page/section.php');

            echo $content;
        } else {
            $panel = '';

            $catname = rex_i18n::msg('pool_kats_no');
            $Cat = rex_media_category::get($rex_file_category);
            if ($Cat) {
                $catname = $Cat->getName();
            }

            $ftitle .= ' [' . $file_id . ']';
            $catname .= ' [' . $rex_file_category . ']';

            $formElements = [];

            $e = [];
            $e['label'] = '<label>' . rex_i18n::msg('pool_file_title') . '</label>';
            $e['field'] = '<p class="form-control-static">' . htmlspecialchars($ftitle) . '</p>';
            $formElements[] = $e;

            $e = [];
            $e['label'] = '<label>' . rex_i18n::msg('pool_file_category') . '</label>';
            $e['field'] = '<p class="form-control-static">' . htmlspecialchars($catname) . '</p>';
            $formElements[] = $e;

            $e = [];
            $e['label'] = '<label>' . rex_i18n::msg('pool_filename') . '</label>';
            $e['field'] = '<p class="form-control-static"><a href="' . rex_url::media($encoded_fname) . '">' . $fname . '</a>  <span class="rex-filesize">' . $ffile_size . '</span></p>';
            $formElements[] = $e;

            $e = [];
            $e['label'] = '<label>' . rex_i18n::msg('pool_last_update') . '</label>';
            $e['field'] = '<p class="form-control-static">' . strftime(rex_i18n::msg('datetimeformat'), strtotime($gf->getValue('updatedate'))) . ' <span class="rex-author">' . $gf->getValue('updateuser') . '</span></p>';
            $formElements[] = $e;

            $e = [];
            $e['label'] = '<label>' . rex_i18n::msg('pool_created') . '</label>';
            $e['field'] = '<p class="form-control-static">' . strftime(rex_i18n::msg('datetimeformat'), strtotime($gf->getValue('createdate'))) . ' <span class="rex-author">' . $gf->getValue('createuser') . '</span></p>';
            $formElements[] = $e;

            $fragment = new rex_fragment();
            $fragment->setVar('elements', $formElements, false);
            $panel .= $fragment->parse('core/form/form.php');

            if ($add_image != '') {
                $fragment = new rex_fragment();
                $fragment->setVar('content', [$panel, $add_image], false);
                $fragment->setVar('classes', ['col-sm-8', 'col-sm-4'], false);
                $panel = $fragment->parse('core/page/grid.php');
            }

            $fragment = new rex_fragment();
            $fragment->setVar('title', rex_i18n::msg('pool_file_details') . $opener_link, false);
            $fragment->setVar('options', $toolbar, false);
            $fragment->setVar('body', $panel, false);
            $content = $fragment->parse('core/page/section.php');

            echo $content;
        }
    } else {
        $error = rex_i18n::msg('pool_file_not_found');
        $file_id = 0;
    }
}

// *************************************** EXTRA FUNCTIONS

if ($PERMALL && $media_method == 'updatecat_selectedmedia') {
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

if ($PERMALL && $media_method == 'delete_selectedmedia') {
    $selectedmedia = rex_post('selectedmedia', 'array');
    if (count($selectedmedia) != 0) {
        $error = [];
        $success = [];

        $countDeleted = 0;
        foreach ($selectedmedia as $file_name) {
            $media = rex_media::get($file_name);
            if ($media) {
                if ($PERMALL || rex::getUser()->getComplexPerm('media')->hasCategoryPerm($media->getCategoryId())) {
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

// *************************************** SUBPAGE: "" -> MEDIEN ANZEIGEN

if (!$file_id) {
    $cats_sel = new rex_media_category_select();
    $cats_sel->setSize(1);
    $cats_sel->setStyle('class="form-control selectpicker"');
    $cats_sel->setName('rex_file_category');
    $cats_sel->setId('rex_file_category');
    $cats_sel->addOption(rex_i18n::msg('pool_kats_no'), '0');
    $cats_sel->setSelected($rex_file_category);

    if (is_array($error)) {
        if (count($error) > 0) {
            echo rex_view::error(implode('<br />', $error));
        }
        $error = '';
    } elseif ($error != '') {
        echo rex_view::error($error);
        $error = '';
    }

    if (is_array($success)) {
        if (count($success) > 0) {
            echo rex_view::success(implode('<br />', $success));
        }
        $success = '';
    } elseif ($success != '') {
        echo rex_view::success($success);
        $success = '';
    }

    if (!empty($args['types'])) {
        echo rex_view::info(rex_i18n::msg('pool_file_filter') . ' <code>' . $args['types'] . '</code>');
    }

    //deletefilelist und cat change
    $panel = '
             <form action="' . rex_url::currentBackendPage() . '" method="post" enctype="multipart/form-data">
                    <fieldset>

                        <input type="hidden" id="media_method" name="media_method" value="" />
                        ' . $arg_fields . '

                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="rex-table-icon"><a href="' . rex_url::backendController(array_merge(['page' => 'mediapool/upload'], $arg_url)) . '"' . rex::getAccesskey(rex_i18n::msg('pool_file_insert'), 'add') . ' title="' . rex_i18n::msg('pool_file_insert') . '"><i class="rex-icon rex-icon-add-media"></i></a></th>
                                    <th class="rex-table-thumbnail">' . rex_i18n::msg('pool_file_thumbnail') . '</th>
                                    <th>' . rex_i18n::msg('pool_file_info') . ' / ' . rex_i18n::msg('pool_file_description') . '</th>
                                    <th>' . rex_i18n::msg('pool_last_update') . '</th>
                                    <th class="rex-table-action" colspan="2">' . rex_i18n::msg('pool_file_functions') . '</th>
                                </tr>
                            </thead>';

    // ----- move, delete and get selected items
    if ($PERMALL) {
        $add_input = '';
        $filecat = rex_sql::factory();
        $filecat->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media_category ORDER BY name ASC LIMIT 1');

        $e = [];
        $e['label'] = '<label>' . rex_i18n::msg('pool_select_all') . '</label>';
        $e['field'] = '<input type="checkbox" name="checkie" value="0" onclick="setAllCheckBoxes(\'selectedmedia[]\',this)" />';
        $fragment = new rex_fragment();
        $fragment->setVar('elements', [$e], false);
        $checkbox = $fragment->parse('core/form/checkbox.php');

        $field = '';
        if ($filecat->getRows() > 0) {
            $e = [];
            $e['field'] = $cats_sel->get();
            $e['left'] = rex_i18n::msg('pool_changecat_selectedmedia_prefix');
            $e['right'] = '<button class="btn btn-update" type="submit" onclick="var needle=new getObj(\'media_method\');needle.obj.value=\'updatecat_selectedmedia\';">' . rex_i18n::msg('pool_changecat_selectedmedia_suffix') . '</button>';

            $fragment = new rex_fragment();
            $fragment->setVar('elements', [$e], false);
            $field .= '<div class="rex-truncate-dropdown">' . $fragment->parse('core/form/input_group.php') . '</div>';
        }

        $buttons = [];

        $button = [];
        $button['label'] = rex_i18n::msg('pool_delete_selectedmedia');
        $button['attributes']['class'][] = 'btn-delete';
        $button['attributes']['type'][] = 'submit';
        $button['attributes']['onclick'][] = 'if(confirm(\'' . rex_i18n::msg('delete') . ' ?\')){var needle=new getObj(\'media_method\');needle.obj.value=\'delete_selectedmedia\';}else{return false;}';
        $buttons[] = $button;

        //$buttons = '<button class="btn btn-delete" type="submit" onclick="if(confirm(\'' . rex_i18n::msg('delete') . ' ?\')){var needle=new getObj(\'media_method\');needle.obj.value=\'delete_selectedmedia\';}else{return false;}">' . rex_i18n::msg('pool_delete_selectedmedia') . '</button>';
        if (substr($opener_input_field, 0, 14) == 'REX_MEDIALIST_') {
            $button = [];
            $button['label'] = rex_i18n::msg('pool_get_selectedmedia');
            $button['attributes']['class'][] = 'btn-apply';
            $button['attributes']['type'][] = 'submit';
            $button['attributes']['onclick'][] = 'selectMediaListArray(\'selectedmedia[]\');return false;';
            $buttons[] = $button;
        }

        $actionButtons = '';
        if (count($buttons) > 0) {
            $fragment = new rex_fragment();
            $fragment->setVar('buttons', $buttons, false);
            $fragment->setVar('vertical', true, false);
            $actionButtons = $fragment->parse('core/buttons/button_group.php');
        }

        $field = '<div class="row"><div class="col-sm-9">' . $field . '</div><div class="col-sm-3 text-right">' . $actionButtons . '</div>';

        $e = [];
        $e['label'] = '<label>' . rex_i18n::msg('pool_selectedmedia') . '</label>';
        $e['field'] = $field;
        $fragment = new rex_fragment();
        $fragment->setVar('elements', [$e], false);
        $field = $fragment->parse('core/form/form.php');

        $panel .= '
            <tfoot>
            <tr>
                <td colspan="2">
                ' . $checkbox . '
                </td>
                <td colspan="4">
                ' . $field . '
                </td>
            </tr>
            </tfoot>
        ';
    }

    $files = rex_sql::factory();
    $where = 'f.category_id=' . $rex_file_category;
    $addTable = '';
    if ($media_name != '') {
        $media_name = str_replace(['_', '%'], ['\_', '\%'], $media_name);
        $media_name = $files->escape('%'.$media_name.'%');
        $where = '(f.filename LIKE ' . $media_name . ' OR f.title LIKE ' . $media_name . ')';
        if (rex_addon::get('mediapool')->getConfig('searchmode', 'local') != 'global' && $rex_file_category != 0) {
            $addTable = rex::getTablePrefix() . 'media_category c, ';
            $where .= ' AND f.category_id = c.id ';
            $where .= " AND (c.path LIKE '%|" . $rex_file_category . "|%' OR c.id=" . $rex_file_category . ') ';
        }
    }
    if (isset($args['types'])) {
        $types = [];
        foreach (explode(',', $args['types']) as $type) {
            $types[] = 'LOWER(RIGHT(f.filename, LOCATE(".", REVERSE(f.filename))-1))=' . $files->escape(strtolower($type));
        }
        $where .= ' AND (' . implode(' OR ', $types) . ')';
    }
    $qry = 'SELECT f.* FROM ' . $addTable . rex::getTablePrefix() . 'media f WHERE ' . $where . ' ORDER BY f.updatedate desc, f.id desc';

    // ----- EXTENSION POINT
    $qry = rex_extension::registerPoint(new rex_extension_point('MEDIA_LIST_QUERY', $qry, [
        'category_id' => $rex_file_category,
    ]));
    $files->setQuery($qry);

    $panel .= '<tbody>';
    for ($i = 0; $i < $files->getRows(); ++$i) {
        $file_id = $files->getValue('id');
        $file_name = $files->getValue('filename');
        $file_oname = $files->getValue('originalname');
        $file_title = $files->getValue('title');
        $file_type = $files->getValue('filetype');
        $file_size = $files->getValue('filesize');
        $file_stamp = rex_formatter::strftime($files->getDateTimeValue('updatedate'), 'datetime');
        $file_updateuser = $files->getValue('updateuser');

        $encoded_file_name = urlencode($file_name);

        // Eine titel Spalte schätzen
        $alt = '';
        foreach (['title'] as $col) {
            if ($files->hasValue($col) && $files->getValue($col) != '') {
                $alt = htmlspecialchars($files->getValue($col));
                break;
            }
        }

        // Eine beschreibende Spalte schätzen
        $desc = '';
        foreach (['med_description'] as $col) {
            if ($files->hasValue($col) && $files->getValue($col) != '') {
                $desc = '<p>' . htmlspecialchars($files->getValue($col)) . '</p>';
                break;
            }
        }

        // wenn datei fehlt
        if (!file_exists(rex_path::media($file_name))) {
            $thumbnail = '<i class="rex-mime rex-mime-error" title="' . rex_i18n::msg('pool_file_does_not_exist') . '"></i><span class="sr-only">' . $file_name . '</span>';
        } else {
            $file_ext = substr(strrchr($file_name, '.'), 1);
            $icon_class = ' rex-mime-default';
            if (rex_media::isDocType($file_ext)) {
                $icon_class = ' rex-mime-' . $file_ext;
            }
            $thumbnail = '<i class="rex-mime' . $icon_class . '" title="' . $alt . '" data-extension="' . $file_ext . '"></i><span class="sr-only">' . $file_name . '</span>';

            if (rex_media::isImageType(rex_file::extension($file_name))) {
                $thumbnail = '<img class="thumbnail" src="' . rex_url::media($file_name) . '" width="80" height="80" alt="' . $alt . '" title="' . $alt . '" />';
                if ($media_manager && rex_file::extension($file_name) != 'svg') {
                    $thumbnail = '<img class="thumbnail" src="' . rex_url::backendController(['rex_media_type' => 'rex_mediapool_preview', 'rex_media_file' => $encoded_file_name]) . '" alt="' . $alt . '" title="' . $alt . '" />';
                }
            }
        }

        // ----- get file size
        $size = $file_size;
        $file_size = rex_formatter::bytes($size);

        if ($file_title == '') {
            $file_title = '[' . rex_i18n::msg('pool_file_notitle') . ']';
        }
        $file_title .= ' [' . $file_id . ']';

        // ----- opener
        $opener_link = '';
        if ($opener_input_field != '') {
            $opener_link = '<a class="btn btn-xs btn-select" href="javascript:selectMedia(\'' . $file_name . '\', \'' . addslashes(htmlspecialchars($files->getValue('title'))) . '\');">' . rex_i18n::msg('pool_file_get') . '</a>';
            if (substr($opener_input_field, 0, 14) == 'REX_MEDIALIST_') {
                $opener_link = '<a class="btn btn-xs btn-select" href="javascript:selectMedialist(\'' . $file_name . '\');">' . rex_i18n::msg('pool_file_get') . '</a>';
            }
        }

        $ilink = rex_url::currentBackendPage(array_merge(['file_id' => $file_id, 'rex_file_category' => $rex_file_category], $arg_url));

        $add_td = '<td></td>';
        if ($PERMALL) {
            $add_td = '<td><input type="checkbox" name="selectedmedia[]" value="' . $file_name . '" /></td>';
        }

        $panel .= '<tr>
                        ' . $add_td . '
                        <td data-title="' . rex_i18n::msg('pool_file_thumbnail') . '"><a href="' . $ilink . '">' . $thumbnail . '</a></td>
                        <td data-title="' . rex_i18n::msg('pool_file_info') . '">
                            <h3><a href="' . $ilink . '">' . htmlspecialchars($file_title) . '</a></h3>
                            ' . $desc . '
                            <p>' . htmlspecialchars($file_name) . ' <span class="rex-filesize">' . $file_size . '</span></p>
                        </td>
                        <td data-title="' . rex_i18n::msg('pool_last_update') . '"><p class="rex-date">' . $file_stamp . '</p><p class="rex-author">' . htmlspecialchars($file_updateuser) . '</p></td>
                        <td class="rex-table-action"><a href="' . $ilink . '">' . rex_i18n::msg('edit') . '</a></td>
                        <td class="rex-table-action">';

        $panel .= rex_extension::registerPoint(new rex_extension_point('MEDIA_LIST_FUNCTIONS', $opener_link, [
            'file_id' => $files->getValue('id'),
            'file_name' => $files->getValue('filename'),
            'file_oname' => $files->getValue('originalname'),
            'file_title' => $files->getValue('title'),
            'file_type' => $files->getValue('filetype'),
            'file_size' => $files->getValue('filesize'),
            'file_stamp' => $files->getDateTimeValue('updatedate'),
            'file_updateuser' => $files->getValue('updateuser'),
        ]));

        $panel .= '</td>
                 </tr>';

        $files->next();
    } // endforeach

    // ----- no items found
    if ($files->getRows() == 0) {
        $panel .= '
            <tr>
                <td></td>
                <td colspan="5">' . rex_i18n::msg('pool_nomediafound') . '</td>
            </tr>';
    }

    $panel .= '
            </tbody>
            </table>
        </fieldset>
    </form>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('pool_file_caption', $rex_file_category_name), false);
    $fragment->setVar('options', $toolbar, false);
    $fragment->setVar('content', $panel, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;
}
