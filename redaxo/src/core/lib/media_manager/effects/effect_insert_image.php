<?php

use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Translation\I18n;

/**
 * Branded ein Bild mit einem Wasserzeichen.
 */
class rex_effect_insert_image extends rex_effect_abstract
{
    public function execute()
    {
        $this->media->asImage();

        // -------------------------------------- CONFIG
        $brandimage = Path::media($this->params['brandimage']);
        if (!is_file($brandimage)) {
            return;
        }

        // Abstand vom Rand
        $paddingX = -10;
        if (isset($this->params['padding_x'])) {
            $paddingX = (int) $this->params['padding_x'];
        }

        $paddingY = -10;
        if (isset($this->params['padding_y'])) {
            $paddingY = (int) $this->params['padding_y'];
        }

        // horizontale ausrichtung: left/center/right
        $hpos = 'right';
        if (isset($this->params['hpos'])) {
            $hpos = (string) $this->params['hpos'];
        }

        // vertikale ausrichtung:   top/center/bottom
        $vpos = 'bottom';
        if (isset($this->params['vpos'])) {
            $vpos = (string) $this->params['vpos'];
        }

        // -------------------------------------- /CONFIG
        $brand = new rex_managed_media($brandimage);
        $brand->asImage();
        $gdbrand = $brand->getImage();
        $gdimage = $this->media->getImage();

        $imageWidth = (int) $this->media->getWidth();
        $imageHeight = (int) $this->media->getHeight();
        $brandWidth = (int) $brand->getWidth();
        $brandHeight = (int) $brand->getHeight();

        $dstX = match ($hpos) {
            'left' => $paddingX,
            'center' => (int) (($imageWidth - $brandWidth) / 2) + $paddingX,
            default => $imageWidth - $brandWidth - $paddingX,
        };

        $dstY = match ($vpos) {
            'top' => $paddingY,
            'middle' => (int) (($imageHeight - $brandHeight) / 2) + $paddingY,
            default => $imageHeight - $brandHeight - $paddingY,
        };

        imagealphablending($gdimage, true);
        imagecopy($gdimage, $gdbrand, $dstX, $dstY, 0, 0, $brandWidth, $brandHeight);

        $this->media->setImage($gdimage);
    }

    public function getName()
    {
        return I18n::msg('media_manager_effect_insert_image');
    }

    public function getParams()
    {
        return [
            [
                'label' => I18n::msg('media_manager_effect_brand_image'),
                'name' => 'brandimage',
                'type' => 'media',
                'default' => '',
            ],
            [
                'label' => I18n::msg('media_manager_effect_brand_hpos'),
                'name' => 'hpos',
                'type' => 'select',
                'options' => ['left', 'center', 'right'],
                'default' => 'left',
            ],
            [
                'label' => I18n::msg('media_manager_effect_brand_vpos'),
                'name' => 'vpos',
                'type' => 'select',
                'options' => ['top', 'middle', 'bottom'],
                'default' => 'top',
            ],
            [
                'label' => I18n::msg('media_manager_effect_brand_padding_x'),
                'name' => 'padding_x',
                'type' => 'int',
                'default' => '-10',
            ],
            [
                'label' => I18n::msg('media_manager_effect_brand_padding_y'),
                'name' => 'padding_y',
                'type' => 'int',
                'default' => '-10',
            ],
        ];
    }
}
