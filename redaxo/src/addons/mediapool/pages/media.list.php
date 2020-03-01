<?php

/**
 * @package redaxo5
 */

assert(isset($csrf) && $csrf instanceof rex_csrf_token);
assert(isset($rex_file_category) && is_int($rex_file_category));
assert(isset($opener_input_field) && is_string($opener_input_field));
assert(isset($arg_fields) && is_string($arg_fields));
assert(isset($toolbar) && is_string($toolbar));
assert(isset($rex_file_category_name) && is_string($rex_file_category_name));

// defaults for globals passed in from index.php
if (!isset($success)) {
    $success = '';
}
if (!isset($error)) {
    $error = '';
}
if (!isset($arg_url)) {
    /**
     * @var array{args: array{types: string}, opener_input_field: string}
     */
    $arg_url = [];
}

$media_method = rex_request('media_method', 'string');

$hasCategoryPerm = rex::getUser()->getComplexPerm('media')->hasCategoryPerm($rex_file_category);

if ($hasCategoryPerm && 'updatecat_selectedmedia' == $media_method) {
    if (!$csrf->isValid()) {
        $error = rex_i18n::msg('csrf_token_invalid');
    } else {
        $selectedmedia = rex_post('selectedmedia', 'array');
        if (isset($selectedmedia[0]) && '' != $selectedmedia[0]) {
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

                    rex_extension::registerPoint(new rex_extension_point('MEDIA_MOVED', null, [
                        'filename' => $file_name,
                        'category_id' => $rex_file_category,
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

if ($hasCategoryPerm && 'delete_selectedmedia' == $media_method) {
    if (!$csrf->isValid()) {
        $error = rex_i18n::msg('csrf_token_invalid');
    } else {
        $selectedmedia = rex_post('selectedmedia', 'array');
        if (0 != count($selectedmedia)) {
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

$cats_sel = new rex_media_category_select();
$cats_sel->setSize(1);
$cats_sel->setStyle('class="form-control selectpicker"');
$cats_sel->setName('rex_file_category');
$cats_sel->setId('rex_file_category');
$cats_sel->setAttribute('class', 'selectpicker form-control');
$cats_sel->setAttribute('data-live-search', 'true');
$cats_sel->setSelected($rex_file_category);

if (rex::getUser()->getComplexPerm('media')->hasAll()) {
    $cats_sel->addOption(rex_i18n::msg('pool_kats_no'), '0');
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

if (!empty($arg_url['args']['types'])) {
    echo rex_view::info(rex_i18n::msg('pool_file_filter') . ' <code>' . $arg_url['args']['types'] . '</code>');
}

//deletefilelist und cat change
$panel = '
<form action="' . rex_url::currentBackendPage() . '" method="post" enctype="multipart/form-data">
    <fieldset>
        ' . $csrf->getHiddenField() . '
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
            if ($hasCategoryPerm) {
                $add_input = '';
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
                if ('REX_MEDIALIST_' == substr($opener_input_field, 0, 14)) {
                    $button = [];
                    $button['label'] = rex_i18n::msg('pool_get_selectedmedia');
                    $button['attributes']['class'][] = 'btn-apply';
                    $button['attributes']['type'][] = 'submit';
                    $button['attributes']['onclick'][] = 'selectMediaListArray(\'selectedmedia[]\');return false;';
                    $buttons[] = $button;
                }

                $actionButtons = '';
                if (count($buttons) > 0) {
                    foreach ($buttons as $button) {
                        $fragment = new rex_fragment();
                        $fragment->setVar('buttons', [$button], false);
                        $actionButtons .= $fragment->parse('core/buttons/button.php');
                        $actionButtons .= ' ';
                    }
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
                $where = 'f.category_id=' . $rex_file_category;
                $addTable = '';
                $media_name = rex_request('media_name', 'string');
                if ('' != $media_name) {
                    $media_name = str_replace(['_', '%'], ['\_', '\%'], $media_name);
                    $media_name = $files->escape('%'.$media_name.'%');
                    $where = '(f.filename LIKE ' . $media_name . ' OR f.title LIKE ' . $media_name . ')';
                    if ('global' != rex_addon::get('mediapool')->getConfig('searchmode', 'local') && 0 != $rex_file_category) {
                        $addTable = rex::getTablePrefix() . 'media_category c, ';
                        $where .= ' AND f.category_id = c.id ';
                        $where .= " AND (c.path LIKE '%|" . $rex_file_category . "|%' OR c.id=" . $rex_file_category . ') ';
                    }
                }
                if (isset($arg_url['args']['types'])) {
                    $types = [];
                    foreach (explode(',', $arg_url['args']['types']) as $type) {
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

                if (!rex_addon::get('media_manager')->isAvailable()) {
                    $media_manager_url = null;
                } else {
                    $media_manager_url = [rex_media_manager::class, 'getUrl'];
                }

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
                        if ($files->hasValue($col) && '' != $files->getValue($col)) {
                            $alt = rex_escape($files->getValue($col));
                            break;
                        }
                    }

                    // Eine beschreibende Spalte schätzen
                    $desc = '';
                    foreach (['med_description'] as $col) {
                        if ($files->hasValue($col) && '' != $files->getValue($col)) {
                            $desc = '<p>' . rex_escape($files->getValue($col)) . '</p>';
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
                            $thumbnail = '<img class="thumbnail" src="' . rex_url::media($file_name) . '?buster=' . $files->getDateTimeValue('updatedate') . '" width="80" height="80" alt="' . $alt . '" title="' . $alt . '" />';
                            if ($media_manager_url && 'svg' != rex_file::extension($file_name)) {
                                $thumbnail = '<img class="thumbnail" src="' . $media_manager_url('rex_mediapool_preview', $encoded_file_name, $files->getDateTimeValue('updatedate')) . '" alt="' . $alt . '" title="' . $alt . '" />';
                            }
                        }
                    }

                    // ----- get file size
                    $size = $file_size;
                    $file_size = rex_formatter::bytes($size);

                    if ('' == $file_title) {
                        $file_title = '[' . rex_i18n::msg('pool_file_notitle') . ']';
                    }

                    // ----- opener
                    $opener_link = '';
                    if ('' != $opener_input_field) {
                        $opener_link = '<a class="btn btn-xs btn-select" onclick="selectMedia(\'' . $file_name . '\', \'' . rex_escape($files->getValue('title'), 'js') . '\'); return false;">' . rex_i18n::msg('pool_file_get') . '</a>';
                        if ('REX_MEDIALIST_' == substr($opener_input_field, 0, 14)) {
                            $opener_link = '<a class="btn btn-xs btn-select btn-highlight" onclick="selectMedialist(\'' . $file_name . '\', this);return false;">' . rex_i18n::msg('pool_file_get') . '</a>';
                        }
                    }

                    $ilink = rex_url::currentBackendPage(array_merge(['file_id' => $file_id, 'rex_file_category' => $rex_file_category], $arg_url));

                    $add_td = '<td></td>';
                    if ($hasCategoryPerm) {
                        $add_td = '<td><input type="checkbox" name="selectedmedia[]" value="' . $file_name . '" /></td>';
                    }

                    $panel .= '<tr>
                    ' . $add_td . '
                    <td data-title="' . rex_i18n::msg('pool_file_thumbnail') . '"><a href="' . $ilink . '"><div class="lazyload" data-noscript=""><noscript>' . $thumbnail . '</noscript></div></a></td>
                    <td data-title="' . rex_i18n::msg('pool_file_info') . '">
                        <h3><a href="' . $ilink . '">' . rex_escape($file_title) . '</a></h3>
                        ' . $desc . '
                        <p>' . rex_escape($file_name) . ' <span class="rex-filesize">' . $file_size . '</span></p>
                    </td>
                    <td data-title="' . rex_i18n::msg('pool_last_update') . '"><p class="rex-date">' . $file_stamp . '</p><p class="rex-author">' . rex_escape($file_updateuser) . '</p></td>
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
$fragment->setVar('title', rex_i18n::msg('pool_file_caption', $rex_file_category_name), false);
$fragment->setVar('options', $toolbar, false);
$fragment->setVar('content', $panel, false);
$content = $fragment->parse('core/page/section.php');

echo $content;
