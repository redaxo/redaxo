<?php

assert(isset($rex_file_category) && is_int($rex_file_category));
assert(isset($PERMALL) && is_bool($PERMALL));

$csrf = rex_csrf_token::factory('mediapool');

// ----- SYNC DB WITH FILES DIR
if ($PERMALL) {
    // ---- Dateien aus dem Ordner lesen
    $folder_files = [];
    $path = rex_path::media();
    $iterator = rex_finder::factory($path)->filesOnly()->ignoreFiles(['.*', rex::getTempPrefix() . '*'])->sort();
    foreach ($iterator as $file) {
        $folder_files[] = rex_string::normalizeEncoding($file->getFilename());
    }

    // ---- Dateien aus der DB lesen
    $db = rex_sql::factory();
    $db->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media');
    $db_files = [];
    $db_filenames = [];

    foreach ($db->getArray() as $db_file) {
        $db_filenames[] = $db_file['filename'];
        $db_files[] = $db_file;
    }

    $diff_files = array_diff($folder_files, $db_filenames);
    $diff_count = count($diff_files);

    // Extra - filesize/width/height DB-Filesystem Sync
    foreach ($db_files as $db_file) {
        $path = rex_path::media($db_file['filename']);
        if (!file_exists($path)) {
            continue;
        }

        $file_filesize = filesize($path);
        if ($db_file['filesize'] != $file_filesize) {
            $file_sql = rex_sql::factory();
            $file_sql->setTable(rex::getTable('media'));
            $file_sql->setWhere(['filename' => $db_file['filename']]);
            $file_sql->setValue('filesize', $file_filesize);
            if ($db_file['width'] > 0) {
                if ($size = @getimagesize(rex_path::media($db_file['filename']))) {
                    $file_sql->setValue('width', $size[0]);
                    $file_sql->setValue('height', $size[1]);
                }
            }
            $file_sql->update();
            rex_media_cache::delete($db_file['filename']);
        }
    }

    $error = [];
    $success = [];
    if (rex_post('save', 'boolean') && rex_post('sync_files', 'boolean')) {
        if (!$csrf->isValid()) {
            $error[] = rex_i18n::msg('csrf_token_invalid');
        } else {
            $sync_files = rex_post('sync_files', 'array');
            $ftitle = rex_post('ftitle', 'string');

            if ($diff_count > 0) {
                $success = [];
                $first = true;
                foreach ($sync_files as $file) {
                    // hier mit is_int, wg kompatibilität zu PHP < 4.2.0
                    if (!is_int($key = array_search($file, $diff_files))) {
                        continue;
                    }

                    $syncResult = rex_mediapool_syncFile($file, $rex_file_category, $ftitle, '', '');
                    if ($syncResult['ok']) {
                        unset($diff_files[$key]);
                        if ($first) {
                            $success[] = rex_i18n::msg('pool_sync_files_synced');
                            $first = false;
                        }
                        if ($syncResult['msg']) {
                            $success[] = $syncResult['msg'];
                        }
                    } elseif ($syncResult['msg']) {
                        $error[] = $syncResult['msg'];
                    }
                }
                // diff count neu berechnen, da (hoffentlich) diff files in die db geladen wurden
                $diff_count = count($diff_files);
            }
        }
    } elseif (rex_post('save', 'boolean')) {
        $error[] = rex_i18n::msg('pool_file_not_found');
    }

    if (count($error) > 0) {
        echo rex_view::error(implode('<br />', $error));
        $error = [];
    }
    if (count($success) > 0) {
        echo rex_view::info(implode('<br />', $success));
        $success = [];
    }

    $content = '';

    if ($diff_count > 0) {
        $writable = [];
        $not_writable = [];
        foreach ($diff_files as $file) {
            if (is_writable(rex_path::media($file))) {
                $e = [];
                $e['label'] = '<label>' . $file . '</label>';
                $e['field'] = '<input type="checkbox" name="sync_files[]" value="' . $file . '" />';
                $writable[] = $e;
            } else {
                $not_writable[] = $file;
            }
        }

        $e = [];
        $e['label'] = '<label>' . rex_i18n::msg('pool_select_all') . '</label>';
        $e['field'] = '<input type="checkbox" name="checkie" id="rex-js-checkie" value="0" onchange="setAllCheckBoxes(\'sync_files[]\',this)" />';
        $writable[] = $e;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $writable, false);
        $panel = $fragment->parse('core/form/checkbox.php');

        $count = count($writable) - 1;
        if ($count) {
            $content .= rex_mediapool_Mediaform(rex_i18n::msg('pool_sync_title'), rex_i18n::msg('pool_sync_button'), $rex_file_category, false, false);
            $content .= '<fieldset>';

            $title = rex_i18n::msg('pool_sync_affected_files') . ' (' . $count . ')';

            $fragment = new rex_fragment();
            $fragment->setVar('title', $title, false);
            $fragment->setVar('body', $panel, false);
            $content .= $fragment->parse('core/page/section.php');

            $content .= '
                </fieldset>
            </form>

            <script type="text/javascript">
                jQuery(document).ready(function($){
                    $("input[name=\'sync_files[]\']").change(function() {
                        $(this).closest(\'form\').find("[type=\'submit\']").attr("disabled", $("input[name=\'sync_files[]\']:checked").length == 0);
                    }).change();
                    $("#rex-js-checkie").change(function() {
                        $("input[name=\'sync_files[]\']").change();
                    });
                });
            </script>';
        }

        $count = count($not_writable);
        if ($count) {
            $title = $count > 1 ? rex_i18n::msg('pool_files_not_writable') : rex_i18n::msg('pool_file_not_writable');

            $fragment = new rex_fragment();
            $fragment->setVar('title', $title, false);
            $fragment->setVar('body', '<ul><li>' . implode('</li><li>', $not_writable) . '</li></ul>', false);
            $fragment->setVar('class', 'warning', false);
            $content .= $fragment->parse('core/page/section.php');
        }
    } else {
        $panel = '<p>' . rex_i18n::msg('pool_sync_no_diffs') . '</p>';

        $fragment = new rex_fragment();
        $fragment->setVar('title', rex_i18n::msg('pool_sync_title'), false);
        $fragment->setVar('body', $panel, false);
        $fragment->setVar('class', 'info', false);
        $content = $fragment->parse('core/page/section.php');
    }

    echo $content;
}
