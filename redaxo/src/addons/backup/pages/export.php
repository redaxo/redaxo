<?php

/**
 * @package redaxo5
 */

// Für größere Exports den Speicher für PHP erhöhen.
if (rex_ini_get('memory_limit') < 67108864) {
    @ini_set('memory_limit', '64M');
}

$success = '';
$error = '';

// ------------------------------ Requestvars
$function = rex_request('function', 'string');
$exportfilename = rex_post('exportfilename', 'string');
$exporttype = rex_post('exporttype', 'string');
$exportdl = rex_post('exportdl', 'boolean');
$EXPTABLES = rex_post('EXPTABLES', 'array');
$EXPDIR = rex_post('EXPDIR', 'array');

if ('' == $exportfilename) {
    $exportfilename = rex_string::normalize(rex::getServerName()) . '_' . date('Ymd_Hi') . '_rex' . rex::getVersion();
}

if ($EXPTABLES) {
    $tables = rex_sql::factory()->getTables();

    foreach ($EXPTABLES as $k => $EXPTABLE) {
        if (!in_array($EXPTABLE, $tables)) {
            unset($EXPTABLES[$k]);
        }
    }
}

$csrfToken = rex_csrf_token::factory('backup');
$export = rex_post('export', 'bool');

if ($export && !$csrfToken->isValid()) {
    $error = rex_i18n::msg('csrf_token_invalid');
} elseif ($export) {
    // ------------------------------ FUNC EXPORT

    $exportfilename = strtolower($exportfilename);
    $filename = preg_replace('@[^\.a-z0-9_\-]@', '', $exportfilename);

    if ($filename != $exportfilename) {
        $success = rex_i18n::msg('backup_filename_updated');
        $exportfilename = $filename;
    } else {
        $hasContent = false;
        $header = '';
        $ext = 'sql' == $exporttype ? '.sql' : '.tar.gz';
        $export_path = rex_backup::getDir() . '/';

        if (file_exists($export_path . $filename . $ext)) {
            $i = 1;
            while (file_exists($export_path . $filename . '_' . $i . $ext)) {
                ++$i;
            }
            $filename = $filename . '_' . $i;
        }

        if ('sql' == $exporttype) {
            // ------------------------------ FUNC EXPORT SQL
            $header = 'plain/text';

            $hasContent = rex_backup::exportDb($export_path . $filename . $ext, $EXPTABLES);
        } elseif ('files' == $exporttype) {
            // ------------------------------ FUNC EXPORT FILES
            $header = 'tar/gzip';

            if (empty($EXPDIR)) {
                $error = rex_i18n::msg('backup_please_choose_folder');
            } else {
                $content = rex_backup::exportFiles($EXPDIR);
                $hasContent = rex_file::put($export_path . $filename . $ext, $content);
            }
        }

        if ($hasContent) {
            if ($exportdl) {
                $filename = $filename . $ext;
                rex_response::sendFile($export_path . $filename, $header, 'attachment');
                rex_file::delete($export_path . $filename);
                exit;
            }
            $success = rex_i18n::msg('backup_file_generated_in') . ' ' . strtr($filename . $ext, '\\', '/');
        } elseif (empty($error)) { //if the user selected no files to export $error is already filled
            $error = rex_i18n::msg('backup_file_could_not_be_generated') . ' ' . rex_i18n::msg('backup_check_rights_in_directory') . ' ' . $export_path;
        }
    }
}

if ('' != $success) {
    echo rex_view::success($success);
}
if ('' != $error) {
    echo rex_view::error($error);
}

$content = '';

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', rex_i18n::msg('backup_information'), false);
$fragment->setVar('body', '<p>' . rex_i18n::msg('backup_intro_export') . '</p>', false);
echo $fragment->parse('core/page/section.php');

$content .= '<fieldset>';

$checkedsql = '';
$checkedfiles = '';

if ('files' == $exporttype) {
    $checkedfiles = ' checked="checked"';
} else {
    $checkedsql = ' checked="checked"';
}

$formElements = [];
$n = [];
$n['label'] = '<label for="rex-js-exporttype-sql">' . rex_i18n::msg('backup_database_export') . '</label>';
$n['field'] = '<input type="radio" id="rex-js-exporttype-sql" name="exporttype" value="sql"' . $checkedsql . ' />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-js-exporttype-files">' . rex_i18n::msg('backup_file_export') . '</label>';
$n['field'] = '<input type="radio" id="rex-js-exporttype-files" name="exporttype" value="files"' . $checkedfiles . ' />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$radios = $fragment->parse('core/form/radio.php');

$formElements = [];
$n = [];
$n['label'] = rex_i18n::msg('backup_export_select');
$n['field'] = $radios;
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$tableSelect = new rex_select();
$tableSelect->setMultiple();
$tableSelect->setId('rex-form-exporttables');
$tableSelect->setSize(20);
$tableSelect->setName('EXPTABLES[]');
$tableSelect->setAttribute('class', 'form-control');
$tables = rex_sql::factory()->getTables();
foreach ($tables as $table) {
    $tableSelect->addOption($table, $table);
    if ($table != rex::getTable('user') && 0 === strpos($table, rex::getTablePrefix()) && 0 !== strpos($table, rex::getTablePrefix() . rex::getTempPrefix())) {
        $tableSelect->setSelected($table);
    }
}

$formElements = [];
$n = [];
$n['header'] = '<div id="rex-js-exporttype-sql-div">';
$n['label'] = '<label for="rex-form-exporttables">' . rex_i18n::msg('backup_export_select_tables') . '</label>';
$n['field'] = $tableSelect->get();
$n['footer'] = '</div>';
$formElements[] = $n;

// Vorhandene Exporte auslesen
$sel_dirs = new rex_select();
$sel_dirs->setId('rex-form-exportdir');
$sel_dirs->setName('EXPDIR[]');
$sel_dirs->setMultiple();
$sel_dirs->setSelected($EXPDIR);
$sel_dirs->setStyle('class="form-control"');

$dir = rex_path::frontend();
$folders = rex_finder::factory($dir)
    ->dirsOnly()
    ->ignoreDirs('.*')
    ->ignoreDirs('redaxo')
;
$folders = iterator_to_array($folders);
$count_folders = count($folders);
if ($count_folders > 4) {
    $sel_dirs->setSize($count_folders);
}
foreach ($folders as $path => $_) {
    $file = basename($path);
    $sel_dirs->addOption($file, $file);
}

$n = [];
$n['header'] = '<div id="rex-js-exporttype-files-div" style="display: none;">';
$n['label'] = '<label for="rex-form-exportdir">' . rex_i18n::msg('backup_export_select_dir') . '</label>';
$n['field'] = $sel_dirs->get();
$n['footer'] = '</div>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$checked0 = '';
$checked1 = '';

if ($exportdl) {
    $checked1 = ' checked="checked"';
} else {
    $checked0 = ' checked="checked"';
}

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-form-exportdl-server">' . rex_i18n::msg('backup_save_on_server') . '</label>';
$n['field'] = '<input type="radio" id="rex-form-exportdl-server" name="exportdl" value="0"' . $checked0 . ' />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-exportdl-download">' . rex_i18n::msg('backup_download_as_file') . '</label>';
$n['field'] = '<input type="radio" id="rex-form-exportdl-download" name="exportdl" value="1"' . $checked1 . ' />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$radios = $fragment->parse('core/form/radio.php');

$formElements = [];
$n = [];
$n['label'] = rex_i18n::msg('backup_export_select_location');
$n['field'] = $radios;
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-form-exportfilename">' . rex_i18n::msg('backup_filename') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-form-exportfilename" name="exportfilename" value="' . $exportfilename . '" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset>';

$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="export" value="' . rex_i18n::msg('backup_db_export') . '">' . rex_i18n::msg('backup_to_export') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('backup_export'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

$content = '
<form action="' . rex_url::currentBackendPage() . '" data-pjax="false" method="post">
    ' . $csrfToken->getHiddenField() . '
    ' . $content . '
</form>

<script type="text/javascript">
    <!--

    (function($) {
        var currentShown = null;
        $("#rex-js-exporttype-sql, #rex-js-exporttype-files").click(function(){
            if(currentShown) currentShown.hide();

            var effectParamsId = "#" + $(this).attr("id") + "-div";
            currentShown = $(effectParamsId);
            currentShown.fadeIn();
        }).filter(":checked").click();
    })(jQuery);

    //-->
</script>';

echo $content;
