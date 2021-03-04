<?php

class rex_mediapool
{
    /**
     * Erstellt einen Filename der eindeutig ist für den Medienpool.
     *
     * @param string $MediaName      Dateiname
     * @param bool   $doSubindexing
     *
     * @return string
     */
    public static function getUniqueMediaName($MediaName, $doSubindexing = true)
    {
        // ----- neuer filename und extension holen
        $NewMediaName = rex_string::normalize($MediaName, '_', '.-@');

        if ('.' === $NewMediaName[0]) {
            $NewMediaName[0] = '_';
        }

        if ($pos = strrpos($NewMediaName, '.')) {
            $NewMediaBaseName = substr($NewMediaName, 0, strlen($NewMediaName) - (strlen($NewMediaName) - $pos));
            $NewMediaExtension = substr($NewMediaName, $pos, strlen($NewMediaName) - $pos);
        } else {
            $NewMediaBaseName = $NewMediaName;
            $NewMediaExtension = '';
        }

        // ---- ext checken - alle scriptendungen rausfiltern
        if (!self::isAllowedMediaType($NewMediaName)) {
            // make sure we dont add a 2nd file-extension to the file,
            // because some webspaces execute files like file.php.txt as a php script
            $NewMediaBaseName .= str_replace('.', '_', $NewMediaExtension);
            $NewMediaExtension = '.txt';
        }

        $NewMediaName = $NewMediaBaseName . $NewMediaExtension;

        if ($doSubindexing || $MediaName != $NewMediaName) {
            // ----- datei schon vorhanden -> namen aendern -> _1 ..
            $cnt = 0;
            while (is_file(rex_path::media($NewMediaName)) || rex_media::get($NewMediaName)) {
                ++$cnt;
                $NewMediaName = $NewMediaBaseName . '_' . $cnt . $NewMediaExtension;
            }
        }

        return $NewMediaName;
    }

    /**
     * @param string $filename
     *
     * @return bool|string
     */
    public static function mediaIsInUse(string $filename)
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
                $name = ($ooa) ? $ooa->getName() : '';
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
    public static function isAllowedMediaType(string $filename, array $args = []):bool
    {
        $fileExt = mb_strtolower(rex_file::extension($filename));

        if ('' === $filename || str_contains($fileExt, ' ') || '' === $fileExt) {
            return false;
        }

        if (str_starts_with($fileExt, 'php')) {
            return false;
        }

        $blockedExtensions = self::getBlockedExtensions();
        foreach ($blockedExtensions as $blockedExtension) {
            // $blockedExtensions extensions are not allowed within filenames, to prevent double extension vulnerabilities:
            // -> some webspaces execute files named file.php.txt as php
            if (str_contains($filename, '.'. $blockedExtension)) {
                return false;
            }
        }

        $allowedExtensions = self::getAllowedExtensions($args);
        return !count($allowedExtensions) || in_array($fileExt, $allowedExtensions);
    }

    /**
     * Checks file against optional AllowedMimetypes from property `allowed_mime_types`.
     *
     * @param string      $path     Path to the physical file
     * @param null|string $filename Optional filename, will be used for extracting the file extension.
     *                              If not given, the extension is extracted from `$path`.
     *
     * @return bool
     */
    public static function isAllowedMimeType(string $path, $filename = null):bool
    {
        $allowedMimetypes = rex_addon::get('mediapool')->getProperty('allowed_mime_types');

        if (!$allowedMimetypes) {
            return true;
        }

        $extension = mb_strtolower(rex_file::extension($filename ?: $path));

        if (!isset($allowedMimetypes[$extension])) {
            return false;
        }

        $mime_type = rex_file::mimeType($path);

        return in_array($mime_type, $allowedMimetypes[$extension]);
    }

    /**
     * get allowedExtensions of mediatypes(extensions) given via media widget "types" param.
     *
     * @param array $args widget params
     *
     * @return array allowedExtensions
     */
    public static function getAllowedExtensions($args = []):array
    {
        $blockedExtensions = self::getBlockedExtensions();

        $allowedExtensions = [];
        if (isset($args['types'])) {
            foreach (explode(',', $args['types']) as $ext) {
                $ext = ltrim($ext, '.');
                $ext = mb_strtolower($ext);
                if (!in_array($ext, $blockedExtensions)) { // allowedExtensions cannot override any blockedExtensions entry from master
                    $allowedExtensions[] = $ext;
                }
            }
        }
        return $allowedExtensions;
    }

    /**
     * return global mediatype blockedExtensions from master.inc.
     *
     * @return array blocked mediatype extensions
     */
    public static function getBlockedExtensions()
    {
        return rex_addon::get('mediapool')->getProperty('blocked_extensions');
    }
}
