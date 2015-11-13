<?php

/**
 * @package redaxo5
 */

$success = '';
$error = '';

// ------------------------------ Requestvars
$function = rex_request('function', 'string');
$impname = rex_request('impname', 'string');
$exporttype = rex_post('exporttype', 'string');
$exportdl = rex_post('exportdl', 'boolean');
$EXPDIR = rex_post('EXPDIR', 'array');

@set_time_limit(0);

if ($impname != '') {
    $impname = basename($impname);

    if ($function == 'dbimport' && substr($impname, -4, 4) != '.sql') {
        $impname = '';
    } elseif ($function == 'fileimport' && substr($impname, -7, 7) != '.tar.gz') {
        $impname = '';
    } elseif (($function == 'delete'  || $function == 'download') && substr($impname, -4, 4) != '.sql' && substr($impname, -7, 7) != '.tar.gz') {
        $impname = '';
    }
}

if ($function == 'delete' && $impname) {
    // ------------------------------ FUNC DELETE
    if (rex_file::delete(getImportDir() . '/' . $impname));
    $success = rex_i18n::msg('im_export_file_deleted');
} elseif ($function == 'download' && $impname && is_readable(getImportDir() . '/' . $impname)) {
    rex_response::sendFile(getImportDir() . '/' . $impname, substr($impname, -7, 7) != '.tar.gz' ? 'tar/gzip' : 'plain/test', 'attachment');
    exit;
} elseif ($function == 'dbimport') {
    // ------------------------------ FUNC DBIMPORT

    // noch checken das nicht alle tabellen geloescht werden
    // install/temp.sql aendern
    if (isset($_FILES['FORM']) && $_FILES['FORM']['size']['importfile'] < 1 && $impname == '') {
        $error = rex_i18n::msg('im_export_no_import_file_chosen_or_wrong_version') . '<br>';
    } else {
        if ($impname != '') {
            $file_temp = getImportDir() . '/' . $impname;
        } else {
            $file_temp = getImportDir() . '/temp.sql';
        }

        if ($impname != '' || @move_uploaded_file($_FILES['FORM']['tmp_name']['importfile'], $file_temp)) {
            $state = rex_a1_import_db($file_temp);
            if ($state['state']) {
                $success = $state['message'];
            } else {
                $error = $state['message'];
            }

            // temp datei löschen
            if ($impname == '') {
                rex_file::delete($file_temp);
            }
        } else {
            $error = rex_i18n::msg('im_export_file_could_not_be_uploaded') . ' ' . rex_i18n::msg('im_export_you_have_no_write_permission_in', 'addons/import_export/files/') . ' <br>';
        }
    }
} elseif ($function == 'fileimport') {
    // ------------------------------ FUNC FILEIMPORT

    if (isset($_FILES['FORM']) && $_FILES['FORM']['size']['importfile'] < 1 && $impname == '') {
        $error = rex_i18n::msg('im_export_no_import_file_chosen') . '<br/>';
    } else {
        if ($impname == '') {
            $file_temp = getImportDir() . '/temp.tar.gz';
        } else {
            $file_temp = getImportDir() . '/' . $impname;
        }

        if ($impname != '' || @move_uploaded_file($_FILES['FORM']['tmp_name']['importfile'], $file_temp)) {
            $return = rex_a1_import_files($file_temp);
            if ($return['state']) {
                $info = $return['message'];
            } else {
                $error = $return['message'];
            }

            // temp datei löschen
            if ($impname == '') {
                rex_file::delete($file_temp);
            }
        } else {
            $error = rex_i18n::msg('im_export_file_could_not_be_uploaded') . ' ' . rex_i18n::msg('im_export_you_have_no_write_permission_in', 'addons/import_export/files/') . ' <br>';
        }
    }
}
if ($success != '') {
    echo rex_view::success($success);
}
if ($error != '') {
    echo rex_view::error($error);
}

$fragment = new rex_fragment();
$fragment->setVar('class', 'info');
$fragment->setVar('title', rex_i18n::msg('im_export_note'), false);
$fragment->setVar('body', '<p>' . rex_i18n::msg('im_export_intro_import') . '</p>', false);
echo $fragment->parse('core/page/section.php');

$content = '<table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="rex-table-icon"></th>
                        <th>' . rex_i18n::msg('im_export_filename') . '</th>
                        <th class="rex-table-width-5">' . rex_i18n::msg('im_export_filesize') . '</th>
                        <th class="rex-table-width-5">' . rex_i18n::msg('im_export_createdate') . '</th>
                        <th class="rex-table-action" colspan="3">' . rex_i18n::msg('im_export_function') . '</th>
                    </tr>
                </thead>
                <tbody>';

$dir = getImportDir();
$folder = readImportFolder('.sql');

foreach ($folder as $file) {
    $filepath = $dir . '/' . $file;
    $filec = date('d.m.Y H:i', filemtime($filepath));
    $filesize = rex_file::formattedSize($filepath);

    $content .= '<tr>
                    <td class="rex-table-icon"><i class="rex-icon rex-icon-database"></i></td>
                    <td data-title="' . rex_i18n::msg('im_export_filename') . '">' . $file . '</td>
                    <td data-title="' . rex_i18n::msg('im_export_filesize') . '">' . $filesize . '</td>
                    <td data-title="' . rex_i18n::msg('im_export_createdate') . '">' . $filec . '</td>
                    <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['function' => 'dbimport', 'impname' => $file]) . '" title="' . rex_i18n::msg('im_export_import_file') . '" data-confirm="' . rex_i18n::msg('im_export_proceed_db_import') . '"><i class="rex-icon rex-icon-import"></i> ' . rex_i18n::msg('im_export_to_import') . '</a></td>
                    <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['function' => 'download', 'impname' => $file]) . '" title="' . rex_i18n::msg('im_export_download_file') . '"><i class="rex-icon rex-icon-download"></i> ' . rex_i18n::msg('im_export_download') . '</a></td>
                    <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['function' => 'delete', 'impname' => $file]) . '" title="' . rex_i18n::msg('im_export_delete_file') . '" data-confirm="' . rex_i18n::msg('im_export_delete') . ' ?"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('im_export_delete') . '</a></td>
                </tr>
    ';
}

$content .= '
                    </tbody>
                </table>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('im_export_export_db_caption'), false);
$fragment->setVar('content', $content, false);
$content = $fragment->parse('core/page/section.php');

echo $content;

$content = '<table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="rex-table-icon"></th>
                        <th>' . rex_i18n::msg('im_export_filename') . '</th>
                        <th class="rex-table-width-5">' . rex_i18n::msg('im_export_filesize') . '</th>
                        <th class="rex-table-width-5">' . rex_i18n::msg('im_export_createdate') . '</th>
                        <th class="rex-table-action" colspan="3">' . rex_i18n::msg('im_export_function') . '</th>
                    </tr>
                </thead>
                <tbody>';

$dir = getImportDir();
$folder = readImportFolder('.tar.gz');

foreach ($folder as $file) {
    $filepath = $dir . '/' . $file;
    $filec = date('d.m.Y H:i', filemtime($filepath));
    $filesize = rex_file::formattedSize($filepath);

    $content .= '<tr>
                    <td class="rex-table-icon"><i class="rex-icon rex-icon-file-archive"></i></td>
                    <td data-title="' . rex_i18n::msg('im_export_filename') . '">' . $file . '</td>
                    <td data-title="' . rex_i18n::msg('im_export_filesize') . '">' . $filesize . '</td>
                    <td data-title="' . rex_i18n::msg('im_export_createdate') . '">' . $filec . '</td>
                    <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['function' => 'fileimport', 'impname' => $file]) . '" title="' . rex_i18n::msg('im_export_import_file') . '" data-confirm="' . rex_i18n::msg('im_export_proceed_file_import') . '"><i class="rex-icon rex-icon-import"></i> ' . rex_i18n::msg('im_export_to_import') . '</a></td>
                    <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['function' => 'download', 'impname' => $file]) . '" title="' . rex_i18n::msg('im_export_download_file') . '"><i class="rex-icon rex-icon-download"></i> ' . rex_i18n::msg('im_export_download') . '</a></td>
                    <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['function' => 'delete', 'impname' => $file]) . '" title="' . rex_i18n::msg('im_export_delete_file') . '" data-confirm="' . rex_i18n::msg('im_export_delete') . ' ?"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('im_export_delete') . '</a></td>
                </tr>';
}

$content .= '
               </tbody>
            </table>
        ';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('im_export_export_file_caption'), false);
$fragment->setVar('content', $content, false);
$content = $fragment->parse('core/page/section.php');

echo $content;
