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

    public function getParams()
    {
        // NOOP
    }

    protected function keepTransparent($des)
    {
        $image = $this->media;
        $gdimage = $image->getImage();

        imagepalettecopy($gdimage, $des);

        if ($image->getFormat() == 'png') {
            imagealphablending($des, false);
            imagesavealpha($des, true);
        } elseif ($image->getFormat() == 'gif') {
            $colorTransparent = imagecolortransparent($gdimage);
            if ($colorTransparent > 0) {
                imagefill($des, 0, 0, $colorTransparent);
                imagecolortransparent($des, $colorTransparent);
            }
            imagetruecolortopalette($des, true, 256);
        }
    }
}
