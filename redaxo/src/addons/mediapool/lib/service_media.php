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
     * @param array  $FILE
     * @param array  $params // <- int    $rex_file_category
     * @param array  $FILEINFOS
     * @param string $userlogin
     * @param bool   $doSubindexing // echte Dateinamen anpassen, falls schon vorhanden
     *
     * @return array
     */
    public static function addMedia($FILE, $params, $FILEINFOS, $userlogin = null, $doSubindexing = true)
    {
        $rex_file_category = (array) $params['categories'];

        // TODO:
        // params [ status, tags, categories ]

        $gc = rex_sql::factory();
        $gc->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media_category WHERE id=' . $rex_file_category);
        if (1 != $gc->getRows()) {
            $rex_file_category = 0;
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
            'category_id' => $rex_file_category,
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

        $RETURN = [];
        $RETURN['title'] = $FILEINFOS['title'];
        $RETURN['type'] = $FILETYPE;
        $RETURN['msg'] = implode('<br />', $message);
        // Aus BC gruenden hier mit int 1/0
        $RETURN['ok'] = $success ? 1 : 0;
        $RETURN['filename'] = $NFILENAME;
        $RETURN['old_filename'] = $FILENAME;
        $RETURN['category_id'] = $rex_file_category;

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
