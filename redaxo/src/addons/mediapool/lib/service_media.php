<?php

/**
 * @package redaxo\mediapool
 */
class rex_media_service
{
    /**
     * Holt ein upgeloadetes File und legt es in den Medienpool
     * Dabei wird kontrolliert ob das File schon vorhanden ist und es
     * wird eventuell angepasst, weiterhin werden die Fileinformationen übergeben.
     *
     * @param array  $data
     * @param string $userlogin
     * @param bool   $doSubindexing // echte Dateinamen anpassen, falls schon vorhanden
     * @param array   $whitelistTypes
     */
    public static function addMedia($data, $userlogin = null, $doSubindexing = true, $whitelistTypes = []): array
    {
        if (!is_array($data)) {
            throw new rex_api_exception('Expecting $data to be an array!');
        }

        if ('' == $data['file']['name'] || 'none' == $data['file']['name'] || '' == $data['file']['path']) {
            throw new rex_api_exception(rex_i18n::msg('pool_file_not_found'));
        }

        if (!rex_mediapool::isAllowedMediaType($data['file']['name'], $whitelistTypes)) {
            $warning = rex_i18n::msg('pool_file_mediatype_not_allowed') . ' <code>' . rex_file::extension($data['file']['name']) . '</code>';
            $whitelist = rex_mediapool::getMediaTypeWhitelist($whitelistTypes);
            $warning .= count($whitelist) > 0
                    ? '<br />' . rex_i18n::msg('pool_file_allowed_mediatypes') . ' <code>' . rtrim(implode('</code>, <code>', $whitelist), ', ') . '</code>'
                    : '<br />' . rex_i18n::msg('pool_file_banned_mediatypes') . ' <code>' . rtrim(implode('</code>, <code>', rex_mediapool::getMediaTypeBlacklist()), ', ') . '</code>';

            throw new rex_api_exception($warning);
        }

        if (!rex_mediapool::isAllowedMimeType($data['file']['path'], $data['file']['name'])) {
            $warning = rex_i18n::msg('pool_file_mediatype_not_allowed') . ' <code>' . rex_file::extension($data['file']['name']) . '</code> (<code>' . rex_file::mimeType($data['file']['path']) . '</code>)';
            throw new rex_api_exception($warning);
        }

        $categories = (array) $data['categories'];
        $tags = $data['tags'];
        $status = (int) $data['status'];
        $title = (string) $data['title'];

        $isFileUpload = isset($data['file']['path']);
        if ($isFileUpload) {
            $doSubindexing = true;
        }

        $data['file']['name_new'] = rex_mediapool::filename($data['file']['name'], $doSubindexing);

        // ----- alter/neuer filename
        $srcFile = rex_path::media($data['file']['name']);
        $dstFile = rex_path::media($data['file']['name_new']);

        $data['file']['type'] = $isFileUpload ? rex_file::mimeType($data['file']['path']) : rex_file::mimeType($srcFile);

        // Bevor die Datei engueltig in den Medienpool uebernommen wird, koennen
        // Addons ueber einen Extension-Point ein Veto einlegen.
        // Sobald ein Addon eine negative Entscheidung getroffen hat, sollten
        // Addons, fuer die der Extension-Point spaeter ausgefuehrt wird, diese
        // Entscheidung respektieren
        $errorMessage = rex_extension::registerPoint(new rex_extension_point('MEDIA_ADD', null, [
            'file' => $data['file'],
            'title' => $title,
            'filename' => $data['file']['name_new'],
            'old_filename' => $data['file']['name'],
            'is_upload' => $isFileUpload,
            'categories' => implode(',', $categories),
            'tags' => $tags,
            'status' => $status,
            'type' => $data['file']['type'],
        ]));

        if ($errorMessage) {
            // ein Addon hat die Fehlermeldung gesetzt, dem Upload also faktisch widersprochen
            throw new rex_api_exception($errorMessage);
        }
        if ($isFileUpload) { // Fileupload?
            if (!@move_uploaded_file($data['file']['path'], $dstFile)) {
                throw new rex_api_exception(rex_i18n::msg('pool_file_movefailed'));
            }
        } else { // Filesync?
            if (!@rename($srcFile, $dstFile)) {
                throw new rex_api_exception(rex_i18n::msg('pool_file_movefailed'));
            }
        }

        @chmod($dstFile, rex::getFilePerm());

        // get widht height
        $size = @getimagesize($dstFile);

        if ('' == $data['file']['type'] && isset($size['mime'])) {
            $data['file']['type'] = $size['mime'];
        }

        $saveObject = rex_sql::factory();
        $saveObject->setTable(rex::getTablePrefix() . 'media');
        $saveObject->setValue('filetype', $data['file']['type']);
        $saveObject->setValue('title', $title);
        $saveObject->setValue('filename', $data['file']['name_new']);
        $saveObject->setValue('originalname', $data['file']['name']);
        $saveObject->setValue('filesize', $data['file']['size']);

        if ($size) {
            $saveObject->setValue('width', $size[0]);
            $saveObject->setValue('height', $size[1]);
        }

        $saveObject->setValue('categories', implode(',', $categories));
        $saveObject->setValue('tags', $tags);
        $saveObject->setValue('status', $status);
        $saveObject->addGlobalCreateFields($userlogin);
        $saveObject->addGlobalUpdateFields($userlogin);
        $saveObject->insert();

        $message = [];

        if ($isFileUpload) {
            $message[] = rex_i18n::msg('pool_file_added');
        }

        if ($data['file']['name_new'] != $data['file']['name']) {
            $message[] = rex_i18n::rawMsg('pool_file_renamed', $data['file']['name'], $data['file']['name_new']);
        }

        $data['message'] = implode('<br />', $message);

        /**
         * TODO:.
         * @deprecated $return
         */
        $return = $data;
        $return['type'] = $data['file']['type'];
        $return['msg'] = implode('<br />', $message);

        $return['filename'] = $data['file']['name_new'];
        $return['old_filename'] = $data['file']['name'];
        $return['ok'] = 1;

        if (isset($size) && $size) {
            $return['width'] = $size[0];
            $return['height'] = $size[1];
        }

        // ----- EXTENSION POINT
        rex_extension::registerPoint(new rex_extension_point('MEDIA_ADDED', '', $return));

        return $data;
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
    public static function updateMedia($FILE, &$FILEINFOS, $userlogin = null)
    {
        // TODO:
        // params [ status, tags, categories ]

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
     * @param string $filename
     *
     * @return array
     *
     * @psalm-return array{ok: bool, msg: string}
     */
    public static function deleteMedia($filename)
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
}
