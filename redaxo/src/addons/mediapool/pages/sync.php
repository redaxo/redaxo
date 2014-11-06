<?php

// *************************************** SYNC FUNCTIONS


// ----- SYNC DB WITH FILES DIR
if ($PERMALL) {
    // ---- Dateien aus dem Ordner lesen
    $folder_files = [];
    $path = rex_path::media();
    $iterator = rex_finder::factory($path)->filesOnly()->ignoreFiles(['.*', rex::getTempPrefix() . '*'])->sort();
    foreach ($iterator as $file) {
        $folder_files[] = $file->getFilename();
    }

    // ---- Dateien aus der DB lesen
    $db = rex_sql::factory();
    $db->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media');
    $db_files = [];
    $db_filenames = array();

    foreach($db->getArray() as $db_file) {
        $db_filenames[] = $db_file["filename"];
        $db_files[] = $db_file;
    }

    $diff_files = array_diff($folder_files, $db_filenames);
    $diff_count = count($diff_files);

    // Extra - filesize/width/height DB-Filesystem Sync
    foreach($db_files as $db_file) {
        $file_filesize = filesize(rex_path::media($db_file["filename"]));
        if ($db_file["filesize"] != $file_filesize) {
            $file_sql = rex_sql::factory();
            // $file_sql->debugsql = 1;
            $file_sql->setTable(rex::getTable('file'));
            $file_sql->setWhere('filename=?', [$db_file["filename"]]);
            $file_sql->setValue('filesize', $file_filesize);
            if ($db_file["width"] > 0) {
                if($size = @getimagesize(rex_path::media($db_file["filename"]))) {
                    $file_sql->setValue('width', $size[0]);
                    $file_sql->setValue('height', $size[1]);
                }
            }
            $file_sql->update();
            rex_media_cache::delete($db_file["filename"]);
        }
    }

    $warning = [];
    if (rex_post('save', 'boolean') && rex_post('sync_files', 'boolean')) {
        $sync_files = rex_post('sync_files', 'array');
        $ftitle     = rex_post('ftitle', 'string');

        if ($diff_count > 0) {
            $info = [];
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
                        $info[] = rex_i18n::msg('pool_sync_files_synced');
                        $first = false;
                    }
                    if ($syncResult['msg']) {
                        $info[] = $syncResult['msg'];
                    }
                } elseif ($syncResult['msg']) {
                    $warning[] = $syncResult['msg'];
                }
            }
            // diff count neu berechnen, da (hoffentlich) diff files in die db geladen wurden
            $diff_count = count($diff_files);
        }
    } elseif (rex_post('save', 'boolean')) {
        $warning[] = rex_i18n::msg('pool_file_not_found');
    }

    echo rex_mediapool_Mediaform(rex_i18n::msg('pool_sync_title'), rex_i18n::msg('pool_sync_button'), $rex_file_category, false, false);

    $title = rex_i18n::msg('pool_sync_affected_files');
    if (!empty($diff_count)) {
        $title .= ' (' . $diff_count . ')';
    }
    echo '<fieldset class="rex-form-col-1">
                    <legend>' . $title . '</legend>
                    <div class="rex-form-wrapper">';

    if ($diff_count > 0) {
        foreach ($diff_files as $file) {
            echo '<div class="rex-form-row">
                            <p class="checkbox rex-form-label-right">';
            if (is_writable(rex_path::media($file))) {
                echo '<input class="checkbox" type="checkbox" id="sync_file_' . $file . '" name="sync_files[]" value="' . $file . '" />
                            <label for="sync_file_' . $file . '">' . $file . '</label>';
            } else {
                echo $file . ' - ' .  rex_i18n::msg('pool_file_not_writable') . "\n";
            }
            echo '    </p>
                        </div>';
        }

        echo '<div class="rex-form-row">
                        <p class="checkbox rex-form-label-right">
                            <input class="checkbox" type="checkbox" name="checkie" id="checkie" value="0" onchange="setAllCheckBoxes(\'sync_files[]\',this)" />
                            <label for="checkie">' . rex_i18n::msg('pool_select_all') . '</label>
                        </p>
                    </div>';

    } else {
        echo '<div class="rex-form-row">
                        <p class="rex-form-notice">
                            <span class="rex-form-notice"><strong>' . rex_i18n::msg('pool_sync_no_diffs') . '</strong></span>
                        </p>
                    </div>';
    }

    echo '</div>
                </fieldset>
            </form>
        </div>

    <script type="text/javascript">
        jQuery(document).ready(function($){
            $("input[name=\'sync_files[]\']").change(function() {
                $("#media-form-button").attr("disabled", $("input[name=\'sync_files[]\']:checked").size() == 0);
            }).change();
            $("#checkie").change(function() {
                $("input[name=\'sync_files[]\']").change();
            });
        });
    </script>';
}
