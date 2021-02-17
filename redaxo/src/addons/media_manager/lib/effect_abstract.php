<?php

/**
 * @package redaxo\media-manager
 */
abstract class rex_effect_abstract
{
    /**
     * @var rex_managed_media
     */
    public $media;

    /**
     * effekt parameter.
     *
     * @var array
     */
    public $params = [];

    public function __construct()
    {
    }

    public function setMedia(rex_managed_media $media)
    {
        $this->media = $media;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    abstract public function execute();

    /**
     * @return string
     */
    public function getName()
    {
        return static::class;
    }

    /**
     * Returns a array of array items each describing a spec of a input field with which the enduser can configure parameters for this effect.
     *
     * Example:
     *     return [
     *       [
     *         'label' => rex_i18n::msg('...'),  -> a short user-friendly field label
     *         'notice' => rex_i18n::msg('...'), -> additional description.
     *         'name' => 'contrast',             -> name of your parameter. this will be the index within $this->params
     *         'type' => 'int',                  -> scalar storage type
     *         'default' => '',                  -> default value
     *       ],
     *       // ... the next input-field spec
     *     ];
     *
     * @return array
     */
    public function getParams()
    {
        // implement me in your subclass.
        return [];
    }

    protected function keepTransparent($gdImage)
    {
        $image = $this->media;
        if ('png' == $image->getFormat() || 'webp' == $image->getFormat()) {
            imagealphablending($gdImage, false);
            imagesavealpha($gdImage, true);
        } elseif ('gif' == $image->getFormat()) {
            $gdimage = $image->getImage();
            $colorTransparent = imagecolortransparent($gdimage);
            imagepalettecopy($gdimage, $gdImage);
            if ($colorTransparent > 0) {
                imagefill($gdImage, 0, 0, $colorTransparent);
                imagecolortransparent($gdImage, $colorTransparent);
            }
            imagetruecolortopalette($gdImage, true, 256);
        }
    }
}
