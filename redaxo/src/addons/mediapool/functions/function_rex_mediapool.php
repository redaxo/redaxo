<?php

/**
 * Funktionensammlung für den Medienpool.
 *
 * @package redaxo\mediapool
 */

/**
 * Erstellt einen Filename der eindeutig ist für den Medienpool.
 *
 * @param string $FILENAME      Dateiname
 * @param bool   $doSubindexing
 *
 * @return string
 */
function rex_mediapool_filename($FILENAME, $doSubindexing = true)
{
    // ----- neuer filename und extension holen
    $NFILENAME = rex_string::normalize($FILENAME, '_', '.-');

    if ('.' === $NFILENAME[0]) {
        $NFILENAME[0] = '_';
    }

    if (strrpos($NFILENAME, '.') != '') {
        $NFILE_NAME = substr($NFILENAME, 0, strlen($NFILENAME) - (strlen($NFILENAME) - strrpos($NFILENAME, '.')));
        $NFILE_EXT = substr($NFILENAME, strrpos($NFILENAME, '.'), strlen($NFILENAME) - strrpos($NFILENAME, '.'));
    } else {
        $NFILE_NAME = $NFILENAME;
        $NFILE_EXT = '';
    }

    // ---- ext checken - alle scriptendungen rausfiltern
    if (!rex_mediapool_isAllowedMediaType($NFILENAME)) {
        $NFILE_NAME .= $NFILE_EXT;
        $NFILE_EXT = '.txt';
    }

    // ---- multiple extension check
    foreach (rex_addon::get('mediapool')->getProperty('blocked_extensions') as $ext) {
        $NFILE_NAME = str_replace($ext . '.', $ext . '_.', $NFILE_NAME);
    }

    $NFILENAME = $NFILE_NAME . $NFILE_EXT;

    if ($doSubindexing || $FILENAME != $NFILENAME) {
        // ----- datei schon vorhanden -> namen aendern -> _1 ..
        if (file_exists(rex_path::media($NFILENAME))) {
            $cnt = 1;
            while (file_exists(rex_path::media($NFILE_NAME . '_' . $cnt . $NFILE_EXT))) {
                ++$cnt;
            }

            $NFILENAME = $NFILE_NAME . '_' . $cnt . $NFILE_EXT;
        }
    }

    return $NFILENAME;
}

/**
 * Holt ein upgeloadetes File und legt es in den Medienpool
 * Dabei wird kontrolliert ob das File schon vorhanden ist und es
 * wird eventuell angepasst, weiterhin werden die Fileinformationen übergeben.
 *
 * @param array  $FILE
 * @param int    $rex_file_category
 * @param array  $FILEINFOS
 * @param string $userlogin
 * @param bool   $doSubindexing
 *
 * @return array
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
    if ($isFileUpload) {
        $doSubindexing = true;
    }

    $FILENAME = $FILE['name'];
    $FILESIZE = $FILE['size'];
    $FILETYPE = $FILE['type'];
    $NFILENAME = rex_mediapool_filename($FILENAME, $doSubindexing);
    $message = [];

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

        if ($FILETYPE == '' && isset($size['mime'])) {
            $FILETYPE = $size['mime'];
        }

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

    if (isset($size)) {
        $RETURN['width'] = $size[0];
        $RETURN['height'] = $size[1];
    }

    // ----- EXTENSION POINT
    if ($success) {
        rex_extension::registerPoint(new rex_extension_point('MEDIA_ADDED', '', $RETURN));
    }

    return $RETURN;
}

/**
 * Holt ein upgeloadetes File und legt es in den Medienpool
 * Dabei wird kontrolliert ob das File schon vorhanden ist und es
 * wird eventuell angepasst, weiterhin werden die Fileinformationen übergeben.
 *
 * @param array  $FILE
 * @param array  $FILEINFOS
 * @param string $userlogin
 *
 * @return array
 */
function rex_mediapool_updateMedia($FILE, &$FILEINFOS, $userlogin = null)
{
    $RETURN = [];

    $FILESQL = rex_sql::factory();
    // $FILESQL->setDebug();
    $FILESQL->setTable(rex::getTablePrefix() . 'media');
    $FILESQL->setWhere(['id' => $FILEINFOS['file_id']]);
    $FILESQL->setValue('title', $FILEINFOS['title']);
    $FILESQL->setValue('category_id', $FILEINFOS['rex_file_category']);

    $updated = false;
    if ($FILE['name'] != '' && $FILE['name'] != 'none') {
        $ffilename = $FILE['tmp_name'];
        $ffiletype = $FILE['type'];
        $ffilesize = $FILE['size'];

        $extensionNew = mb_strtolower(pathinfo($FILE['name'], PATHINFO_EXTENSION));
        $extensionOld = mb_strtolower(pathinfo($FILEINFOS['filename'], PATHINFO_EXTENSION));

        static $jpgExtensions = ['jpg', 'jpeg'];

        if (
            $extensionNew == $extensionOld ||
            in_array($extensionNew, $jpgExtensions) && in_array($extensionOld, $jpgExtensions)
        ) {
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
    } else {
        if ($size = @getimagesize(rex_path::media($FILEINFOS['filename']))) {
            $FILESQL->setValue('width', $size[0]);
            $FILESQL->setValue('height', $size[1]);
        }
        $FILESQL->setValue('filesize', @filesize(rex_path::media($FILEINFOS['filename'])));
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
        $RETURN['id'] = $FILEINFOS['file_id'];
    }

    $FILESQL->addGlobalUpdateFields($userlogin);
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

    // ----- EXTENSION POINT
    if ($RETURN['ok']) {
        rex_extension::registerPoint(new rex_extension_point('MEDIA_UPDATED', '', $RETURN));
    }

    return $RETURN;
}

/**
 * Synchronisiert die Datei $physical_filename des Mediafolders in den
 * Medienpool.
 *
 * @param string      $physical_filename
 * @param int         $category_id
 * @param string      $title
 * @param null|int    $filesize
 * @param null|string $filetype
 * @param null|string $userlogin
 *
 * @return bool|array
 */
function rex_mediapool_syncFile($physical_filename, $category_id, $title, $filesize = null, $filetype = null, $userlogin = null)
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

    $FILE = [];
    $FILE['name'] = $physical_filename;
    $FILE['size'] = $filesize;
    $FILE['type'] = $filetype;

    $FILEINFOS = [];
    $FILEINFOS['title'] = $title;

    // check for previous 6th (unused) parameter $doSubindexing
    if (is_bool($userlogin)) {
        $userlogin = null;
    }

    $RETURN = rex_mediapool_saveMedia($FILE, $category_id, $FILEINFOS, $userlogin, false);
    return $RETURN;
}

/**
 * @param string $filename
 *
 * @return bool
 */
function rex_mediapool_deleteMedia($filename)
{
    if ($uses = rex_mediapool_mediaIsInUse($filename)) {
        $msg = '<strong>' . rex_i18n::msg('pool_file_delete_error', $filename) . ' '
            . rex_i18n::msg('pool_object_in_use_by') . '</strong><br />' . $uses;
        return ['ok' => false, 'msg' => $msg];
    }

    $sql = rex_sql::factory();
    $sql->setQuery('DELETE FROM ' . rex::getTable('media') . ' WHERE filename = ? LIMIT 1', [$filename]);

    rex_file::delete(rex_path::media($filename));

    rex_media_cache::delete($filename);

    rex_extension::registerPoint(new rex_extension_point('MEDIA_DELETED', '', [
        'filename' => $filename,
    ]));

    return ['ok' => true, 'msg' => rex_i18n::msg('pool_file_deleted')];
}

/**
 * @param $filename
 *
 * @return bool|string
 */
function rex_mediapool_mediaIsInUse($filename)
{
    $sql = rex_sql::factory();

    // FIXME move structure stuff into structure addon
    $values = [];
    for ($i = 1; $i < 21; ++$i) {
        $values[] = 'value' . $i . ' REGEXP ' . $sql->escape('(^|[^[:alnum:]+_-])'.$filename);
    }

    $files = [];
    $filelists = [];
    $escapedFilename = $sql->escape($filename);
    for ($i = 1; $i < 11; ++$i) {
        $files[] = 'media' . $i . ' = ' . $escapedFilename;
        $filelists[] = 'FIND_IN_SET(' . $escapedFilename . ', medialist' . $i . ')';
    }

    $where = '';
    $where .= implode(' OR ', $files) . ' OR ';
    $where .= implode(' OR ', $filelists) . ' OR ';
    $where .= implode(' OR ', $values);
    $query = 'SELECT DISTINCT article_id, clang_id FROM ' . rex::getTablePrefix() . 'article_slice WHERE ' . $where;

    $warning = [];
    $res = $sql->getArray($query);
    if ($sql->getRows() > 0) {
        $warning[0] = rex_i18n::msg('pool_file_in_use_articles') . '<br /><ul>';
        foreach ($res as $art_arr) {
            $aid = $art_arr['article_id'];
            $clang = $art_arr['clang_id'];
            $ooa = rex_article::get($aid, $clang);
            $name = $ooa->getName();
            $warning[0] .= '<li><a href="javascript:openPage(\'' . rex_url::backendPage('content', ['article_id' => $aid, 'mode' => 'edit', 'clang' => $clang]) . '\')">' . $name . '</a></li>';
        }
        $warning[0] .= '</ul>';
    }

    // ----- EXTENSION POINT
    $warning = rex_extension::registerPoint(new rex_extension_point('MEDIA_IS_IN_USE', $warning, [
        'filename' => $filename,
    ]));

    if (!empty($warning)) {
        return implode('<br />', $warning);
    }

    return false;
}

/**
 * Ausgabe des Medienpool Formulars.
 */
function rex_mediapool_Mediaform($form_title, $button_title, $rex_file_category, $file_chooser, $close_form)
{
    global $ftitle, $warning, $info;

    $s = '';

    $cats_sel = new rex_media_category_select();
    $cats_sel->setStyle('class="form-control"');
    $cats_sel->setSize(1);
    $cats_sel->setName('rex_file_category');
    $cats_sel->setId('rex-mediapool-category');
    $cats_sel->setAttribute('class', 'selectpicker form-control');
    $cats_sel->setAttribute('data-live-search', 'true');
    $cats_sel->setAttribute('onchange', 'this.form.submit()');
    $cats_sel->setSelected($rex_file_category);

    if (rex::getUser()->getComplexPerm('media')->hasAll()) {
        $cats_sel->addOption(rex_i18n::msg('pool_kats_no'), '0');
    }

    if (isset($warning)) {
        if (is_array($warning)) {
            if (count($warning) > 0) {
                $s .= rex_view::error(implode('<br />', $warning));
            }
        } elseif ($warning != '') {
            $s .= rex_view::error($warning);
        }
        $warning = '';
    }

    if (isset($info)) {
        if (is_array($info)) {
            if (count($info) > 0) {
                $s .= rex_view::success(implode('<br />', $info));
            }
        } elseif ($info != '') {
            $s .= rex_view::success($info);
        }
        $info = '';
    }

    if (!isset($ftitle)) {
        $ftitle = '';
    }

    $arg_fields = '';
    foreach (rex_request('args', 'array') as $arg_name => $arg_value) {
        $arg_fields .= '<input type="hidden" name="args[' . htmlspecialchars($arg_name) . ']" value="' . htmlspecialchars($arg_value) . '" />' . "\n";
    }

    $opener_input_field = rex_request('opener_input_field', 'string');
    if ($opener_input_field != '') {
        $arg_fields .= '<input type="hidden" name="opener_input_field" value="' . htmlspecialchars($opener_input_field) . '" />' . "\n";
    }

    $add_submit = '';
    if ($close_form && $opener_input_field != '') {
        $add_submit = '<button class="btn btn-save" type="submit" name="saveandexit" value="' . rex_i18n::msg('pool_file_upload_get') . '"' . rex::getAccesskey(rex_i18n::msg('pool_file_upload_get'), 'save') . '>' . rex_i18n::msg('pool_file_upload_get') . '</button>';
    }

    $panel = '';
    $panel .= '<fieldset>';
    $formElements = [];

    $e = [];
    $e['label'] = '<label for="rex-mediapool-category">' . rex_i18n::msg('pool_file_category') . '</label>';
    $e['field'] = $cats_sel->get();
    $formElements[] = $e;

    $e = [];
    $e['label'] = '<label for="rex-mediapool-title">' . rex_i18n::msg('pool_file_title') . '</label>';
    $e['field'] = '<input class="form-control" type="text" id="rex-mediapool-title" name="ftitle" value="' . htmlspecialchars($ftitle) . '" />';
    $formElements[] = $e;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $panel .= $fragment->parse('core/form/form.php');

    $panel .= rex_extension::registerPoint(new rex_extension_point('MEDIA_FORM_ADD', ''));

    if ($file_chooser) {
        $e = [];
        $e['label'] = '<label for="rex-mediapool-choose-file">' . rex_i18n::msg('pool_file_file') . '</label>';
        $e['field'] = '<input id="rex-mediapool-choose-file" type="file" name="file_new" />';
        $e['after'] = '<h3>' . rex_i18n::msg('phpini_settings') . '</h3>
                        <dl class="dl-horizontal text-left">
                        ' . ((rex_ini_get('file_uploads') == 0) ? '<dt><span class="text-warning">' . rex_i18n::msg('pool_upload') . '</span></dt><dd><span class="text-warning">' . rex_i18n::msg('pool_upload_disabled') . '</span></dd>' : '') . '
                            <dt>' . rex_i18n::msg('pool_max_uploadsize') . ':</dt><dd>' . rex_formatter::bytes(rex_ini_get('upload_max_filesize')) . '</dd>
                            <dt>' . rex_i18n::msg('pool_max_uploadtime') . ':</dt><dd>' . rex_ini_get('max_input_time') . 's</dd>
                        </dl>';

        $fragment = new rex_fragment();
        $fragment->setVar('elements', [$e], false);
        $panel .= $fragment->parse('core/form/form.php');
    }
    $panel .= '</fieldset>';

    $formElements = [];

    $e = [];
    $e['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="save" value="' . $button_title . '"' . rex::getAccesskey($button_title, 'save') . '>' . $button_title . '</button>';
    $formElements[] = $e;

    $e = [];
    $e['field'] = $add_submit;
    $formElements[] = $e;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $buttons = $fragment->parse('core/form/submit.php');

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $form_title, false);
    $fragment->setVar('body', $panel, false);
    $fragment->setVar('buttons', $buttons, false);
    $content = $fragment->parse('core/page/section.php');

    $s .= ' <form action="' . rex_url::currentBackendPage() . '" method="post" enctype="multipart/form-data" data-pjax="false">
                ' . rex_csrf_token::factory('mediapool')->getHiddenField() . '
                <fieldset>
                    <input type="hidden" name="media_method" value="add_file" />
                    ' . $arg_fields . '
                    ' . $content . '
                </fieldset>
            ';

    if ($close_form) {
        $s .= '</form>' . "\n";
    }

    return $s;
}

/**
 * Ausgabe des Medienpool Upload-Formulars.
 */
function rex_mediapool_Uploadform($rex_file_category)
{
    return rex_mediapool_Mediaform(rex_i18n::msg('pool_file_insert'), rex_i18n::msg('pool_file_upload'), $rex_file_category, true, true);
}

/**
 * Ausgabe des Medienpool Sync-Formulars.
 */
function rex_mediapool_Syncform($rex_file_category)
{
    return rex_mediapool_Mediaform(rex_i18n::msg('pool_sync_title'), rex_i18n::msg('pool_sync_button'), $rex_file_category, false, false);
}

/**
 * check if mediatpye(extension) is allowed for upload.
 *
 * @param string $filename
 * @param array  $args
 *
 * @return bool
 */
function rex_mediapool_isAllowedMediaType($filename, array $args = [])
{
    $file_ext = mb_strtolower(rex_file::extension($filename));

    if ($filename === '' || strpos($file_ext, ' ') !== false || $file_ext === '') {
        return false;
    }

    if (0 === strpos($file_ext, 'php')) {
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
 * Checks file against optional whitelist from property `allowed_mime_types`.
 *
 * @param string      $path     Path to the physical file
 * @param null|string $filename Optional filename, will be used for extracting the file extension.
 *                              If not given, the extension is extracted from `$path`.
 *
 * @return bool
 */
function rex_mediapool_isAllowedMimeType($path, $filename = null)
{
    $whitelist = rex_addon::get('mediapool')->getProperty('allowed_mime_types');

    if (!$whitelist) {
        return true;
    }

    $extension = mb_strtolower(rex_file::extension($filename ?: $path));

    if (!isset($whitelist[$extension])) {
        return false;
    }

    $mime_type = mime_content_type($path);

    return in_array($mime_type, $whitelist[$extension]);
}

/**
 * get whitelist of mediatypes(extensions) given via media widget "types" param.
 *
 * @param array $args widget params
 *
 * @return array whitelisted extensions
 */
function rex_mediapool_getMediaTypeWhitelist($args = [])
{
    $blacklist = rex_mediapool_getMediaTypeBlacklist();

    $whitelist = [];
    if (isset($args['types'])) {
        foreach (explode(',', $args['types']) as $ext) {
            $ext = ltrim($ext, '.');
            $ext = mb_strtolower($ext);
            if (!in_array($ext, $blacklist)) { // whitelist cannot override any blacklist entry from master
                $whitelist[] = $ext;
            }
        }
    }
    return $whitelist;
}

/**
 * return global mediatype blacklist from master.inc.
 *
 * @return array blacklisted mediatype extensions
 */
function rex_mediapool_getMediaTypeBlacklist()
{
    return rex_addon::get('mediapool')->getProperty('blocked_extensions');
}
