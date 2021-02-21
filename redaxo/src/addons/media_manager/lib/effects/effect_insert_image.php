<?php

/**
 * Branded ein Bild mit einem Wasserzeichen.
 *
 * @package redaxo\media-manager
 */
class rex_effect_insert_image extends rex_effect_abstract
{
    public function execute()
    {
        $this->media->asImage();

        // -------------------------------------- CONFIG
        $brandimage = rex_path::media($this->params['brandimage']);
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

        $imageWidth = $this->media->getWidth();
        $imageHeight = $this->media->getHeight();
        $brandWidth = $brand->getWidth();
        $brandHeight = $brand->getHeight();

        switch ($hpos) {
            case 'left':
                $dstX = $paddingX;
                break;
            case 'center':
                $dstX = (int) (($imageWidth - $brandWidth) / 2) + $paddingX;
                break;
            case 'right':
            default:
                $dstX = $imageWidth - $brandWidth - $paddingX;
        }

        switch ($vpos) {
            case 'top':
                $dstY = $paddingY;
                break;
            case 'middle':
                $dstY = (int) (($imageHeight - $brandHeight) / 2) + $paddingY;
                break;
            case 'bottom':
            default:
                $dstY = $imageHeight - $brandHeight - $paddingY;
        }

        imagealphablending($gdimage, true);
        imagecopy($gdimage, $gdbrand, $dstX, $dstY, 0, 0, $brandWidth, $brandHeight);

        $this->media->setImage($gdimage);
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_insert_image');
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_brand_image'),
                'name' => 'brandimage',
                'type' => 'media',
                'default' => '',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_brand_hpos'),
                'name' => 'hpos',
                'type' => 'select',
                'options' => ['left', 'center', 'right'],
                'default' => 'left',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_brand_vpos'),
                'name' => 'vpos',
                'type' => 'select',
                'options' => ['top', 'middle', 'bottom'],
                'default' => 'top',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_brand_padding_x'),
                'name' => 'padding_x',
                'type' => 'int',
                'default' => '-10',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_brand_padding_y'),
                'name' => 'padding_y',
                'type' => 'int',
                'default' => '-10',
            ],
        ];
    }
}
