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

if ($exportfilename == '')
  $exportfilename = strtolower($_SERVER['HTTP_HOST']) . '_rex' . rex::getVersion('') . '_' . date('Ymd');

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
      while (file_exists($export_path . $filename . '_' . $i . $ext)) $i++;
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
  $content .= rex_view::info($info);
}
if ($warning != '') {
  $content .= rex_view::warning($warning);
}


$content .= '

    <h3 class="rex-hl2">' . rex_i18n::msg('im_export_export') . '</h3>

    <div class="rex-area-content">
      <p class="rex-tx1">' . rex_i18n::msg('im_export_intro_export') . '</p>

      <div class="rex-form" id="rex-form-export">
      <form action="' . rex_url::currentBackendPage() . '" method="post" >
        <fieldset class="rex-form-col-1">
          <legend>' . rex_i18n::msg('im_export_export') . '</legend>

          <div class="rex-form-wrapper">';

$checkedsql = '';
$checkedfiles = '';

if ($exporttype == 'files') {
  $checkedfiles = ' checked="checked"';
} else {
  $checkedsql = ' checked="checked"';
}

$content .= '
            <div class="rex-form-row">
              <p class="rex-form-radio rex-form-label-right">
                <input class="rex-form-radio" type="radio" id="exporttype_sql" name="exporttype" value="sql"' . $checkedsql . ' />
                <label for="exporttype_sql">' . rex_i18n::msg('im_export_database_export') . '</label>
              </p>
            </div>
            <div class="rex-form-row rex-form-element-v2">
              <p class="rex-form-radio rex-form-label-right">
                <input class="rex-form-radio" type="radio" id="exporttype_files" name="exporttype" value="files"' . $checkedfiles . ' />
                <label for="exporttype_files">' . rex_i18n::msg('im_export_file_export') . '</label>
              </p>

              <div class="rex-form-checkboxes">
                <div class="rex-form-checkboxes-wrapper">';

  $dir = rex_path::frontend();
  $folders = readSubFolders($dir);

  foreach ($folders as $file) {
    if ($file == 'redaxo') {
      continue;
    }

    $checked = '';
    if (array_key_exists($file, $EXPDIR) !== false) {
      $checked = ' checked="checked"';
    }

    $content .= '<p class="rex-form-checkbox rex-form-label-right">
            <input class="rex-form-checkbox" type="checkbox" onchange="checkInput(\'exporttype_files\');" id="EXPDIR_' . $file . '" name="EXPDIR[' . $file . ']" value="true"' . $checked . ' />
            <label for="EXPDIR_' . $file . '">' . $file . '</label>
          </p>
    ';
  }

$content .= '</div>
  </div>
</div>';

$checked0 = '';
$checked1 = '';

if ($exportdl) {
  $checked1 = ' checked="checked"';
} else {
  $checked0 = ' checked="checked"';
}

$content .= '<div class="rex-form-row">
              <p class="rex-form-radio rex-form-label-right">
                <input class="rex-form-radio" type="radio" id="exportdl_server" name="exportdl" value="0"' . $checked0 . ' />
                <label for="exportdl_server">' . rex_i18n::msg('im_export_save_on_server') . '</label>
              </p>
            </div>
            <div class="rex-form-row">
              <p class="rex-form-radio rex-form-label-right">
                <input class="rex-form-radio" type="radio" id="exportdl_download" name="exportdl" value="1"' . $checked1 . ' />
                <label for="exportdl_download">' . rex_i18n::msg('im_export_download_as_file') . '</label>
              </p>
            </div>
            <div class="rex-form-row">
              <p class="rex-form-text">
                <label for="exportfilename">' . rex_i18n::msg('im_export_filename') . '</label>
                <input class="rex-form-text" type="text" id="exportfilename" name="exportfilename" value="' . $exportfilename . '" />
              </p>
            </div>
            <div class="rex-form-row">
              <p class="rex-form-submit">
                <input class="rex-form-submit" type="submit" name="export" value="' . rex_i18n::msg('im_export_db_export') . '" />
              </p>
            </div>
          </div>
        </fieldset>
      </form>
      </div>';


echo rex_view::contentBlock($content);
