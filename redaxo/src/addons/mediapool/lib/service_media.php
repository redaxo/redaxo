<?php

/**
 * @package redaxo\mediapool
 */
final class rex_media_service
{
    public const ORDERBY = [
        'filename',
        'updatedate',
        'title',
    ];

    /**
     * Holt ein upgeloadetes File und legt es in den Medienpool
     * Dabei wird kontrolliert ob das File schon vorhanden ist und es
     * wird eventuell angepasst, weiterhin werden die Fileinformationen übergeben.
     *
     * @param bool $doSubindexing // echte Dateinamen anpassen, falls schon vorhanden
     */
    public static function addMedia(array $data, ?string $userlogin = null, bool $doSubindexing = true, array $allowedExtensions = []): array
    {
        if (empty($data['file']) || empty($data['file']['name']) || empty($data['file']['path'])) {
            throw new rex_api_exception(rex_i18n::msg('pool_file_not_found'));
        }

        if (!rex_mediapool::isAllowedExtension($data['file']['name'], $allowedExtensions)) {
            $warning = rex_i18n::msg('pool_file_mediatype_not_allowed') . ' <code>' . rex_file::extension($data['file']['name']) . '</code>';
            $allowedExtensions = rex_mediapool::getAllowedExtensions($allowedExtensions);
            $warning .= count($allowedExtensions) > 0
                    ? '<br />' . rex_i18n::msg('pool_file_allowed_mediatypes') . ' <code>' . rtrim(implode('</code>, <code>', $allowedExtensions), ', ') . '</code>'
                    : '<br />' . rex_i18n::msg('pool_file_banned_mediatypes') . ' <code>' . rtrim(implode('</code>, <code>', rex_mediapool::getBlockedExtensions()), ', ') . '</code>';

            throw new rex_api_exception($warning);
        }

        if (!rex_mediapool::isAllowedMimeType($data['file']['path'], $data['file']['name'])) {
            $warning = rex_i18n::msg('pool_file_mediatype_not_allowed') . ' <code>' . rex_file::extension($data['file']['name']) . '</code> (<code>' . rex_file::mimeType($data['file']['path']) . '</code>)';
            throw new rex_api_exception($warning);
        }

        $categoryId = (int) $data['category_id'];
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
        /** @var string|null $errorMessage */
        $errorMessage = null;
        $errorMessage = rex_extension::registerPoint(new rex_extension_point('MEDIA_ADD', $errorMessage, [
            'file' => $data['file'],
            'title' => $title,
            'filename' => $data['file']['name_new'],
            'old_filename' => $data['file']['name'],
            'is_upload' => !empty($data['file']['tmp_name']), // wir gehen davon aus, dass aus BC Gründen das tmp_name vorhanden sein muss wenn es ein upload ist.
            'category_id' => $categoryId,
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

        $saveObject->setValue('category_id', $categoryId);
        $saveObject->addGlobalCreateFields($userlogin);
        $saveObject->addGlobalUpdateFields($userlogin);
        $saveObject->insert();

        $message = [];

        $message[] = rex_i18n::msg('pool_file_added');

        if ($data['file']['name_new'] != $data['file']['name']) {
            $message[] = rex_i18n::rawMsg('pool_file_renamed', $data['file']['name'], $data['file']['name_new']);
        }

        $data['message'] = implode('<br />', $message);

        rex_media_cache::deleteList($categoryId);

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
     */
    public static function updateMedia(array $data, ?string $userlogin = null): array
    {
        if (empty($data['filename'])) {
            throw new rex_api_exception('Expecting Filename.');
        }

        $categoryId = (int) $data['category_id'];

        $saveObject = rex_sql::factory();
        $saveObject->setTable(rex::getTablePrefix() . 'media');
        $saveObject->setWhere(['filename' => $data['filename']]);
        $saveObject->setValue('title', $data['title']);
        $saveObject->setValue('category_id', $categoryId);

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

    public static function deleteMedia(string $filename): void
    {
        $media = rex_media::get($filename);
        if (!$media) {
            throw new rex_api_exception(rex_i18n::msg('pool_file_not_found', $filename));
        }

        if ($uses = rex_mediapool::mediaIsInUse($filename)) {
            throw new rex_api_exception(rex_i18n::msg('pool_file_delete_error', $filename) . ' ' . rex_i18n::msg('pool_object_in_use_by') . $uses);
        }

        $sql = rex_sql::factory();
        $sql->setQuery('DELETE FROM ' . rex::getTable('media') . ' WHERE filename = ? LIMIT 1', [$filename]);

        rex_file::delete(rex_path::media($filename));
        rex_media_cache::delete($filename);

        rex_extension::registerPoint(new rex_extension_point('MEDIA_DELETED', '', [
            'filename' => $filename,
        ]));
    }

    /**
     * @param list<array{type: 'category_id'|'category_id_path'|'types'|'term', value: int|string|list<string>}> $searchItems
     * @param list<array{string, 'ASC'|'DESC'}> $orderbyItems
     * @throws rex_sql_exception
     * @return list<rex_media>
     */
    public static function getList(array $searchItems = [], array $orderbyItems = [], ?rex_pager $pager = null): array
    {
        $sql = rex_sql::factory();
        $where = [];
        $queryParams = [];
        $tables = [];
        $tables[] = rex::getTable('media').' AS m';

        foreach ($searchItems as $counter => $searchItem) {
            switch ($searchItem['type']) {
                case 'category_id':
                    if (is_int($searchItem['value'])) {
                        $where[] = '(m.category_id = :search_'.$counter.')';
                        $queryParams['search_'.$counter] = $searchItem['value'];
                    }
                    break;
                case 'category_id_path':
                    if (is_int($searchItem['value'])) {
                        $tables[] = rex::getTable('media_category').' AS c';
                        $where[] = '(m.category_id = c.id AND (c.path LIKE "%|'.$searchItem['value'].'|%" OR c.id='.$searchItem['value'].') )';
                        $queryParams['search_'.$counter] = $searchItem['value'];
                    }
                    break;
                case 'types':
                    if (is_array($searchItem['value'])) {
                        $types = [];
                        foreach ($searchItem['value'] as $index => $type) {
                            if (is_string($type)) {
                                $types[] = 'LOWER(RIGHT(m.filename, LOCATE(".", REVERSE(m.filename))-1)) = :search_'.$counter.'_'.$index;
                                $queryParams['search_'.$counter.'_'.$index] = strtolower($type);
                            }
                        }
                        if (count($types) > 0) {
                            $where[] = '('.implode(' OR ', $types).')';
                        }
                    }
                    break;
                case 'term':
                    if (is_string($searchItem['value']) && '' != $searchItem['value']) {
                        $where[] = '(m.filename LIKE :search_'.$counter.' || m.title LIKE :search_'.$counter.')';
                        $queryParams['search_'.$counter] = $searchItem['value'];
                    }
                    break;
            }
        }

        $where = count($where) ? ' WHERE '.implode(' AND ', $where) : '';
        $query = 'SELECT m.filename FROM '.implode(',', $tables).' '.$where;

        $orderbys = [];
        foreach ($orderbyItems as $index => $orderbyItem) {
            if (!is_array($orderbyItem)) {
                continue;
            }
            if (!in_array($orderbyItem[0], self::ORDERBY, true)) {
                continue;
            }
            $orderbys[] = ':orderby_'.$index.' '.('ASC' == $orderbyItem[1]) ? 'ASC' : 'DESC';
            $queryParams['orderby_'.$index] = 'm.' . $orderbyItem[0];
        }

        if (0 == count($orderbys)) {
            $orderbys[] = 'm.id DESC';
        }

        if ($pager) {
            $query .= ' ORDER BY '.implode(', ', $orderbys);
            $sql->setQuery(str_replace('SELECT m.filename', 'SELECT count(*)', $query), $queryParams);
            $pager->setRowCount((int) $sql->getValue('count(*)'));

            $query .= ' LIMIT '.$pager->getCursor().','.$pager->getRowsPerPage();
        }

        $items = [];

        /** @var array{filename: string} $media */
        foreach ($sql->getArray($query, $queryParams) as $media) {
            $mediaObject = rex_media::get($media['filename']);
            if (!$mediaObject) {
                throw new LogicException('Media "'.$media['filename'].'" does not exist');
            }
            $items[] = $mediaObject;
        }

        return $items;
    }
}
