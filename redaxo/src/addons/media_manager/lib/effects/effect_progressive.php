<?php

/**
 * Schaltet ein Bild in den Progressive Modus.
 *
 * @package redaxo\media-manager
 */
class rex_effect_progressive extends rex_effect_abstract
{
    public function execute()
    {
        $this->media->asImage();
        $gdimage = $this->media->getImage();
        imageinterlace($gdimage, 1);
        $this->media->setImage($gdimage);
    }
}
