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
    $NFILENAME = rex_string::normalize($FILENAME, '_', '.-@');

    if ('.' === $NFILENAME[0]) {
        $NFILENAME[0] = '_';
    }

    if ($pos = strrpos($NFILENAME, '.')) {
        $nFILENAME = substr($NFILENAME, 0, strlen($NFILENAME) - (strlen($NFILENAME) - $pos));
        $nFILEEXT = substr($NFILENAME, $pos, strlen($NFILENAME) - $pos);
    } else {
        $nFILENAME = $NFILENAME;
        $nFILEEXT = '';
    }

    // ---- ext checken - alle scriptendungen rausfiltern
    if (!rex_mediapool_isAllowedMediaType($NFILENAME)) {
        // make sure we dont add a 2nd file-extension to the file,
        // because some webspaces execute files like file.php.txt as a php script
        $nFILENAME .= str_replace('.', '_', $nFILEEXT);
        $nFILEEXT = '.txt';
    }

    $NFILENAME = $nFILENAME . $nFILEEXT;

    if ($doSubindexing || $FILENAME != $NFILENAME) {
        // ----- datei schon vorhanden -> namen aendern -> _1 ..
        $cnt = 0;
        while (is_file(rex_path::media($NFILENAME)) || rex_media::get($NFILENAME)) {
            ++$cnt;
            $NFILENAME = $nFILENAME . '_' . $cnt . $nFILEEXT;
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
 * @param int    $rexFileCategory
 * @param array  $FILEINFOS
 * @param string $userlogin
 * @param bool   $doSubindexing
 *
 * @return array
 */
function rex_mediapool_saveMedia($FILE, $rexFileCategory, $FILEINFOS, $userlogin = null, $doSubindexing = true)
{
    $rexFileCategory = (int) $rexFileCategory;

    $gc = rex_sql::factory();
    $gc->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media_category WHERE id=' . $rexFileCategory);
    if (1 != $gc->getRows()) {
        $rexFileCategory = 0;
    }

    $isFileUpload = isset($FILE['tmp_name']);
    if ($isFileUpload) {
        $doSubindexing = true;
    }

    $FILENAME = $FILE['name'];
    $FILESIZE = $FILE['size'];
    $NFILENAME = rex_mediapool_filename($FILENAME, $doSubindexing);
    $message = [];

    // ----- alter/neuer filename
    $srcFile = rex_path::media($FILENAME);
    $dstFile = rex_path::media($NFILENAME);

    $success = true;
    $FILETYPE = $isFileUpload ? rex_file::mimeType($FILE['tmp_name']) : rex_file::mimeType($srcFile);

    // Bevor die Datei engueltig in den Medienpool uebernommen wird, koennen
    // Addons ueber einen Extension-Point ein Veto einlegen.
    // Sobald ein Addon eine negative Entscheidung getroffen hat, sollten
    // Addons, fuer die der Extension-Point spaeter ausgefuehrt wird, diese
    // Entscheidung respektieren
    $errorMessage = rex_extension::registerPoint(new rex_extension_point('MEDIA_ADD', '', [
        'file' => $FILE,
        'title' => $FILEINFOS['title'],
        'filename' => $NFILENAME,
        'old_filename' => $FILENAME,
        'is_upload' => $isFileUpload,
        'category_id' => $rexFileCategory,
        'type' => $FILETYPE,
    ]));

    if ($errorMessage) {
        // ein Addon hat die Fehlermeldung gesetzt, dem Upload also faktisch widersprochen
        $success = false;
        $message[] = $errorMessage;
    } elseif ($isFileUpload) { // Fileupload?
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

        if ('' == $FILETYPE && isset($size['mime'])) {
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

        $FILESQL->setValue('category_id', $rexFileCategory);
        $FILESQL->addGlobalCreateFields($userlogin);
        $FILESQL->addGlobalUpdateFields($userlogin);
        $FILESQL->insert();

        if ($isFileUpload) {
            $message[] = rex_i18n::msg('pool_file_added');
        }

        if ($NFILENAME != $FILENAME) {
            $message[] = rex_i18n::rawMsg('pool_file_renamed', $FILENAME, $NFILENAME);
        }

        rex_media_cache::deleteList($rexFileCategory);
    }

    $RETURN = [];
    $RETURN['title'] = $FILEINFOS['title'];
    $RETURN['type'] = $FILETYPE;
    $RETURN['msg'] = implode('<br />', $message);
    // Aus BC gruenden hier mit int 1/0
    $RETURN['ok'] = $success ? 1 : 0;
    $RETURN['filename'] = $NFILENAME;
    $RETURN['old_filename'] = $FILENAME;
    $RETURN['category_id'] = $rexFileCategory;

    if (isset($size) && $size) {
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
    if ('' != $FILE['name'] && 'none' != $FILE['name']) {
        $ffilename = $FILE['tmp_name'];
        $ffiletype = rex_file::mimeType($FILE['tmp_name']);
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
    if (1 == $RETURN['ok']) {
        $RETURN['filename'] = $FILEINFOS['filename'];
        $RETURN['filetype'] = $FILEINFOS['filetype'];
        $RETURN['id'] = $FILEINFOS['file_id'];
        $RETURN['category_id'] = $FILEINFOS['rex_file_category'];
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
 * @param string      $physicalFilename
 * @param int         $categoryId
 * @param string      $title
 * @param null|int    $filesize
 * @param null|string $filetype
 * @param null|string $userlogin
 *
 * @return array
 */
function rex_mediapool_syncFile($physicalFilename, $categoryId, $title, $filesize = null, $filetype = null, $userlogin = null)
{
    $absFile = rex_path::media($physicalFilename);

    if (!is_file($absFile)) {
        throw new rex_exception(sprintf('File "%s" does not exist.', $absFile));
    }

    if (empty($filesize)) {
        $filesize = filesize($absFile);
    }

    if (empty($filetype)) {
        $filetype = rex_file::mimeType($absFile);
    }

    $FILE = [];
    $FILE['name'] = $physicalFilename;
    $FILE['size'] = $filesize;
    $FILE['type'] = $filetype;

    $FILEINFOS = [];
    $FILEINFOS['title'] = $title;

    // check for previous 6th (unused) parameter $doSubindexing
    if (is_bool($userlogin)) {
        $userlogin = null;
    }

    $RETURN = rex_mediapool_saveMedia($FILE, $categoryId, $FILEINFOS, $userlogin, false);
    return $RETURN;
}

/**
 * @param string $filename
 *
 * @return array
 *
 * @psalm-return array{ok: bool, msg: string}
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
 * @param string $filename
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
        foreach ($res as $artArr) {
            $aid = $artArr['article_id'];
            $clang = $artArr['clang_id'];
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
 *
 * @return string
 */
function rex_mediapool_Mediaform($formTitle, $buttonTitle, $rexFileCategory, $fileChooser, $closeForm)
{
    global $ftitle, $warning, $info;

    $s = '';

    $catsSel = new rex_media_category_select();
    $catsSel->setStyle('class="form-control"');
    $catsSel->setSize(1);
    $catsSel->setName('rex_file_category');
    $catsSel->setId('rex-mediapool-category');
    $catsSel->setAttribute('class', 'selectpicker form-control');
    $catsSel->setAttribute('data-live-search', 'true');
    $catsSel->setAttribute('onchange', 'this.form.submit()');
    $catsSel->setSelected($rexFileCategory);

    if (rex::getUser()->getComplexPerm('media')->hasAll()) {
        $catsSel->addOption(rex_i18n::msg('pool_kats_no'), '0');
    }

    if (isset($warning)) {
        if (is_array($warning)) {
            if (count($warning) > 0) {
                $s .= rex_view::error(implode('<br />', $warning));
            }
        } elseif ('' != $warning) {
            $s .= rex_view::error($warning);
        }
        $warning = '';
    }

    if (isset($info)) {
        if (is_array($info)) {
            if (count($info) > 0) {
                $s .= rex_view::success(implode('<br />', $info));
            }
        } elseif ('' != $info) {
            $s .= rex_view::success($info);
        }
        $info = '';
    }

    if (!isset($ftitle)) {
        $ftitle = '';
    }

    $argFields = '';
    foreach (rex_request('args', 'array') as $argName => $argValue) {
        $argFields .= '<input type="hidden" name="args[' . rex_escape($argName) . ']" value="' . rex_escape($argValue) . '" />' . "\n";
    }

    $openerInputField = rex_request('opener_input_field', 'string');
    if ('' != $openerInputField) {
        $argFields .= '<input type="hidden" name="opener_input_field" value="' . rex_escape($openerInputField) . '" />' . "\n";
    }

    $addSubmit = '';
    if ($closeForm && '' != $openerInputField) {
        $addSubmit = '<button class="btn btn-save" type="submit" name="saveandexit" value="' . rex_i18n::msg('pool_file_upload_get') . '"' . rex::getAccesskey(rex_i18n::msg('save_and_close_tooltip'), 'save') . '>' . rex_i18n::msg('pool_file_upload_get') . '</button>';
    }

    $panel = '';
    $panel .= '<fieldset>';
    $formElements = [];

    $e = [];
    $e['label'] = '<label for="rex-mediapool-category">' . rex_i18n::msg('pool_file_category') . '</label>';
    $e['field'] = $catsSel->get();
    $formElements[] = $e;

    $e = [];
    $e['label'] = '<label for="rex-mediapool-title">' . rex_i18n::msg('pool_file_title') . '</label>';
    $e['field'] = '<input class="form-control" type="text" id="rex-mediapool-title" name="ftitle" value="' . rex_escape($ftitle) . '" />';
    $formElements[] = $e;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $panel .= $fragment->parse('core/form/form.php');

    $panel .= rex_extension::registerPoint(new rex_extension_point('MEDIA_FORM_ADD', ''));

    if ($fileChooser) {
        $e = [];
        $e['label'] = '<label for="rex-mediapool-choose-file">' . rex_i18n::msg('pool_file_file') . '</label>';
        $e['field'] = '<input id="rex-mediapool-choose-file" type="file" name="file_new" />';
        $e['after'] = '<h3>' . rex_i18n::msg('phpini_settings') . '</h3>
                        <dl class="dl-horizontal text-left">
                        ' . ((0 == rex_ini_get('file_uploads')) ? '<dt><span class="text-warning">' . rex_i18n::msg('pool_upload') . '</span></dt><dd><span class="text-warning">' . rex_i18n::msg('pool_upload_disabled') . '</span></dd>' : '') . '
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
    $e['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="save" value="' . $buttonTitle . '"' . rex::getAccesskey($buttonTitle, 'save') . '>' . $buttonTitle . '</button>';
    $formElements[] = $e;

    $e = [];
    $e['field'] = $addSubmit;
    $formElements[] = $e;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $buttons = $fragment->parse('core/form/submit.php');

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $formTitle, false);
    $fragment->setVar('body', $panel, false);
    $fragment->setVar('buttons', $buttons, false);
    $content = $fragment->parse('core/page/section.php');

    $s .= ' <form action="' . rex_url::currentBackendPage() . '" method="post" enctype="multipart/form-data" data-pjax="false">
                ' . rex_csrf_token::factory('mediapool')->getHiddenField() . '
                <fieldset>
                    <input type="hidden" name="media_method" value="add_file" />
                    ' . $argFields . '
                    ' . $content . '
                </fieldset>
            ';

    if ($closeForm) {
        $s .= '</form>' . "\n";
    }

    return $s;
}

/**
 * Ausgabe des Medienpool Upload-Formulars.
 */
function rex_mediapool_Uploadform($rexFileCategory)
{
    return rex_mediapool_Mediaform(rex_i18n::msg('pool_file_insert'), rex_i18n::msg('pool_file_upload'), $rexFileCategory, true, true);
}

/**
 * Ausgabe des Medienpool Sync-Formulars.
 */
function rex_mediapool_Syncform($rexFileCategory)
{
    return rex_mediapool_Mediaform(rex_i18n::msg('pool_sync_title'), rex_i18n::msg('pool_sync_button'), $rexFileCategory, false, false);
}

/**
 * check if mediatpye(extension) is allowed for upload.
 *
 * @param string $filename
 *
 * @return bool
 */
function rex_mediapool_isAllowedMediaType($filename, array $args = [])
{
    $fileExt = mb_strtolower(rex_file::extension($filename));

    if ('' === $filename || false !== strpos($fileExt, ' ') || '' === $fileExt) {
        return false;
    }

    if (0 === strpos($fileExt, 'php')) {
        return false;
    }

    $blacklist = rex_mediapool_getMediaTypeBlacklist();
    foreach ($blacklist as $blackExtension) {
        // blacklisted extensions are not allowed within filenames, to prevent double extension vulnerabilities:
        // -> some webspaces execute files named file.php.txt as php
        if (false !== strpos($filename, '.'. $blackExtension)) {
            return false;
        }
    }

    $whitelist = rex_mediapool_getMediaTypeWhitelist($args);
    return !count($whitelist) || in_array($fileExt, $whitelist);
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

    $mimeType = rex_file::mimeType($path);

    return in_array($mimeType, $whitelist[$extension]);
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
