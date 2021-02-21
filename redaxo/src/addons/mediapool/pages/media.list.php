<?php

/**
 * @package redaxo5
 */

assert(isset($csrf) && $csrf instanceof rex_csrf_token);
assert(isset($rexFileCategory) && is_int($rexFileCategory));
assert(isset($openerInputField) && is_string($openerInputField));
assert(isset($argFields) && is_string($argFields));
assert(isset($toolbar) && is_string($toolbar));
assert(isset($rexFileCategoryName) && is_string($rexFileCategoryName));

// defaults for globals passed in from index.php
if (!isset($success)) {
    $success = '';
}
if (!isset($error)) {
    $error = '';
}
if (!isset($argUrl)) {
    /**
     * @var array{args: array{types: string}, opener_input_field: string}
     */
    $argUrl = [];
}

$mediaMethod = rex_request('media_method', 'string');

$hasCategoryPerm = rex::getUser()->getComplexPerm('media')->hasCategoryPerm($rexFileCategory);

if ($hasCategoryPerm && 'updatecat_selectedmedia' == $mediaMethod) {
    if (!$csrf->isValid()) {
        $error = rex_i18n::msg('csrf_token_invalid');
    } else {
        $selectedmedia = rex_post('selectedmedia', 'array');
        if (isset($selectedmedia[0]) && '' != $selectedmedia[0]) {
            foreach ($selectedmedia as $fileName) {
                $db = rex_sql::factory();
                // $db->setDebug();
                $db->setTable(rex::getTablePrefix() . 'media');
                $db->setWhere(['filename' => $fileName]);
                $db->setValue('category_id', $rexFileCategory);
                $db->addGlobalUpdateFields();
                try {
                    $db->update();
                    $success = rex_i18n::msg('pool_selectedmedia_moved');
                    rex_media_cache::delete($fileName);

                    rex_extension::registerPoint(new rex_extension_point('MEDIA_MOVED', null, [
                        'filename' => $fileName,
                        'category_id' => $rexFileCategory,
                    ]));
                } catch (rex_sql_exception $e) {
                    $error = rex_i18n::msg('pool_selectedmedia_error');
                }
            }
        } else {
            $error = rex_i18n::msg('pool_selectedmedia_error');
        }
    }
}

if ($hasCategoryPerm && 'delete_selectedmedia' == $mediaMethod) {
    if (!$csrf->isValid()) {
        $error = rex_i18n::msg('csrf_token_invalid');
    } else {
        $selectedmedia = rex_post('selectedmedia', 'array');
        if (0 != count($selectedmedia)) {
            $error = [];
            $success = [];

            $countDeleted = 0;
            foreach ($selectedmedia as $fileName) {
                $media = rex_media::get($fileName);
                if ($media) {
                    if (rex::getUser()->getComplexPerm('media')->hasCategoryPerm($media->getCategoryId())) {
                        $return = rex_mediapool_deleteMedia($fileName);
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

$catsSel = new rex_media_category_select();
$catsSel->setSize(1);
$catsSel->setStyle('class="form-control selectpicker"');
$catsSel->setName('rex_file_category');
$catsSel->setId('rex_file_category');
$catsSel->setAttribute('class', 'selectpicker form-control');
$catsSel->setAttribute('data-live-search', 'true');
$catsSel->setSelected($rexFileCategory);

if (rex::getUser()->getComplexPerm('media')->hasAll()) {
    $catsSel->addOption(rex_i18n::msg('pool_kats_no'), '0');
}

if ($error) {
    if (is_array($error)) {
        $error = implode('<br />', $error);
    }

    echo rex_view::error($error);
    $error = '';
}

if ($success) {
    if (is_array($success)) {
        $success = implode('<br />', $success);
    }

    echo rex_view::success($success);
    $success = '';
}

if (!empty($argUrl['args']['types'])) {
    echo rex_view::info(rex_i18n::msg('pool_file_filter') . ' <code>' . $argUrl['args']['types'] . '</code>');
}

//deletefilelist und cat change
$panel = '
<form action="' . rex_url::currentBackendPage() . '" method="post" enctype="multipart/form-data">
    <fieldset>
        ' . $csrf->getHiddenField() . '
        <input type="hidden" id="media_method" name="media_method" value="" />
        ' . $argFields . '

        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="rex-table-icon"><a class="rex-link-expanded" href="' . rex_url::backendController(array_merge(['page' => 'mediapool/upload'], $argUrl)) . '"' . rex::getAccesskey(rex_i18n::msg('pool_file_insert'), 'add') . ' title="' . rex_i18n::msg('pool_file_insert') . '"><i class="rex-icon rex-icon-add-media"></i></a></th>
                <th class="rex-table-thumbnail">' . rex_i18n::msg('pool_file_thumbnail') . '</th>
                <th>' . rex_i18n::msg('pool_file_info') . ' / ' . rex_i18n::msg('pool_file_description') . '</th>
                <th>' . rex_i18n::msg('pool_last_update') . '</th>
                <th class="rex-table-action" colspan="2">' . rex_i18n::msg('pool_file_functions') . '</th>
            </tr>
            </thead>';

            // ----- move, delete and get selected items
            if ($hasCategoryPerm) {
                $addInput = '';
                $filecat = rex_sql::factory();
                $filecat->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media_category ORDER BY name ASC LIMIT 1');

                $e = [];
                $e['label'] = '<label>' . rex_i18n::msg('pool_select_all') . '</label>';
                $e['field'] = '<input type="checkbox" name="checkie" value="0" onclick="setAllCheckBoxes(\'selectedmedia[]\',this)" />';
                $e['class'] = 'rex-form-group-no-margin';
                $fragment = new rex_fragment();
                $fragment->setVar('elements', [$e], false);
                $checkbox = $fragment->parse('core/form/checkbox.php');

                $field = '';
                if ($filecat->getRows() > 0) {
                    $e = [];
                    $e['field'] = $catsSel->get();
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
                if ('REX_MEDIALIST_' == substr($openerInputField, 0, 14)) {
                    $button = [];
                    $button['label'] = rex_i18n::msg('pool_get_selectedmedia');
                    $button['attributes']['class'][] = 'btn-apply';
                    $button['attributes']['type'][] = 'submit';
                    $button['attributes']['onclick'][] = 'selectMediaListArray(\'selectedmedia[]\');return false;';
                    $buttons[] = $button;
                }

                $actionButtons = '';
                foreach ($buttons as $button) {
                    $fragment = new rex_fragment();
                    $fragment->setVar('buttons', [$button], false);
                    $actionButtons .= $fragment->parse('core/buttons/button.php');
                    $actionButtons .= ' ';
                }

                $field = '<div class="row"><div class="col-sm-7">' . $field . '</div><div class="col-sm-5 text-right">' . $actionButtons . '</div>';

                $e = [];
                $e['label'] = '<label>' . rex_i18n::msg('pool_selectedmedia') . '</label>';
                $e['field'] = $field;
                $e['class'] = 'rex-form-group-no-margin';
                $fragment = new rex_fragment();
                $fragment->setVar('elements', [$e], false);
                $field = $fragment->parse('core/form/form.php');

                $panel .= '
                <tfoot class="rex-sticky-table-footer">
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
                $where = 'f.category_id=' . $rexFileCategory;
                $addTable = '';
                $mediaName = rex_request('media_name', 'string');
                if ('' != $mediaName) {
                    $mediaName = str_replace(['_', '%'], ['\_', '\%'], $mediaName);
                    $mediaName = $files->escape('%'.$mediaName.'%');
                    $where = '(f.filename LIKE ' . $mediaName . ' OR f.title LIKE ' . $mediaName . ')';
                    if ('global' != rex_addon::get('mediapool')->getConfig('searchmode', 'local') && 0 != $rexFileCategory) {
                        $addTable = rex::getTablePrefix() . 'media_category c, ';
                        $where .= ' AND f.category_id = c.id ';
                        $where .= " AND (c.path LIKE '%|" . $rexFileCategory . "|%' OR c.id=" . $rexFileCategory . ') ';
                    }
                }
                if (isset($argUrl['args']['types'])) {
                    $types = [];
                    foreach (explode(',', $argUrl['args']['types']) as $type) {
                        $types[] = 'LOWER(RIGHT(f.filename, LOCATE(".", REVERSE(f.filename))-1))=' . $files->escape(strtolower($type));
                    }
                    $where .= ' AND (' . implode(' OR ', $types) . ')';
                }
                $qry = 'SELECT f.* FROM ' . $addTable . rex::getTablePrefix() . 'media f WHERE ' . $where . ' ORDER BY f.updatedate desc, f.id desc';

                // ----- EXTENSION POINT
                $qry = rex_extension::registerPoint(new rex_extension_point('MEDIA_LIST_QUERY', $qry, [
                    'category_id' => $rexFileCategory,
                ]));
                $files->setQuery($qry);

                if (!rex_addon::get('media_manager')->isAvailable()) {
                    $mediaManagerUrl = null;
                } else {
                    $mediaManagerUrl = [rex_media_manager::class, 'getUrl'];
                }

                $panel .= '<tbody>';
                for ($i = 0; $i < $files->getRows(); ++$i) {
                    $fileId = $files->getValue('id');
                    $fileName = $files->getValue('filename');
                    $fileOname = $files->getValue('originalname');
                    $fileTitle = $files->getValue('title');
                    $fileType = $files->getValue('filetype');
                    $fileSize = $files->getValue('filesize');
                    $fileStamp = rex_formatter::strftime($files->getDateTimeValue('updatedate'), 'datetime');
                    $fileUpdateuser = $files->getValue('updateuser');

                    $encodedFileName = urlencode($fileName);

                    // Eine titel Spalte schätzen
                    $alt = '';
                    foreach (['title'] as $col) {
                        if ($files->hasValue($col) && '' != $files->getValue($col)) {
                            $alt = rex_escape($files->getValue($col));
                            break;
                        }
                    }

                    // Eine beschreibende Spalte schätzen
                    $desc = '';
                    foreach (['med_description'] as $col) {
                        if ($files->hasValue($col) && '' != $files->getValue($col)) {
                            $desc = '<p>' . rex_escape(strip_tags($files->getValue($col))) . '</p>';
                            break;
                        }
                    }

                    // wenn datei fehlt
                    if (!is_file(rex_path::media($fileName))) {
                        $thumbnail = '<i class="rex-mime rex-mime-error" title="' . rex_i18n::msg('pool_file_does_not_exist') . '"></i><span class="sr-only">' . $fileName . '</span>';
                    } else {
                        $fileExt = rex_file::extension($fileName);
                        $iconClass = ' rex-mime-default';
                        if (rex_media::isDocType($fileExt)) {
                            $iconClass = ' rex-mime-' . $fileExt;
                        }
                        $thumbnail = '<i class="rex-mime' . $iconClass . '" title="' . $alt . '" data-extension="' . $fileExt . '"></i><span class="sr-only">' . $fileName . '</span>';

                        if (rex_media::isImageType(rex_file::extension($fileName))) {
                            $thumbnail = '<img class="thumbnail" src="' . rex_url::media($fileName) . '?buster=' . $files->getDateTimeValue('updatedate') . '" width="80" height="80" alt="' . $alt . '" title="' . $alt . '" />';
                            if ($mediaManagerUrl && 'svg' != rex_file::extension($fileName)) {
                                $thumbnail = '<img class="thumbnail" src="' . $mediaManagerUrl('rex_mediapool_preview', $encodedFileName, $files->getDateTimeValue('updatedate')) . '" alt="' . $alt . '" title="' . $alt . '" />';
                            }
                        }
                    }

                    // ----- get file size
                    $size = $fileSize;
                    $fileSize = rex_formatter::bytes($size);

                    if ('' == $fileTitle) {
                        $fileTitle = '[' . rex_i18n::msg('pool_file_notitle') . ']';
                    }

                    // ----- opener
                    $openerLink = '';
                    if ('' != $openerInputField) {
                        $openerLink = '<a class="btn btn-xs btn-select" onclick="selectMedia(\'' . $fileName . '\', \'' . rex_escape($files->getValue('title'), 'js') . '\'); return false;">' . rex_i18n::msg('pool_file_get') . '</a>';
                        if ('REX_MEDIALIST_' == substr($openerInputField, 0, 14)) {
                            $openerLink = '<a class="btn btn-xs btn-select btn-highlight" onclick="selectMedialist(\'' . $fileName . '\', this);return false;">' . rex_i18n::msg('pool_file_get') . '</a>';
                        }
                    }

                    $ilink = rex_url::currentBackendPage(array_merge(['file_id' => $fileId, 'rex_file_category' => $rexFileCategory], $argUrl));

                    $addTd = '<td></td>';
                    if ($hasCategoryPerm) {
                        $addTd = '<td><input type="checkbox" name="selectedmedia[]" value="' . $fileName . '" /></td>';
                    }

                    $panel .= '<tr>
                    ' . $addTd . '
                    <td class="rex-word-break" data-title="' . rex_i18n::msg('pool_file_thumbnail') . '"><a href="' . $ilink . '"><div class="lazyload" data-noscript=""><noscript>' . $thumbnail . '</noscript></div></a></td>
                    <td class="rex-word-break" data-title="' . rex_i18n::msg('pool_file_info') . '">
                        <h3><a class="rex-link-expanded" href="' . $ilink . '">' . rex_escape($fileTitle) . '</a></h3>
                        ' . $desc . '
                        <p>' . rex_escape($fileName) . ' <span class="rex-filesize">' . $fileSize . '</span></p>
                    </td>
                    <td data-title="' . rex_i18n::msg('pool_last_update') . '"><p class="rex-date">' . $fileStamp . '</p><p class="rex-author">' . rex_escape($fileUpdateuser) . '</p></td>
                    <td class="rex-table-action"><a class="rex-link-expanded" href="' . $ilink . '">' . rex_i18n::msg('edit') . '</a></td>
                    <td class="rex-table-action">';

                    $panel .= rex_extension::registerPoint(new rex_extension_point('MEDIA_LIST_FUNCTIONS', $openerLink, [
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
                if (0 == $files->getRows()) {
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
$fragment->setVar('title', rex_i18n::msg('pool_file_caption', $rexFileCategoryName), false);
$fragment->setVar('options', $toolbar, false);
$fragment->setVar('content', $panel, false);
$content = $fragment->parse('core/page/section.php');

echo $content;
