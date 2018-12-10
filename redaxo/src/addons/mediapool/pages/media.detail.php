<?php

/**
 * @package redaxo5
 */

if (rex_post('btn_delete', 'string')) {
    if (!$csrf->isValid()) {
        $error = rex_i18n::msg('csrf_token_invalid');
    } else {
        $sql = rex_sql::factory()->setQuery('SELECT filename FROM ' . rex::getTable('media') . ' WHERE id = ?', [$file_id]);
        $media = null;
        if ($sql->getRows() == 1) {
            $media = rex_media::get($sql->getValue('filename'));
        }

        if ($media) {
            $filename = $media->getFileName();
            if (rex::getUser()->getComplexPerm('media')->hasCategoryPerm($media->getCategoryId())) {
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
}

if (rex_post('btn_update', 'string')) {
    if (!$csrf->isValid()) {
        $error = rex_i18n::msg('csrf_token_invalid');
    } else {
        $gf = rex_sql::factory();
        $gf->setQuery('select * from ' . rex::getTablePrefix() . 'media where id=?', [$file_id]);
        if ($gf->getRows() != 1) {
            $error = rex_i18n::msg('pool_file_not_found');
            $file_id = 0;
        } elseif (!rex::getUser()->getComplexPerm('media')->hasCategoryPerm($gf->getValue('category_id')) || !rex::getUser()->getComplexPerm('media')->hasCategoryPerm($rex_file_category)) {
            $error = rex_i18n::msg('no_permission');
        } elseif (!empty($_FILES['file_new']['tmp_name']) && !rex_mediapool_isAllowedMimeType($_FILES['file_new']['tmp_name'], $_FILES['file_new']['name'])) {
            $error = rex_i18n::msg('pool_file_mediatype_not_allowed') . ' <code>' . rex_file::extension($_FILES['file_new']['name']) . '</code> (<code>' . mime_content_type($_FILES['file_new']['tmp_name']) . '</code>)';
        } else {
            $FILEINFOS = [];
            $FILEINFOS['rex_file_category'] = $rex_file_category;
            $FILEINFOS['file_id'] = $file_id;
            $FILEINFOS['title'] = rex_request('ftitle', 'string');
            $FILEINFOS['filetype'] = $gf->getValue('filetype');
            $FILEINFOS['filename'] = $gf->getValue('filename');

            $return = rex_mediapool_updateMedia($_FILES['file_new'], $FILEINFOS, rex::getUser()->getValue('login'));

            if ($return['ok'] == 1) {
                $success = $return['msg'];
            } else {
                $error = $return['msg'];
            }
        }
    }
}

$gf = rex_sql::factory();
$gf->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media WHERE id = ?', [$file_id]);
if ($gf->getRows() != 1) {
    $error = rex_i18n::msg('pool_file_not_found');
    $file_id = 0;

    return;
}

$TPERM = false;
if (rex::getUser()->getComplexPerm('media')->hasCategoryPerm($gf->getValue('category_id'))) {
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

$sidebar = '';
$add_ext_info = '';
$encoded_fname = urlencode($fname);
if ($isImage) {
    $e = [];
    $e['label'] = '<label>' . rex_i18n::msg('pool_img_width') . ' / ' . rex_i18n::msg('pool_img_height') . '</label>';
    $e['field'] = '<p class="form-control-static">' . $fwidth . 'px / ' . $fheight . 'px</p>';

    $fragment = new rex_fragment();
    $fragment->setVar('elements', [$e], false);
    $add_ext_info = $fragment->parse('core/form/form.php');

    $imgn = rex_url::media($fname).'?buster='.$gf->getDateTimeValue('updatedate');
    $width = ' width="'.$rfwidth.'"';
    $img_max = rex_url::media($fname);

    if ($media_manager && rex_file::extension($fname) != 'svg') {
        $imgn = rex_url::backendController(['rex_media_type' => 'rex_mediapool_detail', 'rex_media_file' => $encoded_fname, 'buster' => $gf->getDateTimeValue('updatedate')]);
        $img_max = rex_url::backendController(['rex_media_type' => 'rex_mediapool_maximized', 'rex_media_file' => $encoded_fname, 'buster' => $gf->getDateTimeValue('updatedate')]);
        $width = '';
    }

    if (!file_exists(rex_path::media($fname))) {
        $sidebar = '<i class="rex-mime rex-mime-error"></i><span class="sr-only">' . $fname . '</span>';
    } else {
        $sidebar = '
                <a href="' . $img_max . '">
                    <img class="img-responsive" src="' . $imgn . '"' . $width . ' alt="' . rex_escape($ftitle, 'html_attr') . '" title="' . rex_escape($ftitle, 'html_attr') . '" />
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
    $opener_link = '<a class="btn btn-xs btn-select" onclick="selectMedia(\'' . $encoded_fname . '\', \'' . rex_escape($gf->getValue('title'), 'js') . '\'); return false;">' . rex_i18n::msg('pool_file_get') . '</a>';
    if (substr($opener_input_field, 0, 14) == 'REX_MEDIALIST_') {
        $opener_link = '<a class="btn btn-xs btn-select" onclick="selectMedialist(\'' . $encoded_fname . '\'); return false;">' . rex_i18n::msg('pool_file_get') . '</a>';
    }
}

if ($opener_link != '') {
    $opener_link = ' | ' . $opener_link;
}

// ----- EXTENSION POINT
$sidebar = rex_extension::registerPoint(new rex_extension_point('MEDIA_DETAIL_SIDEBAR', $sidebar, [
    'id' => $file_id,
    'filename' => $fname,
    'media' => $gf,
    'is_image' => $isImage,
]));

if ($TPERM) {
    $panel = '';

    $cats_sel = new rex_media_category_select();
    $cats_sel->setStyle('class="form-control"');
    $cats_sel->setSize(1);
    $cats_sel->setName('rex_file_category');
    $cats_sel->setId('rex-mediapool-category');
    $cats_sel->setAttribute('class', 'selectpicker form-control');
    $cats_sel->setAttribute('data-live-search', 'true');
    $cats_sel->setSelected($rex_file_category);

    if (rex::getUser()->getComplexPerm('media')->hasAll()) {
        $cats_sel->addOption(rex_i18n::msg('pool_kats_no'), '0');
    }

    $formElements = [];

    $e = [];
    $e['label'] = '<label for="rex-mediapool-title">' . rex_i18n::msg('pool_file_title') . '</label>';
    $e['field'] = '<input class="form-control" type="text" id="rex-mediapool-title" name="ftitle" value="' . rex_escape($ftitle, 'html_attr') . '" />';
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
    $e['field'] = '<p class="form-control-static"><a href="' . rex_url::media($encoded_fname) . '">' . rex_escape($fname) . '</a> <span class="rex-filesize">' . $ffile_size . '</span></p>';
    $formElements[] = $e;

    $e = [];
    $e['label'] = '<label>' . rex_i18n::msg('pool_last_update') . '</label>';
    $e['field'] = '<p class="form-control-static">' . rex_formatter::strftime(strtotime($gf->getValue('updatedate')), 'datetime') . ' <span class="rex-author">' . $gf->getValue('updateuser') . '</span></p>';
    $formElements[] = $e;

    $e = [];
    $e['label'] = '<label>' . rex_i18n::msg('pool_created') . '</label>';
    $e['field'] = '<p class="form-control-static">' . rex_formatter::strftime(strtotime($gf->getValue('createdate')), 'datetime') . ' <span class="rex-author">' . $gf->getValue('createuser') . '</span></p>';
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

    if ($sidebar != '') {
        $fragment = new rex_fragment();
        $fragment->setVar('content', [$panel, $sidebar], false);
        $fragment->setVar('classes', ['col-sm-8', 'col-sm-4'], false);
        $panel = $fragment->parse('core/page/grid.php');
    }

    $body = '
        <form action="' . rex_url::currentBackendPage() . '" method="post" enctype="multipart/form-data" data-pjax="false">
            ' . $csrf->getHiddenField() . '
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
    $e['field'] = '<p class="form-control-static">' . rex_escape($ftitle) . '</p>';
    $formElements[] = $e;

    $e = [];
    $e['label'] = '<label>' . rex_i18n::msg('pool_file_category') . '</label>';
    $e['field'] = '<p class="form-control-static">' . rex_escape($catname) . '</p>';
    $formElements[] = $e;

    $e = [];
    $e['label'] = '<label>' . rex_i18n::msg('pool_filename') . '</label>';
    $e['field'] = '<p class="form-control-static"><a href="' . rex_url::media($encoded_fname) . '">' . $fname . '</a>  <span class="rex-filesize">' . $ffile_size . '</span></p>';
    $formElements[] = $e;

    $e = [];
    $e['label'] = '<label>' . rex_i18n::msg('pool_last_update') . '</label>';
    $e['field'] = '<p class="form-control-static">' . rex_formatter::strftime(strtotime($gf->getValue('updatedate')), 'datetime') . ' <span class="rex-author">' . $gf->getValue('updateuser') . '</span></p>';
    $formElements[] = $e;

    $e = [];
    $e['label'] = '<label>' . rex_i18n::msg('pool_created') . '</label>';
    $e['field'] = '<p class="form-control-static">' . rex_formatter::strftime(strtotime($gf->getValue('createdate')), 'datetime') . ' <span class="rex-author">' . $gf->getValue('createuser') . '</span></p>';
    $formElements[] = $e;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $panel .= $fragment->parse('core/form/form.php');

    if ($sidebar != '') {
        $fragment = new rex_fragment();
        $fragment->setVar('content', [$panel, $sidebar], false);
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
