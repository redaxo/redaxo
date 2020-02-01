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

$csrfToken = rex_csrf_token::factory('backup_import');

if ('' != $impname) {
    $impname = basename($impname);

    if ('dbimport' == $function && '.sql' != substr($impname, -4, 4)) {
        $impname = '';
    } elseif ('fileimport' == $function && '.tar.gz' != substr($impname, -7, 7)) {
        $impname = '';
    } elseif (('delete' == $function || 'download' == $function) && '.sql' != substr($impname, -4, 4) && '.tar.gz' != substr($impname, -7, 7)) {
        $impname = '';
    }
}

if ('download' == $function && $impname && is_readable(rex_backup::getDir() . '/' . $impname)) {
    rex_response::sendFile(rex_backup::getDir() . '/' . $impname, '.tar.gz' != substr($impname, -7, 7) ? 'tar/gzip' : 'plain/test', 'attachment');
    exit;
}

if ($function && !$csrfToken->isValid()) {
    $error = rex_i18n::msg('csrf_token_invalid');
} elseif ('delete' == $function && $impname) {
    // ------------------------------ FUNC DELETE
    if (rex_file::delete(rex_backup::getDir() . '/' . $impname)) {
        $success = rex_i18n::msg('backup_file_deleted');
    } else {
        $error = rex_i18n::msg('backup_file_error_while_delete');
    }
} elseif ('dbimport' == $function) {
    // ------------------------------ FUNC DBIMPORT

    // noch checken das nicht alle tabellen geloescht werden
    // install/temp.sql aendern
    if (isset($_FILES['FORM']) && $_FILES['FORM']['size']['importfile'] < 1 && '' == $impname) {
        $error = rex_i18n::msg('backup_no_import_file_chosen_or_wrong_version') . '<br>';
    } else {
        if ('' != $impname) {
            $file_temp = rex_backup::getDir() . '/' . $impname;
        } else {
            $file_temp = rex_backup::getDir() . '/temp.sql';
        }

        if ('' != $impname || @move_uploaded_file($_FILES['FORM']['tmp_name']['importfile'], $file_temp)) {
            $state = rex_backup::importDb($file_temp);
            if ($state['state']) {
                $success = $state['message'];
            } else {
                $error = $state['message'];
            }

            // temp datei löschen
            if ('' == $impname) {
                rex_file::delete($file_temp);
            }
        } else {
            $error = rex_i18n::msg('backup_file_could_not_be_uploaded') . ' ' . rex_i18n::msg('backup_you_have_no_write_permission_in', 'data/addons/backup/') . ' <br>';
        }
    }
} elseif ('fileimport' == $function) {
    // ------------------------------ FUNC FILEIMPORT

    if (isset($_FILES['FORM']) && $_FILES['FORM']['size']['importfile'] < 1 && '' == $impname) {
        $error = rex_i18n::msg('backup_no_import_file_chosen') . '<br/>';
    } else {
        if ('' == $impname) {
            $file_temp = rex_backup::getDir() . '/temp.tar.gz';
        } else {
            $file_temp = rex_backup::getDir() . '/' . $impname;
        }

        if ('' != $impname || @move_uploaded_file($_FILES['FORM']['tmp_name']['importfile'], $file_temp)) {
            $return = rex_backup::importFiles($file_temp);
            if ($return['state']) {
                $success = $return['message'];
            } else {
                $error = $return['message'];
            }

            // temp datei löschen
            if ('' == $impname) {
                rex_file::delete($file_temp);
            }
        } else {
            $error = rex_i18n::msg('backup_file_could_not_be_uploaded') . ' ' . rex_i18n::msg('backup_you_have_no_write_permission_in', 'data/addons/backup/') . ' <br>';
        }
    }
}
if ('' != $success) {
    echo rex_view::success($success);
}
if ('' != $error) {
    echo rex_view::error($error);
}

$body = '<p>' . rex_i18n::msg('backup_intro_import') . '</p>';
$body .= '<p>' . rex_i18n::msg('backup_version_warning') . '</p>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'info');
$fragment->setVar('title', rex_i18n::msg('backup_note'), false);
$fragment->setVar('body', $body, false);
echo $fragment->parse('core/page/section.php');

$content = '<table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="rex-table-icon"></th>
                        <th>' . rex_i18n::msg('backup_filename') . '</th>
                        <th class="rex-table-width-5">' . rex_i18n::msg('backup_filesize') . '</th>
                        <th class="rex-table-width-5">' . rex_i18n::msg('backup_createdate') . '</th>
                        <th class="rex-table-action" colspan="3">' . rex_i18n::msg('backup_function') . '</th>
                    </tr>
                </thead>
                <tbody>';

$dir = rex_backup::getDir();
$folder = rex_backup::getBackupFiles('.sql');

foreach ($folder as $file) {
    $filepath = $dir . '/' . $file;
    $filec = date('d.m.Y H:i', filemtime($filepath));
    $filesize = rex_file::formattedSize($filepath);

    $content .= '<tr>
                    <td class="rex-table-icon"><i class="rex-icon rex-icon-database"></i></td>
                    <td data-title="' . rex_i18n::msg('backup_filename') . '">' . $file . '</td>
                    <td data-title="' . rex_i18n::msg('backup_filesize') . '">' . $filesize . '</td>
                    <td data-title="' . rex_i18n::msg('backup_createdate') . '">' . $filec . '</td>
                    <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['function' => 'dbimport', 'impname' => $file] + $csrfToken->getUrlParams()) . '" title="' . rex_i18n::msg('backup_import_file') . '" data-confirm="' . rex_i18n::msg('backup_proceed_db_import') . '"><i class="rex-icon rex-icon-import"></i> ' . rex_i18n::msg('backup_to_import') . '</a></td>
                    <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['function' => 'download', 'impname' => $file]) . '" title="' . rex_i18n::msg('backup_download_file') . '"><i class="rex-icon rex-icon-download"></i> ' . rex_i18n::msg('backup_download') . '</a></td>
                    <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['function' => 'delete', 'impname' => $file] + $csrfToken->getUrlParams()) . '" title="' . rex_i18n::msg('backup_delete_file') . '" data-confirm="' . rex_i18n::msg('backup_delete') . ' ?"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('backup_delete') . '</a></td>
                </tr>
    ';
}

$content .= '
                    </tbody>
                </table>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('backup_export_db_caption'), false);
$fragment->setVar('content', $content, false);
$content = $fragment->parse('core/page/section.php');

echo $content;

$content = '<table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="rex-table-icon"></th>
                        <th>' . rex_i18n::msg('backup_filename') . '</th>
                        <th class="rex-table-width-5">' . rex_i18n::msg('backup_filesize') . '</th>
                        <th class="rex-table-width-5">' . rex_i18n::msg('backup_createdate') . '</th>
                        <th class="rex-table-action" colspan="3">' . rex_i18n::msg('backup_function') . '</th>
                    </tr>
                </thead>
                <tbody>';

$dir = rex_backup::getDir();
$folder = rex_backup::getBackupFiles('.tar.gz');

foreach ($folder as $file) {
    $filepath = $dir . '/' . $file;
    $filec = date('d.m.Y H:i', filemtime($filepath));
    $filesize = rex_file::formattedSize($filepath);

    $content .= '<tr>
                    <td class="rex-table-icon"><i class="rex-icon rex-icon-file-archive"></i></td>
                    <td data-title="' . rex_i18n::msg('backup_filename') . '">' . $file . '</td>
                    <td data-title="' . rex_i18n::msg('backup_filesize') . '">' . $filesize . '</td>
                    <td data-title="' . rex_i18n::msg('backup_createdate') . '">' . $filec . '</td>
                    <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['function' => 'fileimport', 'impname' => $file] + $csrfToken->getUrlParams()) . '" title="' . rex_i18n::msg('backup_import_file') . '" data-confirm="' . rex_i18n::msg('backup_proceed_file_import') . '"><i class="rex-icon rex-icon-import"></i> ' . rex_i18n::msg('backup_to_import') . '</a></td>
                    <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['function' => 'download', 'impname' => $file]) . '" title="' . rex_i18n::msg('backup_download_file') . '"><i class="rex-icon rex-icon-download"></i> ' . rex_i18n::msg('backup_download') . '</a></td>
                    <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['function' => 'delete', 'impname' => $file] + $csrfToken->getUrlParams()) . '" title="' . rex_i18n::msg('backup_delete_file') . '" data-confirm="' . rex_i18n::msg('backup_delete') . ' ?"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('backup_delete') . '</a></td>
                </tr>';
}

$content .= '
               </tbody>
            </table>
        ';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('backup_export_file_caption'), false);
$fragment->setVar('content', $content, false);
$content = $fragment->parse('core/page/section.php');

echo $content;
