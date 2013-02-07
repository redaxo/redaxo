<?php

/**
 * Funktionensammlung für den Medienpool
 *
 * @package redaxo5
 */

/**
 * Erstellt einen Filename der eindeutig ist für den Medienpool
 * @param $FILENAME Dateiname
 */
function rex_mediapool_filename($FILENAME, $doSubindexing = true)
{
  // ----- neuer filename und extension holen
  $NFILENAME = strtolower($FILENAME);
  $NFILENAME = str_replace(array('ä', 'ö', 'ü', 'ß'), array('ae', 'oe', 'ue', 'ss'), $NFILENAME);
  $NFILENAME = preg_replace('/[^a-zA-Z0-9.\-\+]/', '_', $NFILENAME);
  if (strrpos($NFILENAME, '.') != '') {
    $NFILE_NAME = substr($NFILENAME, 0, strlen($NFILENAME) - (strlen($NFILENAME) - strrpos($NFILENAME, '.')));
    $NFILE_EXT  = substr($NFILENAME, strrpos($NFILENAME, '.'), strlen($NFILENAME) - strrpos($NFILENAME, '.'));
  } else {
    $NFILE_NAME = $NFILENAME;
    $NFILE_EXT  = '';
  }

  // ---- ext checken - alle scriptendungen rausfiltern
  if (in_array(ltrim($NFILE_EXT, '.'), rex_addon::get('mediapool')->getProperty('blocked_extensions'))) {
    $NFILE_NAME .= $NFILE_EXT;
    $NFILE_EXT = '.txt';
  }

  $NFILENAME = $NFILE_NAME . $NFILE_EXT;

  if ($doSubindexing) {
    // ----- datei schon vorhanden -> namen aendern -> _1 ..
    if (file_exists(rex_path::media($NFILENAME))) {
      $cnt = 1;
      while (file_exists(rex_path::media($NFILE_NAME . '_' . $cnt . $NFILE_EXT)))
        $cnt++;

      $NFILENAME = $NFILE_NAME . '_' . $cnt . $NFILE_EXT;
    }
  }

  return $NFILENAME;
}

/**
 * Holt ein upgeloadetes File und legt es in den Medienpool
 * Dabei wird kontrolliert ob das File schon vorhanden ist und es
 * wird eventuell angepasst, weiterhin werden die Fileinformationen übergeben
 *
 * @param $FILE
 * @param $rex_file_category
 * @param $FILEINFOS
 * @param $userlogin
 */
function rex_mediapool_saveMedia($FILE, $rex_file_category, $FILEINFOS, $userlogin = null, $doSubindexing = true)
{

  $rex_file_category = (int) $rex_file_category;

  $gc = rex_sql::factory();
  $gc->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media_category WHERE id=' . $rex_file_category);
  if ($gc->getRows() != 1) {
    $rex_file_category = 0;
  }

  $isFileUpload = isset($FILE['tmp_name']);
  if ($isFileUpload) $doSubindexing = true;

  $FILENAME = $FILE['name'];
  $FILESIZE = $FILE['size'];
  $FILETYPE = $FILE['type'];
  $NFILENAME = rex_mediapool_filename($FILENAME, $doSubindexing);
  $message = array();

  // ----- alter/neuer filename
  $srcFile = rex_path::media($FILENAME);
  $dstFile = rex_path::media($NFILENAME);

  $success = true;
  if ($isFileUpload) { // Fileupload?
    if (!@move_uploaded_file($FILE['tmp_name'], $dstFile)) {
      $message[] = rex_i18n::msg('pool_file_movefailed');
      $success = false;
    }
  } else { // Filesync?
    if (!@rename($srcFile, $dstFile)) {
      $message[] = rex_i18n::msg('pool_file_movefailed');
      $success = false;
    }
  }

  if ($success) {
    @chmod($dstFile, rex::getFilePerm());

    // get widht height
    $size = @getimagesize($dstFile);

    if ($FILETYPE == '' && isset($size['mime']))
      $FILETYPE = $size['mime'];

    $FILESQL = rex_sql::factory();
    $FILESQL->setTable(rex::getTablePrefix() . 'media');
    $FILESQL->setValue('filetype', $FILETYPE);
    $FILESQL->setValue('title', $FILEINFOS['title']);
    $FILESQL->setValue('filename', $NFILENAME);
    $FILESQL->setValue('originalname', $FILENAME);
    $FILESQL->setValue('filesize', $FILESIZE);

    if ($size) {
      $FILESQL->setValue('width', $size[0]);
      $FILESQL->setValue('height', $size[1]);
    }

    $FILESQL->setValue('category_id', $rex_file_category);
    $FILESQL->addGlobalCreateFields($userlogin);
    $FILESQL->addGlobalUpdateFields($userlogin);
    $FILESQL->insert();

    if ($isFileUpload) {
      $message[] = rex_i18n::msg('pool_file_added');
    }

    if ($NFILENAME != $FILENAME) {
      $message[] = rex_i18n::rawMsg('pool_file_renamed', $FILENAME, $NFILENAME);
    }

    rex_media_cache::deleteList($rex_file_category);
  }

  $RETURN['title'] = $FILEINFOS['title'];
  $RETURN['type'] = $FILETYPE;
  $RETURN['msg'] = implode('<br />', $message);
  // Aus BC gruenden hier mit int 1/0
  $RETURN['ok'] = $success ? 1 : 0;
  $RETURN['filename'] = $NFILENAME;
  $RETURN['old_filename'] = $FILENAME;

  if ($size) {
    $RETURN['width'] = $size[0];
    $RETURN['height'] = $size[1];
  }

  // ----- EXTENSION POINT
  if ($success)
    rex_extension::registerPoint('MEDIA_ADDED', '', $RETURN);

  return $RETURN;
}


/**
 * Holt ein upgeloadetes File und legt es in den Medienpool
 * Dabei wird kontrolliert ob das File schon vorhanden ist und es
 * wird eventuell angepasst, weiterhin werden die Fileinformationen übergeben
 *
 * @param $FILE
 * @param $rex_file_category
 * @param $FILEINFOS
 * @param $userlogin
 */
function rex_mediapool_updateMedia($FILE, &$FILEINFOS, $userlogin = null)
{

  $RETURN = array();

  $FILESQL = rex_sql::factory();
  // $FILESQL->debugsql = 1;
  $FILESQL->setTable(rex::getTablePrefix() . 'media');
  $FILESQL->setWhere(array('media_id' => $FILEINFOS['file_id']));
  $FILESQL->setValue('title', $FILEINFOS['title']);
  $FILESQL->setValue('category_id', $FILEINFOS['rex_file_category']);

  $msg = '';

  $updated = false;
  if ($_FILES['file_new']['name'] != '' && $_FILES['file_new']['name'] != 'none') {
    $ffilename = $_FILES['file_new']['tmp_name'];
    $ffiletype = $_FILES['file_new']['type'];
    $ffilesize = $_FILES['file_new']['size'];

    $p_new = pathinfo($_FILES['file_new']['name']);
    $p_old = pathinfo($FILEINFOS['filename']);

    // if ($ffiletype == $FILEINFOS["filetype"] || rex_media::compareImageTypes($ffiletype,$FILEINFOS["filetype"]))
    if ($p_new['extension'] == $p_old['extension']) {
      if (move_uploaded_file($ffilename, rex_path::media($FILEINFOS['filename'])) ||
          copy($ffilename, rex_path::media($FILEINFOS['filename']))
      ) {
        $RETURN['msg'] = rex_i18n::msg('pool_file_changed');
        $FILEINFOS['filetype'] = $ffiletype;
        $FILEINFOS['filesize'] = $ffilesize;

        $FILESQL->setValue('filetype', $FILEINFOS['filetype']);
        // $FILESQL->setValue('originalname',$ffilename);
        $FILESQL->setValue('filesize', $FILEINFOS['filesize']);
        if ($size = @getimagesize(rex_path::media($FILEINFOS['filename']))) {
          $FILESQL->setValue('width', $size[0]);
          $FILESQL->setValue('height', $size[1]);
        }
        @chmod(rex_path::media($FILEINFOS['filename']), rex::getFilePerm());
        $updated = true;
      } else {
          $RETURN['msg'] = rex_i18n::msg('pool_file_upload_error');
      }
    } else {
      $RETURN['msg'] = rex_i18n::msg('pool_file_upload_errortype');
    }
  }

  // Aus BC gruenden hier mit int 1/0
  $RETURN['ok'] = $updated ? 1 : 0;
  if (!isset($RETURN['msg'])) {
    $RETURN['msg'] = rex_i18n::msg('pool_file_infos_updated');
    $RETURN['ok'] = 1;
  }
  if ($RETURN['ok'] == 1) {
    $RETURN['filename'] = $FILEINFOS['filename'];
    $RETURN['filetype'] = $FILEINFOS['filetype'];
    $RETURN['media_id'] = $FILEINFOS['file_id'];
  }

  $FILESQL->addGlobalUpdateFields();
  $FILESQL->update();

  rex_media_cache::delete($FILEINFOS['filename']);


/*
$RETURN['title'] = $FILEINFOS['title'];
$RETURN['type'] = $FILETYPE;
$RETURN['msg'] = $message;
// Aus BC gruenden hier mit int 1/0
$RETURN['ok'] = $success ? 1 : 0;
$RETURN['filename'] = $NFILENAME;
$RETURN['old_filename'] = $FILENAME;
*/

  return $RETURN;
}

/**
 * Synchronisiert die Datei $physical_filename des Mediafolders in den
 * Medienpool
 *
 * @param $physical_filename
 * @param $category_id
 * @param $title
 * @param $filesize
 * @param $filetype
 */
function rex_mediapool_syncFile($physical_filename, $category_id, $title, $filesize = null, $filetype = null, $doSubindexing = false)
{
  $abs_file = rex_path::media($physical_filename);

  if (!file_exists($abs_file)) {
    return false;
  }

  if (empty($filesize)) {
    $filesize = filesize($abs_file);
  }

  if (empty($filetype) && function_exists('mime_content_type')) {
    $filetype = mime_content_type($abs_file);
  }

  if (empty($filetype) && function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
    $filetype = finfo_file($finfo, $abs_file);
  }

  $FILE = array();
  $FILE['name'] = $physical_filename;
  $FILE['size'] = $filesize;
  $FILE['type'] = $filetype;

  $FILEINFOS = array();
  $FILEINFOS['title'] = $title;

  $RETURN = rex_mediapool_saveMedia($FILE, $category_id, $FILEINFOS, null, false);
  return $RETURN;
}

/**
 * Ausgabe des Medienpool Formulars
 */
function rex_mediapool_Mediaform($form_title, $button_title, $rex_file_category, $file_chooser, $close_form)
{
  global $subpage, $ftitle, $warning, $info;

  $s = '';

  $cats_sel = new rex_media_category_select();
  $cats_sel->setStyle('class="rex-form-select"');
  $cats_sel->setSize(1);
  $cats_sel->setName('rex_file_category');
  $cats_sel->setId('rex_file_category');
  $cats_sel->addOption(rex_i18n::msg('pool_kats_no'), '0');
  $cats_sel->setAttribute('onchange', 'this.form.submit()');
  $cats_sel->setSelected($rex_file_category);

  if (isset($warning)) {
    if (is_array($warning)) {
      if (count($warning) > 0)
        $s .= rex_view::error(implode('<br />', $warning));
    } elseif ($warning != '') {
      $s .= rex_view::error($warning);
    }
    $warning = '';
  }

  if (isset($info)) {
    if (is_array($info)) {
      if (count($info) > 0)
        $s .= rex_view::success(implode('<br />', $info));
    } elseif ($info != '') {
      $s .= rex_view::success($info);
    }
    $info = '';
  }

  if (!isset($ftitle)) $ftitle = '';

  $add_file = '';
  if ($file_chooser) {
    $devInfos = '';
    if (rex::getUser()->hasPerm('advancedMode[]')) {
      $devInfos =
      '<span class="rex-form-notice">
         ' . rex_i18n::msg('phpini_settings') . ':<br />
         ' . ((rex_ini_get('file_uploads') == 0) ? '<span>' . rex_i18n::msg('pool_upload') . ':</span> <em>' . rex_i18n::msg('pool_upload_disabled') . '</em><br />' : '') . '
         <span>' . rex_i18n::msg('pool_max_uploadsize') . ':</span> ' . rex_file::formattedSize(rex_ini_get('upload_max_filesize')) . '<br />
         <span>' . rex_i18n::msg('pool_max_uploadtime') . ':</span> ' . rex_ini_get('max_input_time') . 's
       </span>';
    }

    $add_file = '
                <div class="rex-form-row">
                  <p class="rex-form-file">
                    <label for="file_new">' . rex_i18n::msg('pool_file_file') . '</label>
                    <input class="rex-form-file" type="file" id="file_new" name="file_new" size="30" />
                    ' . $devInfos . '
                  </p>
                </div>';
  }

  $arg_fields = '';
  foreach (rex_request('args', 'array') as $arg_name => $arg_value) {
    $arg_fields .= '<input type="hidden" name="args[' . $arg_name . ']" value="' . $arg_value . '" />' . "\n";
  }

  $opener_input_field = rex_request('opener_input_field', 'string');
  if ($opener_input_field != '') {
    $arg_fields .= '<input type="hidden" name="opener_input_field" value="' . htmlspecialchars($opener_input_field) . '" />' . "\n";
  }

  $add_submit = '';
  if ($close_form && $opener_input_field != '') {
    $add_submit = '<input type="submit" class="rex-form-submit" name="saveandexit" value="' . rex_i18n::msg('pool_file_upload_get') . '"' . rex::getAccesskey(rex_i18n::msg('pool_file_upload_get'), 'save') . ' />';
  }

  $s .= '
      <div class="rex-form" id="rex-form-mediapool-other">
        <form action="' . rex_url::currentBackendPage() . '" method="post" enctype="multipart/form-data">
          <fieldset class="rex-form-col-1">
            <legend>' . $form_title . '</legend>
            <div class="rex-form-wrapper">
              <input type="hidden" name="media_method" value="add_file" />
              ' . $arg_fields . '

              <div class="rex-form-row">
                <p class="rex-form-text">
                  <label for="ftitle">' . rex_i18n::msg('pool_file_title') . '</label>
                  <input class="rex-form-text" type="text" size="20" id="ftitle" name="ftitle" value="' . htmlspecialchars($ftitle) . '" />
                </p>
              </div>

              <div class="rex-form-row">
                <p class="rex-form-select">
                  <label for="rex_file_category">' . rex_i18n::msg('pool_file_category') . '</label>
                  ' . $cats_sel->get() . '
                </p>
              </div>

              <div class="rex-clearer"></div>';

  // ----- EXTENSION POINT
  $s .= rex_extension::registerPoint('MEDIA_FORM_ADD', '');

  $s .=        $add_file . '
              <div class="rex-form-row">
                <p class="rex-form-submit">
                 <input class="rex-form-submit" type="submit" id="media-form-button" name="save" value="' . $button_title . '"' . rex::getAccesskey($button_title, 'save') . ' />
                 ' . $add_submit . '
                </p>
              </div>

              <div class="rex-clearer"></div>
            </div>
          </fieldset>
        ';

  if ($close_form) {
    $s .= '</form></div>' . "\n";
  }

  return $s;
}

/**
 * Ausgabe des Medienpool Upload-Formulars
 */
function rex_mediapool_Uploadform($rex_file_category)
{
  return rex_mediapool_Mediaform(rex_i18n::msg('pool_file_insert'), rex_i18n::msg('pool_file_upload'), $rex_file_category, true, true);
}

/**
 * Ausgabe des Medienpool Sync-Formulars
 */
function rex_mediapool_Syncform($rex_file_category)
{
  return rex_mediapool_Mediaform(rex_i18n::msg('pool_sync_title'), rex_i18n::msg('pool_sync_button'), $rex_file_category, false, false);
}

/**
 * Fügt die benötigen Assets ein
 *
 * @param $params Extension-Point Parameter
 */
function rex_mediapool_add_assets($params)
{
  $params['subject'] .= '
  <script type="text/javascript" src="' . rex_url::addonAssets('mediapool', 'mediapool.js') . '"></script>
  <script type="text/javascript">
  <!--
  var rex_imageExtensions = ["' . implode('","', rex_addon::get('mediapool')->getProperty('image_extensions')) . '"];
  //-->
  </script>';

  return $params['subject'];
}

/**
 * check if mediatpye(extension) is allowed for upload
 *
 * @param string $filename
 * @param array  $args
 * @return  bool
 */
function rex_mediapool_isAllowedMediaType($filename, array $args = array())
{
  $file_ext = rex_file::extension($filename);

  if ($filename === '' || strpos($file_ext, ' ') !== false || $file_ext === '') {
    return false;
  }

  $blacklist = rex_mediapool_getMediaTypeBlacklist();
  $whitelist = rex_mediapool_getMediaTypeWhitelist($args);

  if (in_array($file_ext, $blacklist)) {
    return false;
  }
  if (count($whitelist) > 0 && !in_array($file_ext, $whitelist)) {
    return false;
  }
  return true;
}

/**
 * get whitelist of mediatypes(extensions) given via media widget "types" param
 *
 * @param array $args widget params
 * @return  array         whitelisted extensions
 */
function rex_mediapool_getMediaTypeWhitelist($args = array())
{
  $blacklist = rex_mediapool_getMediaTypeBlacklist();

  $whitelist = array();
  if (isset($args['types'])) {
    foreach (explode(',', $args['types']) as $ext) {
      $ext = ltrim($ext, '.');
      if (!in_array($ext, $blacklist)) { // whitelist cannot override any blacklist entry from master
        $whitelist[] = $ext;
      }
    }
  }
  return $whitelist;
}

/**
 * return global mediatype blacklist from master.inc
 *
 * @return  array  blacklisted mediatype extensions
 */
function rex_mediapool_getMediaTypeBlacklist()
{
  return rex_addon::get('mediapool')->getProperty('blocked_extensions');
}
