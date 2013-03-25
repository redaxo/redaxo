<?php

/**
 * Object Oriented Framework: Bildet ein Medium des Medienpools ab
 * @package redaxo\mediapool
 */
class rex_media
{
    use rex_instance_pool_trait;

    // id
    private $id = '';
    // categoryid
    private $category_id = '';

    // filename
    private $name = '';
    // originalname
    private $originalname = '';
    // filetype
    private $type = '';
    // filesize
    private $size = '';

    // filewidth
    private $width = '';
    // fileheight
    private $height = '';

    // filetitle
    private $title = '';

    // updatedate
    private $updatedate = '';
    // createdate
    private $createdate = '';

    // updateuser
    private $updateuser = '';
    // createuser
    private $createuser = '';

    /**
     * @param string $name
     * @return null|self
     */
    public static function get($name)
    {
        if (!$name) {
            return null;
        }
        return self::getInstanceLazy(function ($name) {
            $media_path = rex_path::addonCache('mediapool', $name . '.media');
            if (!file_exists($media_path)) {
                rex_media_cache::generate($name);
            }

            if (file_exists($media_path)) {
                $cache = rex_file::getCache($media_path);
                $aliasMap = [
                    'filename' => 'name',
                    'filetype' => 'type',
                    'filesize' => 'size'
                ];

                $media = new self();
                foreach ($cache as $key => $value) {
                    if (in_array($key, array_keys($aliasMap))) {
                        $var_name = $aliasMap[$key];
                    } else {
                        $var_name = $key;
                    }

                    $media->$var_name = $value;
                }
                $media->category = null;

                return $media;
            }

            return null;
        }, $name);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return rex_media_category
     */
    public function getCategory()
    {
        return rex_media_category::get($this->getCategoryId());
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->category_id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getOriginalFileName()
    {
        return $this->originalname;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return rex_url::media($this->getFileName());
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getFormattedSize()
    {
        return rex_formatter::bytes($this->getSize());
    }

    /**
     * @return string
     */
    public function getUpdateUser()
    {
        return $this->updateuser;
    }

    /**
     * @return int
     */
    public function getUpdateDate()
    {
        return $this->updatedate;
    }

    /**
     * @return string
     */
    public function getCreateUser()
    {
        return $this->createuser;
    }

    /**
     * @return int
     */
    public function getCreateDate()
    {
        return $this->createdate;
    }

    /**
     * @param array $params
     * @return string
     */
    public function toImage(array $params = [])
    {
        if (!is_array($params)) {
            $params = [];
        }

        if (!$this->isImage()) {
            return '';
        }

        $filename = rex_url::media($this->getFileName());
        $title = $this->getTitle();

        if (!isset($params['alt'])) {
            if ($title != '') {
                $params['alt'] = htmlspecialchars($title);
            }
        }

        if (!isset($params['title'])) {
            if ($title != '') {
                $params['title'] = htmlspecialchars($title);
            }
        }

        rex_extension::registerPoint(new rex_extension_point('MEDIA_TOIMAGE', '', ['filename' => &$filename, 'params' => &$params]));

        $additional = '';
        foreach ($params as $name => $value) {
            $additional .= ' ' . $name . '="' . $value . '"';
        }

        return sprintf('<img src="%s"%s />', $filename, $additional);
    }

    /**
     * @param string $attributes
     * @return string
     */
    public function toLink($attributes = '')
    {
        return sprintf('<a href="%s" title="%s"%s>%s</a>', $this->getUrl(), $this->getValue('med_description'), $attributes, $this->getFileName());
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        return self::isImageType($this->getExtension());
    }

    /**
     * @return bool|string[]
     */
    public function isInUse()
    {
        $sql = rex_sql::factory();
        $filename = addslashes($this->getFileName());

        $values = [];
        for ($i = 1; $i < 21; $i++) {
            $values[] = 'value' . $i . ' REGEXP "(^|[^[:alnum:]+_-])' . $filename . '"';
        }

        $files = [];
        $filelists = [];
        for ($i = 1; $i < 11; $i++) {
            $files[] = 'media' . $i . '="' . $filename . '"';
            $filelists[] = 'FIND_IN_SET("' . $filename . '",medialist' . $i . ')';
        }

        $where = '';
        $where .= implode(' OR ', $files) . ' OR ';
        $where .= implode(' OR ', $filelists) . ' OR ';
        $where .= implode(' OR ', $values);
        $query = 'SELECT DISTINCT article_id, clang FROM ' . rex::getTablePrefix() . 'article_slice WHERE ' . $where;

        $warning = [];
        $res = $sql->getArray($query);
        if ($sql->getRows() > 0) {
            $warning[0] = rex_i18n::msg('pool_file_in_use_articles') . '<br /><ul>';
            foreach ($res as $art_arr) {
                $aid = $art_arr['article_id'];
                $clang = $art_arr['clang'];
                $ooa = rex_article::get($aid, $clang);
                $name = $ooa->getName();
                $warning[0] .= '<li><a href="javascript:openPage(\'' . rex_url::backendPage('content', ['article_id' => $aid, 'mode' => 'edit', 'clang' => $clang]) . '\')">' . $name . '</a></li>';
            }
            $warning[0] .= '</ul>';
        }

        // ----- EXTENSION POINT
        $warning = rex_extension::registerPoint(new rex_extension_point('MEDIA_IS_IN_USE', $warning, [
            'filename' => $this->getFileName(),
            'media' => $this,
        ]));

        if (!empty($warning)) {
            return $warning;
        }

        return false;
    }

    // new functions by vscope
    /**
     * @return string
     */
    public function getExtension()
    {
        return rex_file::extension($this->name);
    }

    /**
     * @return string
     */
    public static function _getTableName()
    {
        return rex::getTablePrefix() . 'media';
    }

    /**
     * @return bool Returns <code>true</code> on success or <code>false</code> on error
     */
    public function save()
    {
        try {
            $sql = rex_sql::factory();
            $sql->setTable($this->_getTableName());
            $sql->setValue('category_id', $this->getCategoryId());
            $sql->setValue('filetype', $this->getType());
            $sql->setValue('filename', $this->getFileName());
            $sql->setValue('originalname', $this->getOriginalFileName());
            $sql->setValue('filesize', $this->getSize());
            $sql->setValue('width', $this->getWidth());
            $sql->setValue('height', $this->getHeight());
            $sql->setValue('title', $this->getTitle());

            if ($this->getId() !== null) {
                $sql->addGlobalUpdateFields();
                $sql->setWhere(['id' => $this->getId()]);
                $sql->update();
                rex_media_cache::delete($this->getFileName());
                return true;
            } else {
                $sql->addGlobalCreateFields();
                $sql->insert();
                rex_media_cache::deleteList($this->getCategoryId());
                return true;
            }
        } catch (rex_sql_exception $e) {
            return false;
        }
    }

    /**
     * @param string $filename
     * @return bool Returns <code>true</code> on success or <code>false</code> on error
     */
    public function delete($filename = null)
    {
        if ($filename != null) {
            $OOMed = self::get($filename);
            if ($OOMed) {
                $OOMed->delete();
                return true;
            }
        } else {
            $qry = 'DELETE FROM ' . $this->_getTableName() . ' WHERE id = ' . $this->getId() . ' LIMIT 1';
            $sql = rex_sql::factory();
            $sql->setQuery($qry);

            if ($this->fileExists()) {
                rex_file::delete(rex_path::media($this->getFileName()));
            }

            rex_media_cache::delete($this->getFileName());

            return true;
        }
        return false;
    }

    public function fileExists()
    {
        return file_exists(rex_path::media($this->getFileName()));
    }

    // allowed filetypes
    public static function getDocTypes()
    {
        return rex_addon::get('mediapool')->getProperty('allowed_doctypes');
    }

    public static function isDocType($type)
    {
        return in_array($type, self :: getDocTypes());
    }

    // allowed image upload types
    public static function getImageTypes()
    {
        return rex_addon::get('mediapool')->getProperty('image_types');
    }

    public static function isImageType($type)
    {
        return in_array($type, self :: getImageTypes());
    }

    public static function compareImageTypes($type1, $type2)
    {
        static $jpg = [
            'image/jpg',
            'image/jpeg',
            'image/pjpeg'
        ];

        return in_array($type1, $jpg) && in_array($type2, $jpg);
    }

    public function hasValue($value)
    {
        return isset($this->$value);
    }

    public function getValue($value)
    {
        // damit alte rex_article felder wie copyright, description
        // noch funktionieren
        if ($this->hasValue($value)) {
            return $this->$value;
        } elseif ($this->hasValue('med_' . $value)) {
            return $this->getValue('med_' . $value);
        }
    }
}
