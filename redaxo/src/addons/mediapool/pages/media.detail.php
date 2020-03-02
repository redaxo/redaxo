<?php

/**
 * @package redaxo5
 */

assert(isset($csrf) && $csrf instanceof rex_csrf_token);
assert(isset($rex_file_category) && is_int($rex_file_category));
assert(isset($opener_input_field) && is_string($opener_input_field));
assert(isset($arg_fields) && is_string($arg_fields));
assert(isset($toolbar) && is_string($toolbar));

// defaults for globals passed in from index.php
if (!isset($success)) {
    $success = '';
}
if (!isset($error)) {
    $error = '';
}
if (!isset($opener_link)) {
    $opener_link = '';
}
if (!isset($file_id)) {
    $file_id = 0;
}

if (rex_post('btn_delete', 'string')) {
    if (!$csrf->isValid()) {
        $error = rex_i18n::msg('csrf_token_invalid');
    } else {
        $sql = rex_sql::factory()->setQuery('SELECT filename FROM ' . rex::getTable('media') . ' WHERE id = ?', [$file_id]);
        $media = null;
        if (1 == $sql->getRows()) {
            $media = rex_media::get($sql->getValue('filename'));
        }

        if ($media) {
            $filename = $media->getFileName();
            if (rex::getUser()->getComplexPerm('media')->hasCategoryPerm($media->getCategoryId())) {
                $return = rex_mediapool_deleteMedia($filename);
                if ($return['ok']) {
                    $success = $return['msg'];
                    $file_id = 0;

                    return;
                }

                $error = $return['msg'];
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
        if (1 != $gf->getRows()) {
            $error = rex_i18n::msg('pool_file_not_found');
            $file_id = 0;
        } elseif (!rex::getUser()->getComplexPerm('media')->hasCategoryPerm($gf->getValue('category_id')) || !rex::getUser()->getComplexPerm('media')->hasCategoryPerm($rex_file_category)) {
            $error = rex_i18n::msg('no_permission');
        } elseif (!empty($_FILES['file_new']['tmp_name']) && !rex_mediapool_isAllowedMimeType($_FILES['file_new']['tmp_name'], $_FILES['file_new']['name'])) {
            $error = rex_i18n::msg('pool_file_mediatype_not_allowed') . ' <code>' . rex_file::extension($_FILES['file_new']['name']) . '</code> (<code>' . rex_file::mimeType($_FILES['file_new']['tmp_name']) . '</code>)';
        } else {
            $FILEINFOS = [];
            $FILEINFOS['rex_file_category'] = $rex_file_category;
            $FILEINFOS['file_id'] = $file_id;
            $FILEINFOS['title'] = rex_request('ftitle', 'string');
            $FILEINFOS['filetype'] = $gf->getValue('filetype');
            $FILEINFOS['filename'] = $gf->getValue('filename');

            $return = rex_mediapool_updateMedia($_FILES['file_new'], $FILEINFOS, rex::getUser()->getValue('login'));

            if (1 == $return['ok']) {
                if ($gf->getValue('category_id') != $rex_file_category) {
                    rex_extension::registerPoint(new rex_extension_point('MEDIA_MOVED', null, [
                        'filename' => $FILEINFOS['filename'],
                        'category_id' => $rex_file_category,
                    ]));
                }
                $success = $return['msg'];
            } else {
                $error = $return['msg'];
            }
        }
    }
}

$gf = rex_sql::factory();
$gf->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media WHERE id = ?', [$file_id]);
if (1 != $gf->getRows()) {
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

$sidebar = '';
$add_ext_info = '';
$encoded_fname = urlencode($fname);

$isImage = rex_media::isImageType(rex_file::extension($fname));
if ($isImage) {
    $fwidth = $gf->getValue('width');
    $fheight = $gf->getValue('height');

    if ($fwidth > 199) {
        $rfwidth = 200;
    } else {
        $rfwidth = $fwidth;
    }

    $e = [];
    $e['label'] = '<label>' . rex_i18n::msg('pool_img_width') . ' / ' . rex_i18n::msg('pool_img_height') . '</label>';
    $e['field'] = '<p class="form-control-static">' . $fwidth . 'px / ' . $fheight . 'px</p>';

    $fragment = new rex_fragment();
    $fragment->setVar('elements', [$e], false);
    $add_ext_info = $fragment->parse('core/form/form.php');

    $imgn = rex_url::media($fname).'?buster='.$gf->getDateTimeValue('updatedate');
    $width = ' width="'.$rfwidth.'"';
    $img_max = rex_url::media($fname);

    if (rex_addon::get('media_manager')->isAvailable() && 'svg' != rex_file::extension($fname)) {
        $imgn = rex_media_manager::getUrl('rex_mediapool_detail', $encoded_fname, $gf->getDateTimeValue('updatedate'));
        $img_max = rex_media_manager::getUrl('rex_mediapool_maximized', $encoded_fname, $gf->getDateTimeValue('updatedate'));

        $width = '';
    }

    if (!file_exists(rex_path::media($fname))) {
        $sidebar = '<i class="rex-mime rex-mime-error"></i><span class="sr-only">' . $fname . '</span>';
    } else {
        $sidebar = '
                <a href="' . $img_max . '">
                    <img class="img-responsive" src="' . $imgn . '"' . $width . ' alt="' . rex_escape($ftitle) . '" title="' . rex_escape($ftitle) . '" />
                </a>';
    }
}

if ('' != $error) {
    echo rex_view::error($error);
    $error = '';
}
if ('' != $success) {
    echo rex_view::success($success);
    $success = '';
}

if ('' != $opener_input_field) {
    $opener_link = '<a class="btn btn-xs btn-select" onclick="selectMedia(\'' . $encoded_fname . '\', \'' . rex_escape($gf->getValue('title'), 'js') . '\'); return false;">' . rex_i18n::msg('pool_file_get') . '</a>';
    if ('REX_MEDIALIST_' == substr($opener_input_field, 0, 14)) {
        $opener_link = '<a class="btn btn-xs btn-select btn-highlight" onclick="selectMedialist(\'' . $encoded_fname . '\'); return false;">' . rex_i18n::msg('pool_file_get') . '</a>';
    }
}

if ('' != $opener_link) {
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
    $e['field'] = '<input class="form-control" type="text" id="rex-mediapool-title" name="ftitle" value="' . rex_escape($ftitle) . '" />';
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
    $e['field'] = '<p class="form-control-static">' . rex_formatter::strftime(strtotime($gf->getValue('updatedate')), 'datetime') . ' <span class="rex-author">' . rex_escape($gf->getValue('updateuser')) . '</span></p>';
    $formElements[] = $e;

    $e = [];
    $e['label'] = '<label>' . rex_i18n::msg('pool_created') . '</label>';
    $e['field'] = '<p class="form-control-static">' . rex_formatter::strftime(strtotime($gf->getValue('createdate')), 'datetime') . ' <span class="rex-author">' . rex_escape($gf->getValue('createuser')) . '</span></p>';
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

    if ('' != $sidebar) {
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

    if ('' != $sidebar) {
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
