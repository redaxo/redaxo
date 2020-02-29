<?php

/**
 * Object Oriented Framework: Bildet ein Medium des Medienpools ab.
 *
 * @package redaxo\mediapool
 */
class rex_media
{
    use rex_instance_pool_trait;
    use rex_instance_list_pool_trait;

    // id
    protected $id = '';
    // categoryid
    protected $category_id = '';

    // filename
    protected $name = '';
    // originalname
    protected $originalname = '';
    // filetype
    protected $type = '';
    // filesize
    protected $size = '';

    // filewidth
    protected $width = '';
    // fileheight
    protected $height = '';

    // filetitle
    protected $title = '';

    // updatedate
    protected $updatedate = '';
    // createdate
    protected $createdate = '';

    // updateuser
    protected $updateuser = '';
    // createuser
    protected $createuser = '';

    /**
     * @param string $name
     *
     * @return null|static
     */
    public static function get($name)
    {
        if (!$name) {
            return null;
        }

        return static::getInstance($name, static function ($name) {
            $media_path = rex_path::addonCache('mediapool', $name . '.media');

            $cache = rex_file::getCache($media_path);
            if (!$cache) {
                rex_media_cache::generate($name);
                $cache = rex_file::getCache($media_path);
            }

            if ($cache) {
                $aliasMap = [
                    'filename' => 'name',
                    'filetype' => 'type',
                    'filesize' => 'size',
                ];

                $media = new static();
                foreach ($cache as $key => $value) {
                    if (isset($aliasMap[$key])) {
                        $var_name = $aliasMap[$key];
                    } else {
                        $var_name = $key;
                    }

                    $media->$var_name = $value;
                }

                return $media;
            }

            return null;
        });
    }

    /**
     * @return static[]
     */
    public static function getRootMedia()
    {
        return static::getInstanceList('root_media', ['static', 'get'], static function () {
            $list_path = rex_path::addonCache('mediapool', '0.mlist');

            $list = rex_file::getCache($list_path, null);
            if (null === $list) {
                rex_media_cache::generateList(0);
                $list = rex_file::getCache($list_path);
            }

            return $list;
        });
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return rex_media_category|null
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
        $url = rex_extension::registerPoint(new rex_extension_point('MEDIA_URL_REWRITE', '', ['media' => $this]));
        return $url ?: rex_url::media($this->getFileName());
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
     * @return string
     */
    public function toImage(array $params = [])
    {
        if (!$this->isImage()) {
            return '';
        }

        $filename = rex_url::media($this->getFileName());
        $title = $this->getTitle();

        if (!isset($params['alt'])) {
            if ('' != $title) {
                $params['alt'] = rex_escape($title);
            }
        }

        if (!isset($params['title'])) {
            if ('' != $title) {
                $params['title'] = rex_escape($title);
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
     *
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

    // new functions by vscope

    /**
     * @return string
     */
    public function getExtension()
    {
        return rex_file::extension($this->name);
    }

    /**
     * @return bool
     */
    public function fileExists()
    {
        return file_exists(rex_path::media($this->getFileName()));
    }

    // allowed filetypes
    public static function getDocTypes()
    {
        return rex_addon::get('mediapool')->getProperty('allowed_doctypes');
    }

    /**
     * @return bool
     */
    public static function isDocType($type)
    {
        return in_array($type, self :: getDocTypes());
    }

    // allowed image upload types
    public static function getImageTypes()
    {
        return rex_addon::get('mediapool')->getProperty('image_extensions');
    }

    /**
     * @return bool
     */
    public static function isImageType($extension)
    {
        return in_array($extension, self::getImageTypes());
    }

    /**
     * @return bool
     */
    public function hasValue($value)
    {
        return isset($this->$value) || isset($this->{'med_' . $value});
    }

    public function getValue($value)
    {
        // damit alte rex_article felder wie copyright, description
        // noch funktionieren
        if (isset($this->$value)) {
            return $this->$value;
        }
        if (isset($this->{'med_' . $value})) {
            return $this->getValue('med_' . $value);
        }
    }

    /**
     * Returns whether the element is permitted.
     *
     * @return bool
     */
    public function isPermitted()
    {
        return (bool) rex_extension::registerPoint(new rex_extension_point('MEDIA_IS_PERMITTED', true, ['element' => $this]));
    }
}
