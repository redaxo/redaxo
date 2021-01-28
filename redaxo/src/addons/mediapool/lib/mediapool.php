<?php

class rex_mediapool
{
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
            $NFILE_NAME = substr($NFILENAME, 0, strlen($NFILENAME) - (strlen($NFILENAME) - $pos));
            $NFILE_EXT = substr($NFILENAME, $pos, strlen($NFILENAME) - $pos);
        } else {
            $NFILE_NAME = $NFILENAME;
            $NFILE_EXT = '';
        }

        // ---- ext checken - alle scriptendungen rausfiltern
        if (!self::isAllowedMediaType($NFILENAME)) {
            // make sure we dont add a 2nd file-extension to the file,
            // because some webspaces execute files like file.php.txt as a php script
            $NFILE_NAME .= str_replace('.', '_', $NFILE_EXT);
            $NFILE_EXT = '.txt';
        }

        $NFILENAME = $NFILE_NAME . $NFILE_EXT;

        if ($doSubindexing || $FILENAME != $NFILENAME) {
            // ----- datei schon vorhanden -> namen aendern -> _1 ..
            $cnt = 0;
            while (is_file(rex_path::media($NFILENAME)) || rex_media::get($NFILENAME)) {
                ++$cnt;
                $NFILENAME = $NFILE_NAME . '_' . $cnt . $NFILE_EXT;
            }
        }

        return $NFILENAME;
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
     * @return array
     */
    public static function syncFile($physical_filename, $params, $title, $filesize = null, $filetype = null, $userlogin = null)
    {
        // TODO
        // params [categories, tags, status]
        $category_id = $params['categories'];
        $tags = $params['tags'];
        $status = $params['status'];

        $abs_file = rex_path::media($physical_filename);

        if (!is_file($abs_file)) {
            throw new rex_exception(sprintf('File "%s" does not exist.', $abs_file));
        }

        if (empty($filesize)) {
            $filesize = filesize($abs_file);
        }

        if (empty($filetype)) {
            $filetype = rex_file::mimeType($abs_file);
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
     * check if mediatpye(extension) is allowed for upload.
     *
     * @param string $filename
     *
     * @return bool
     */
    public static function isAllowedMediaType($filename, array $args = [])
    {
        $file_ext = mb_strtolower(rex_file::extension($filename));

        if ('' === $filename || false !== strpos($file_ext, ' ') || '' === $file_ext) {
            return false;
        }

        if (0 === strpos($file_ext, 'php')) {
            return false;
        }

        $blacklist = self::getMediaTypeBlacklist();
        foreach ($blacklist as $blackExtension) {
            // blacklisted extensions are not allowed within filenames, to prevent double extension vulnerabilities:
            // -> some webspaces execute files named file.php.txt as php
            if (false !== strpos($filename, '.'. $blackExtension)) {
                return false;
            }
        }

        $whitelist = self::getMediaTypeWhitelist($args);
        return !count($whitelist) || in_array($file_ext, $whitelist);
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

        $mime_type = rex_file::mimeType($path);

        return in_array($mime_type, $whitelist[$extension]);
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
        return rex_addon::get('mediapool')->getProperty('blocked_extensions');
    }

    public static function getMediaList($params, int $offset = 0, int $limit = 20)
    {
        // TODO:
        // $params['args']['orderby'] = '';
        // $params['args']['searchterm'] = '';
        // $params['args']['filename'] = '';
        // $params['args']['tags'] = '';
        // $params['args']['categories'] = '';

        $sql = rex_sql::factory();
        $where = [];
        $queryParams = [];

        // TODO:
        // wie wird das verarbeitet
        // was ist alles möglich
        dump($params);
        $rex_file_category = '';

        if (isset($params['args']['types'])) {
            $types = [];
            foreach (explode(',', $params['args']['types']) as $index => $type) {
                $types[] = 'LOWER(RIGHT(m.filename, LOCATE(".", REVERSE(m.filename))-1)) = :type'.$index;
                $queryParams['type'.$index] = strtolower($type);
            }
            $where[] = ' AND (' . implode(' OR ', $types) . ')';
        }
        $where = count($where) ? ' WHERE '.implode(' ', $where) : '';
        $query = 'SELECT m.* FROM '.rex::getTable('media').' AS m '.$where.' ORDER BY m.updatedate DESC, m.id DESC';

        $query = rex_extension::registerPoint(new rex_extension_point('MEDIA_LIST_QUERY', $query, [
            'category_id' => $rex_file_category,
            'params' => $params, // NEW
        ]));

        $query .= ' LIMIT '.$offset.','.$limit;

        $media_manager_url = null;
        if (rex_addon::get('media_manager')->isAvailable()) {
            $media_manager_url = [rex_media_manager::class, 'getUrl'];
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
                    if ($media_manager_url && 'svg' != rex_file::extension($element['name'])) {
                        $element['url'] = $media_manager_url('rex_mediapool_maximized', $element['nameEncoded'], $element['stamp']);
                    }
                }
            }
            $elements[] = $element;
        }

        return [
            'amount' => 'todo',
            'items' => $elements
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
