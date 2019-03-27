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

    public $params = []; // effekt parameter

    public function setMedia(rex_managed_media $media)
    {
        $this->media = $media;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    abstract public function execute();

    public function getName()
    {
        return get_class($this);
    }

    public function getParams()
    {
        // NOOP
    }

    protected function keepTransparent($des)
    {
        $image = $this->media;
        if ('png' == $image->getFormat() || 'webp' == $image->getFormat()) {
            imagealphablending($des, false);
            imagesavealpha($des, true);
        } elseif ('gif' == $image->getFormat()) {
            $gdimage = $image->getImage();
            $colorTransparent = imagecolortransparent($gdimage);
            imagepalettecopy($gdimage, $des);
            if ($colorTransparent > 0) {
                imagefill($des, 0, 0, $colorTransparent);
                imagecolortransparent($des, $colorTransparent);
            }
            imagetruecolortopalette($des, true, 256);
        }
    }
}
