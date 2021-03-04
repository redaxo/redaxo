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
     * @param array  $whitelist_types
     */
    public static function addMedia(array $data, $userlogin = null, $doSubindexing = true, $whitelist_types = []): array
    {
        if (!is_array($data)) {
            throw new rex_api_exception('Expecting $data to be an array!');
        }

        if (empty($data['file']) || empty($data['file']['name']) || empty($data['file']['path'])) {
            throw new rex_api_exception(rex_i18n::msg('pool_file_not_found'));
        }

        if (!rex_mediapool::isAllowedMediaType($data['file']['name'], $whitelist_types)) {
            $warning = rex_i18n::msg('pool_file_mediatype_not_allowed') . ' <code>' . rex_file::extension($data['file']['name']) . '</code>';
            $whitelist = rex_mediapool::getMediaTypeWhitelist($whitelist_types);
            $warning .= count($whitelist) > 0
                    ? '<br />' . rex_i18n::msg('pool_file_allowed_mediatypes') . ' <code>' . rtrim(implode('</code>, <code>', $whitelist), ', ') . '</code>'
                    : '<br />' . rex_i18n::msg('pool_file_banned_mediatypes') . ' <code>' . rtrim(implode('</code>, <code>', rex_mediapool::getMediaTypeBlacklist()), ', ') . '</code>';

            throw new rex_api_exception($warning);
        }

        if (!rex_mediapool::isAllowedMimeType($data['file']['path'], $data['file']['name'])) {
            $warning = rex_i18n::msg('pool_file_mediatype_not_allowed') . ' <code>' . rex_file::extension($data['file']['name']) . '</code> (<code>' . rex_file::mimeType($data['file']['path']) . '</code>)';
            throw new rex_api_exception($warning);
        }

        $category_id = (int) $data['category_id'];
        $title = (string) $data['title'];

        $data['file']['name_new'] = rex_mediapool::filename($data['file']['name'], $doSubindexing);

        // ----- alter/neuer filename
        $srcFile = $data['file']['path'];
        $dstFile = rex_path::media($data['file']['name_new']);

        $data['file']['type'] = rex_file::mimeType($srcFile);

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
            'is_upload' => true,
            'category_id' => $category_id,
            'type' => $data['file']['type'],
        ]));
        if ($errorMessage) {
            // ein Addon hat die Fehlermeldung gesetzt, dem Upload also faktisch widersprochen
            throw new rex_api_exception($errorMessage);
        }

        if (!rex_file::move($srcFile, $dstFile)) {
            throw new rex_api_exception(rex_i18n::msg('pool_file_movefailed'));
        }

        @chmod($dstFile, rex::getFilePerm());

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
        $saveObject->setValue('filesize', filesize($dstFile));

        if ($size) {
            $data['width'] = $size[0];
            $data['height'] = $size[1];
            $saveObject->setValue('width', $data['width']);
            $saveObject->setValue('height', $data['height']);
        }

        $saveObject->setValue('category_id', $category_id);
        $saveObject->addGlobalCreateFields($userlogin);
        $saveObject->addGlobalUpdateFields($userlogin);
        $saveObject->insert();

        $message = [];

        $message[] = rex_i18n::msg('pool_file_added');

        if ($data['file']['name_new'] != $data['file']['name']) {
            $message[] = rex_i18n::rawMsg('pool_file_renamed', $data['file']['name'], $data['file']['name_new']);
        }

        $data['message'] = implode('<br />', $message);

        rex_media_cache::deleteList($category_id);

        /**
         * @deprecated $return
         * in future only $data -> MEDIA_ADDED and return $data;
         */
        $return = $data;
        $return['type'] = $data['file']['type'];
        $return['msg'] = implode('<br />', $message);

        $return['filename'] = $data['file']['name_new'];
        $return['old_filename'] = $data['file']['name'];
        $return['ok'] = 1;

        rex_extension::registerPoint(new rex_extension_point('MEDIA_ADDED', '', $return));

        return $return;
    }

    /**
     * Holt ein upgeloadetes File und legt es in den Medienpool
     * Dabei wird kontrolliert ob das File schon vorhanden ist und es
     * wird eventuell angepasst, weiterhin werden die Fileinformationen übergeben.
     *
     * @param array  $data
     * @param string $userlogin
     *
     * @return array
     */
    public static function updateMedia(array $data, $userlogin = null): array
    {

        if (!is_array($data)) {
            throw new rex_api_exception('Expecting $data to be an array!');
        }

        if (empty($data['media_id'])) {
            throw new rex_api_exception('Expecting Media-ID.');
        }

        $category_id = (int) $data['category_id'];

        $saveObject = rex_sql::factory();
        $saveObject->setTable(rex::getTablePrefix() . 'media');
        $saveObject->setWhere(['id' => $data['media_id']]);
        $saveObject->setValue('title', $data['title']);
        $saveObject->setValue('category_id', $category_id);

        if (!empty($data['file']) && !empty($data['file']['name']) && !empty($data['file']['path'])) {
            $data['file']['type'] = rex_file::mimeType($data['file']['path']);

            $srcFile = $data['file']['path'];
            $dstFile = rex_path::media($data['filename']);

            $extensionNew = mb_strtolower(pathinfo($data['file']['name'], PATHINFO_EXTENSION));
            $extensionOld = mb_strtolower(pathinfo($data['filename'], PATHINFO_EXTENSION));

            static $jpgExtensions = ['jpg', 'jpeg'];

            if (
                $extensionNew == $extensionOld ||
                in_array($extensionNew, $jpgExtensions) && in_array($extensionOld, $jpgExtensions)
            ) {
                if (
                    !rex_file::move($srcFile, $dstFile)
                ) {
                    throw new rex_api_exception(rex_i18n::msg('pool_file_movefailed'));
                }

                @chmod($dstFile, rex::getFilePerm());

                $data['file']['size'] = filesize($dstFile);

                $saveObject->setValue('filetype', $data['file']['type']);
                $saveObject->setValue('filesize', $data['file']['size']);
                $saveObject->setValue('originalname', $data['file']['name']);

                if ($size = @getimagesize($dstFile)) {
                    $saveObject->setValue('width', $size[0]);
                    $saveObject->setValue('height', $size[1]);
                }
                @chmod($dstFile, rex::getFilePerm());
            } else {
                throw new rex_api_exception(rex_i18n::msg('pool_file_upload_errortype'));
            }
        }

        $saveObject->addGlobalUpdateFields($userlogin);
        $saveObject->update();

        rex_media_cache::delete($data['filename']);

        /**
         * @deprecated $return
         * in future only $data -> MEDIA_UPDATED and return $data;
         */
        $return = $data;

        $return['ok'] = 1;
        $return['msg'] = rex_i18n::msg('pool_file_infos_updated');
        $return['type'] = $data['file']['type'];

        $return['filename'] = $data['file']['name'];
        $return['filetype'] = $data['file']['type'];

        rex_extension::registerPoint(new rex_extension_point('MEDIA_UPDATED', '', $return));

        return $return;
    }

    /**
     * @param string $filename
     *
     * @return bool
     */
    public static function deleteMedia(string $filename):bool
    {
        if ($uses = rex_mediapool::mediaIsInUse($filename)) {
            throw new rex_api_exception(rex_i18n::msg('pool_file_delete_error', $filename) . ' ' . rex_i18n::msg('pool_object_in_use_by', $uses));
        }

        $sql = rex_sql::factory();
        $sql->setQuery('DELETE FROM ' . rex::getTable('media') . ' WHERE filename = ? LIMIT 1', [$filename]);

        rex_file::delete(rex_path::media($filename));
        rex_media_cache::delete($filename);

        rex_extension::registerPoint(new rex_extension_point('MEDIA_DELETED', '', [
            'filename' => $filename,
        ]));

        return true;
    }
}
