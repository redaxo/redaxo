<?php

class rex_mediapool
{
    public static $orderby = [
        'filename',
        'updatedate',
        'status',
        'title',
        'description',
    ];

    /**
     * Erstellt einen Filename der eindeutig ist für den Medienpool.
     *
     * @param string $FILENAME      Dateiname
     * @param bool   $doSubindexing
     *
     * @return string
     */
    public static function filename($FILENAME, $doSubindexing = true)
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
        if (!self::isAllowedMediaType($NFILENAME)) {
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
     * Synchronisiert die Datei $physical_filename des Mediafolders in den
     * Medienpool.
     *
     * @param string      $physicalFilename
     * @param int         $category_id
     * @param string      $title
     * @param null|int    $filesize
     * @param null|string $filetype
     * @param null|string $userlogin
     *
     * @return array
     */
    public static function syncFile($physicalFilename, $params, $title, $filesize = null, $filetype = null, $userlogin = null)
    {
        // TODO
        // params [categories, tags, status]
        $categoryId = $params['categories'];
        $tags = $params['tags'];
        $status = $params['status'];

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
     * @return bool|string
     */
    public static function mediaIsInUse($filename)
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
     * check if mediatpye(extension) is allowed for upload.
     *
     * @param string $filename
     *
     * @return bool
     */
    public static function isAllowedMediaType($filename, array $args = [])
    {
        $fileExt = mb_strtolower(rex_file::extension($filename));

        if ('' === $filename || str_contains($fileExt, ' ') || '' === $fileExt) {
            return false;
        }

        if (str_starts_with($fileExt, 'php')) {
            return false;
        }

        $blacklist = self::getMediaTypeBlacklist();
        foreach ($blacklist as $blackExtension) {
            // blacklisted extensions are not allowed within filenames, to prevent double extension vulnerabilities:
            // -> some webspaces execute files named file.php.txt as php
            if (str_contains($filename, '.'. $blackExtension)) {
                return false;
            }
        }

        $whitelist = self::getMediaTypeWhitelist($args);
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
    public static function isAllowedMimeType($path, $filename = null)
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
     * @param array $types widget params
     *
     * @return array whitelisted extensions
     */
    public static function getMediaTypeWhitelist($types = [])
    {
        $blacklist = self::getMediaTypeBlacklist();

        $whitelist = [];
        if (is_array($types)) {
            foreach ($types as $ext) {
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
    public static function getMediaTypeBlacklist()
    {
        return rex_addon::get('mediapool')->getProperty('blocked_extensions') ?? [];
    }

    public static function getMediaList(array $params = [], array $search = [], int $offset = 0, int $limit = 50)
    {
        $sql = rex_sql::factory();
        $where = [];
        $queryParams = [];

        $types = [];
        if (isset($search['types']) && is_array($search['types'])) {
            foreach ($search['types'] as $index => $type) {
                if (is_string($type)) {
                    $types[] = 'LOWER(RIGHT(m.filename, LOCATE(".", REVERSE(m.filename))-1)) = :searchtype'.$index;
                    $queryParams['searchtype'.$index] = strtolower($type);
                }
            }
            if (count($types) > 0) {
                $where[] = '(' . implode(' OR ', $types) . ')';
            }
        }

        $categories = [];
        if (isset($search['categories']) && is_array($search['categories'])) {
            foreach ($search['categories'] as $index => $category) {
                if (is_string($category) || is_int($category)) {
                    $categories[] = 'FIND_IN_SET(:searchcategory'.$index.',categories)';
                    $queryParams['searchcategory'.$index] = $category;
                }
            }
            if (count($categories) > 0) {
                $where[] = '(' . implode(' OR ', $categories) . ')';
            }
        }

        $tags = [];
        if (isset($search['tags']) && is_array($search['tags'])) {
            foreach ($search['tags'] as $index => $tag) {
                if (is_string($tag)) {
                    $tags[] = 'FIND_IN_SET(:searchtag'.$index.',tags)';
                    $queryParams['searchtag'.$index] = $tag;
                }
            }
            if (count($tags) > 0) {
                $where[] = '(' . implode(' OR ', $tags) . ')';
            }
        }

        $status = null;
        if (isset($search['status']) && (is_int($search['status']) || is_string($search['status']))) {
            $status = $queryParams['status'];
            $queryParams['status'] = (0 == $status) ? 0 : 1;
            $where[] = '(status LIKE :searchstatus)';
        }

        $term = '';
        if (isset($search['term']) && is_string($search['term']) && '' != $search['term']) {
            $term = $search['term'];
            $queryParams['searchterm'] = $term;
            $where[] = '(filename LIKE :searchterm)';
        }

        $where = count($where) ? ' WHERE '.implode(' AND ', $where) : '';
        $query = 'SELECT m.* FROM '.rex::getTable('media').' AS m '.$where;

        $orderbys = [];

        if (isset($search['orderby']) && is_array($search['orderby'])) {
            foreach ($search['orderby'] as $index => $orderby) {
                if (is_array($orderby)) {
                    if (array_key_exists($orderby[0], static::$orderby)) {
                        $orderbys[] = ':orderby'.$index.' '.('ASC' == $orderby[1]) ? 'ASC' : 'DESC';
                        $queryParams['orderby'.$index] = 'm.' . $orderby[0];
                    }
                }
            }
        }

        if (0 == count($orderbys)) {
            $orderbys[] = 'm.id DESC';
        }

        $query .= ' ORDER BY '.implode(', ', $orderbys);

        $query = rex_extension::registerPoint(new rex_extension_point('MEDIA_LIST_QUERY', $query, [
            'params' => $queryParams,
        ]));

        $sql->setQuery(str_replace('SELECT m.*', 'SELECT count(id)', $query), $queryParams);
        $count = $sql->getValue('count(id)');

        $query .= ' LIMIT '.$offset.','.$limit;

        $mediaManagerUrl = null;
        if (rex_addon::get('media_manager')->isAvailable()) {
            $mediaManagerUrl = [rex_media_manager::class, 'getUrl'];
        }

        $elements = [];
        foreach ($sql->getArray($query, $queryParams) as $media) {
            $element = [
                'id' => $media['id'],
                'name' => $media['filename'],
                'nameEncoded' => urlencode($media['filename']),
                'originalName' => $media['originalname'],
                'title' => $media['title'],
                'type' => $media['filetype'],
                'size' => $media['filesize'],
                'stamp' => rex_formatter::strftime($media['updatedate'], 'datetime'),
                'updateUser' => $media['updateuser'],
                'document' => false,
                'url' => '',
                'exists' => file_exists(rex_path::media($media['filename'])),
            ];
            if ($element['exists']) {
                $mediaExtension = substr(strrchr($element['name'], '.'), 1);
                $element['extension'] = $mediaExtension;

                if (rex_media::isDocType($mediaExtension)) {
                    $element['document'] = true;
                }

                if (rex_media::isImageType(rex_file::extension($element['name']))) {
                    $element['document'] = false;
                    $element['url'] = rex_url::media($element['name']).'?buster='.$media['updatedate'];
                    if ($mediaManagerUrl && 'svg' != rex_file::extension($element['name'])) {
                        $element['url'] = $mediaManagerUrl('rex_mediapool_maximized', $element['nameEncoded'], $element['stamp']);
                    }
                }
            }
            $elements[] = $element;
        }

        return [
            'count' => $count,
            'items' => $elements,
            'offset' => $offset,
            'limit' => $limit,
            'search' => [
                'categories' => $categories,
                'types' => $types,
                'tags' => $tags,
                'term' => $term,
                'status' => $status,
            ],
        ];
    }

    public static function getAvailableTags()
    {
        $tagsWithComma = rex_sql::factory()->getArray('select tags from '.rex::getTable('media').' where tags <> ""');
        $tags = [];
        foreach ($tagsWithComma as $twc) {
            foreach (explode(',', $twc) as $tag) {
                $tags[$tag] = trim($tag);
            }
        }
        return $tags;
    }
}
