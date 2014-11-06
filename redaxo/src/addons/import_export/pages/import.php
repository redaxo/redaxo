<?php

/**
 *
 * @package redaxo5
 */

$info = '';
$warning = '';

// ------------------------------ Requestvars
$function       = rex_request('function', 'string');
$impname        = rex_request('impname', 'string');
$exporttype     = rex_post('exporttype', 'string');
$exportdl       = rex_post('exportdl', 'boolean');
$EXPDIR         = rex_post('EXPDIR', 'array');

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
    $info = rex_i18n::msg('im_export_file_deleted');
} elseif ($function == 'download' && $impname) {
    rex_response::sendFile(getImportDir() . '/' . $impname, substr($impname, -7, 7) != '.tar.gz' ? 'application/x-gtar' : 'plain/test', 'attachment');
    exit;
} elseif ($function == 'dbimport') {
    // ------------------------------ FUNC DBIMPORT

    // noch checken das nicht alle tabellen geloescht werden
    // install/temp.sql aendern
    if (isset ($_FILES['FORM']) && $_FILES['FORM']['size']['importfile'] < 1 && $impname == '') {
        $warning = rex_i18n::msg('im_export_no_import_file_chosen_or_wrong_version') . '<br>';
    } else {
        if ($impname != '') {
            $file_temp = getImportDir() . '/' . $impname;
        } else {
            $file_temp = getImportDir() . '/temp.sql';
        }

        if ($impname != '' || @ move_uploaded_file($_FILES['FORM']['tmp_name']['importfile'], $file_temp)) {
            $state = rex_a1_import_db($file_temp);
            $info = $state['message'];

            // temp datei löschen
            if ($impname == '') {
                rex_file::delete($file_temp);
            }
        } else {
            $warning = rex_i18n::msg('im_export_file_could_not_be_uploaded') . ' ' . rex_i18n::msg('im_export_you_have_no_write_permission_in', 'addons/import_export/files/') . ' <br>';
        }
    }

} elseif ($function == 'fileimport') {
    // ------------------------------ FUNC FILEIMPORT

    if (isset($_FILES['FORM']) && $_FILES['FORM']['size']['importfile'] < 1 && $impname == '') {
        $warning = rex_i18n::msg('im_export_no_import_file_chosen') . '<br/>';
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
                $warning = $return['message'];
            }

            // temp datei löschen
            if ($impname == '') {
                rex_file::delete($file_temp);
            }
        } else {
            $warning = rex_i18n::msg('im_export_file_could_not_be_uploaded') . ' ' . rex_i18n::msg('im_export_you_have_no_write_permission_in', 'addons/import_export/files/') . ' <br>';
        }
    }

}
if ($info != '') {
    echo rex_view::info($info);
}
if ($warning != '') {
    echo rex_view::warning($warning);
}


echo '<h2>' . rex_i18n::msg('im_export_database') . '</h2>';

$content = '<h2>' . rex_i18n::msg('im_export_note') . '</h2>
            <p>' . rex_i18n::msg('im_export_intro_import') . '</p>';

echo rex_view::content('block', $content);


$content = '<div class="rex-form" id="rex-form-import-data">
                <form action="' . rex_url::currentBackendPage() . '" enctype="multipart/form-data" method="post" data-confirm="' . rex_i18n::msg('im_export_proceed_db_import') . '">

                    <fieldset>
                        <h2>' . rex_i18n::msg('im_export_upload') . '</h2>

                        <input type="hidden" name="function" value="dbimport" />';

$formElements = [];
$n = [];
$n['label'] = '<label for="rex-form-importdbfile">' . rex_i18n::msg('im_export_file') . '</label>';
$n['field'] = '<input type="file" id="rex-form-importdbfile" name="FORM[importfile]" size="18" />';
$formElements[] = $n;

$n = [];
$n['field'] = '<button class="btn btn-primary" type="submit" value="' . rex_i18n::msg('im_export_to_import') . '">' . rex_i18n::msg('im_export_to_import') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');


echo rex_view::content('block', $content, '', ['flush' => 1]);

$content .= '</fieldset></form></div>';

$content = '<table class="table">
                <caption>' . rex_i18n::msg('im_export_export_db_caption') . '</caption>
                <thead>
                    <tr class="rex-small">
                        <th></th>
                        <th>' . rex_i18n::msg('im_export_filename') . '</th>
                        <th>' . rex_i18n::msg('im_export_filesize') . '</th>
                        <th>' . rex_i18n::msg('im_export_createdate') . '</th>
                        <th colspan="3">' . rex_i18n::msg('im_export_function') . '</th>
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
                    <td><span class="rex-icon rex-icon-database"></span></td>
                    <td>' . $file . '</td>
                    <td>' . $filesize . '</td>
                    <td>' . $filec . '</td>
                    <td><a href="' . rex_url::currentBackendPage(['function' => 'dbimport', 'impname' =>  $file]) . '" title="' . rex_i18n::msg('im_export_import_file') . '" data-confirm="' . rex_i18n::msg('im_export_proceed_db_import') . '">' . rex_i18n::msg('im_export_to_import') . '</a></td>
                    <td><a href="' . rex_url::currentBackendPage(['function' => 'download', 'impname' => $file]) . '" class="rex-download" title="' . rex_i18n::msg('im_export_download_file') . '">' . rex_i18n::msg('im_export_download') . '</a></td>
                    <td><a href="' . rex_url::currentBackendPage(['function' => 'delete', 'impname' => $file]) . '" class="rex-delete" title="' . rex_i18n::msg('im_export_delete_file') . '" data-confirm="' . rex_i18n::msg('im_export_delete') . ' ?">' . rex_i18n::msg('im_export_delete') . '</a></td>
                </tr>
    ';
}

$content .= '
                    </tbody>
                </table>';

echo rex_view::content('block', $content, rex_i18n::msg('im_export_load_from_server'), ['flush' => 1]);


echo '<h2>' . rex_i18n::msg('im_export_files') . '</h2>';

$content = '
            <div class="rex-form" id="rex-form-import-files">
                <form action="' . rex_url::currentBackendPage() . '" enctype="multipart/form-data" method="post" data-confirm="' . rex_i18n::msg('im_export_proceed_file_import') . '" >
                    <fieldset class="rex-form-col-1">
                        <h2>' . rex_i18n::msg('im_export_upload') . '</h2>

                        <input type="hidden" name="function" value="fileimport" />';

$formElements = [];
$n = [];
$n['label'] = '<label for="rex-form-importtarfile">' . rex_i18n::msg('im_export_file') . '</label>';
$n['field'] = '<input type="file" id="rex-form-importtarfile" name="FORM[importfile]" size="18" />';
$formElements[] = $n;

$n = [];
$n['field'] = '<button class="btn btn-primary" type="submit" value="' . rex_i18n::msg('im_export_to_import') . '">' . rex_i18n::msg('im_export_to_import') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset></form></div>';

echo rex_view::content('block', $content, '', ['flush' => 1]);

$content = '
        <table class="table">
            <caption>' . rex_i18n::msg('im_export_export_file_caption') . '</caption>
            <thead>
                <tr>
                    <th></th>
                    <th>' . rex_i18n::msg('im_export_filename') . '</th>
                    <th>' . rex_i18n::msg('im_export_filesize') . '</th>
                    <th>' . rex_i18n::msg('im_export_createdate') . '</th>
                    <th colspan="3">' . rex_i18n::msg('im_export_function') . '</th>
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
                    <td><span class="rex-icon rex-icon-file"></span></td>
                    <td>' . $file . '</td>
                    <td>' . $filesize . '</td>
                    <td>' . $filec . '</td>
                    <td><a href="' . rex_url::currentBackendPage(['function' => 'fileimport', 'impname' => $file]) . '" title="' . rex_i18n::msg('im_export_import_file') . '" data-confirm="' . rex_i18n::msg('im_export_proceed_file_import') . '">' . rex_i18n::msg('im_export_to_import') . '</a></td>
                    <td><a href="' . rex_url::currentBackendPage(['function' => 'download', 'impname' => $file]) . '" class="rex-download" title="' . rex_i18n::msg('im_export_download_file') . '">' . rex_i18n::msg('im_export_download') . '</a></td>
                    <td><a href="' . rex_url::currentBackendPage(['function' => 'delete', 'impname' => $file]) . '" class="rex-delete" title="' . rex_i18n::msg('im_export_delete_file') . '" data-confirm="' . rex_i18n::msg('im_export_delete') . ' ?">' . rex_i18n::msg('im_export_delete') . '</a></td>
                </tr>';
}

$content .= '
               </tbody>
            </table>
        ';

echo rex_view::content('block', $content, rex_i18n::msg('im_export_load_from_server'), ['flush' => 1]);
