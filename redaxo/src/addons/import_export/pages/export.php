<?php

/**
 *
 * @package redaxo5
 */

// Für größere Exports den Speicher für PHP erhöhen.
if (rex_ini_get('memory_limit') < 67108864) {
    @ini_set('memory_limit', '64M');
}

$content = '';

$info = '';
$warning = '';

// ------------------------------ Requestvars
$function       = rex_request('function', 'string');
$exportfilename = rex_post('exportfilename', 'string');
$exporttype     = rex_post('exporttype', 'string');
$exportdl       = rex_post('exportdl', 'boolean');
$EXPDIR         = rex_post('EXPDIR', 'array');


if ($exportfilename == '') {
    $server = parse_url(rex::getServer(), PHP_URL_HOST);
    $exportfilename = strtolower($server) . '_rex' . rex::getVersion() . '_' . date('Ymd_Hi');
}

if (rex_post('export', 'bool')) {
    // ------------------------------ FUNC EXPORT

    $exportfilename = strtolower($exportfilename);
    $filename       = preg_replace('@[^\.a-z0-9_\-]@', '', $exportfilename);

    if ($filename != $exportfilename) {
        $info = rex_i18n::msg('im_export_filename_updated');
        $exportfilename = $filename;
    } else {
        $content     = '';
        $hasContent  = false;
        $header      = '';
        $ext         = $exporttype == 'sql' ? '.sql' : '.tar.gz';
        $export_path = getImportDir() . '/';

        if (file_exists($export_path . $filename . $ext)) {
            $i = 1;
            while (file_exists($export_path . $filename . '_' . $i . $ext)) {
                $i++;
            }
            $filename = $filename . '_' . $i;
        }

        if ($exporttype == 'sql') {
            // ------------------------------ FUNC EXPORT SQL
            $header = 'plain/text';

            $hasContent = rex_a1_export_db($export_path . $filename . $ext);
            // ------------------------------ /FUNC EXPORT SQL
        } elseif ($exporttype == 'files') {
            // ------------------------------ FUNC EXPORT FILES
            $header = 'tar/gzip';

            if (empty($EXPDIR)) {
                $warning = rex_i18n::msg('im_export_please_choose_folder');
            } else {
                $content    = rex_a1_export_files($EXPDIR);
                $hasContent = rex_file::put($export_path . $filename . $ext, $content);
            }
            // ------------------------------ /FUNC EXPORT FILES
        }

        if ($hasContent) {
            if ($exportdl) {
                $filename = $filename . $ext;
                rex_response::sendFile($export_path . $filename, $header, 'attachment');
                rex_file::delete($export_path . $filename);
                exit;
            } else {
                $info = rex_i18n::msg('im_export_file_generated_in') . ' ' . strtr($filename . $ext, '\\', '/');
            }
        } else {
            $warning = rex_i18n::msg('im_export_file_could_not_be_generated') . ' ' . rex_i18n::msg('im_export_check_rights_in_directory') . ' ' . $export_path;
        }
    }
}

if ($info != '') {
    echo rex_view::info($info);
}
if ($warning != '') {
    echo rex_view::warning($warning);
}

$content_info = '<h2>' . rex_i18n::msg('im_export_information') . '</h2><p>' . rex_i18n::msg('im_export_intro_export') . '</p>';
echo rex_view::content('block', $content_info);



$content .= '
            <div class="rex-form" id="rex-form-export">
            <form action="' . rex_url::currentBackendPage() . '" method="post">
                <fieldset>
                    <h2>' . rex_i18n::msg('im_export_export_select') . '</h2>';

$checkedsql = '';
$checkedfiles = '';

if ($exporttype == 'files') {
    $checkedfiles = ' checked="checked"';
} else {
    $checkedsql = ' checked="checked"';
}



$formElements = [];
$n = [];
$n['label'] = '<label for="rex-exporttype-sql">' . rex_i18n::msg('im_export_database_export') . '</label>';
$n['field'] = '<input type="radio" id="rex-exporttype-sql" name="exporttype" value="sql"' . $checkedsql . ' />';
$formElements[] = $n;


$n = [];
$n['label'] = '<label for="rex-exporttype-files">' . rex_i18n::msg('im_export_file_export') . '</label>';
$n['field'] = '<input type="radio" id="rex-exporttype-files" name="exporttype" value="files"' . $checkedfiles . ' />';
$formElements[] = $n;


$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/radio.php');


// Vorhandene Exporte auslesen
$sel_dirs = new rex_select();
$sel_dirs->setAttribute('onclick', 'checkInput(\'rex-exporttype-files\')');
$sel_dirs->setId('rex-form-exportdir');
$sel_dirs->setName('EXPDIR[]');
$sel_dirs->setMultiple();
$sel_dirs->setSelected($EXPDIR);
$sel_dirs->setStyle('class="rex-form-select"');

$dir = rex_path::frontend();
$folders = readSubFolders($dir);
$count_folders = count($folders);
if ($count_folders > 4) {
    $sel_dirs->setSize($count_folders);
}
foreach ($folders as $file) {
    if ($file == 'redaxo') {
        continue;
    }
    $sel_dirs->addOption($file, $file);
}

$formElements = [];
$n = [];
$n['label'] = '<label for="rex-form-exportdir">' . rex_i18n::msg('im_export_export_select_dir') . '</label>';
$n['field'] = $sel_dirs->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset><fieldset>
                <h2>' . rex_i18n::msg('im_export_export_select_location') . '</h2>';

$checked0 = '';
$checked1 = '';

if ($exportdl) {
    $checked1 = ' checked="checked"';
} else {
    $checked0 = ' checked="checked"';
}


$formElements = [];

$n = [];
$n['label'] = '<label for="rex-form-exportdl-server">' . rex_i18n::msg('im_export_save_on_server') . '</label>';
$n['field'] = '<input type="radio" id="rex-form-exportdl-server" name="exportdl" value="0"' . $checked0 . ' />';
$formElements[] = $n;


$n = [];
$n['label'] = '<label for="rex-form-exportdl-download">' . rex_i18n::msg('im_export_download_as_file') . '</label>';
$n['field'] = '<input type="radio" id="rex-form-exportdl-download" name="exportdl" value="1"' . $checked1 . ' />';
$formElements[] = $n;


$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/radio.php');


$content .= '</fieldset><fieldset>
                <h2>' . rex_i18n::msg('im_export_export_select_filename') . '</h2>';

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-form-exportfilename">' . rex_i18n::msg('im_export_filename') . '</label>';
$n['field'] = '<input type="text" id="rex-form-exportfilename" name="exportfilename" value="' . $exportfilename . '" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset>';


$formElements = [];
$n = [];
$n['field'] = '<button class="rex-button" type="submit" name="export" value="' . rex_i18n::msg('im_export_db_export') . '">' . rex_i18n::msg('im_export_to_export') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/submit.php');




$content .= '</form></div>';


echo rex_view::content('block', $content, '', ['flush' => 1]);
