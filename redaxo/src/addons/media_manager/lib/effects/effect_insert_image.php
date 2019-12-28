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
        if (!file_exists($brandimage) || !is_file($brandimage)) {
            return;
        }

        // Abstand vom Rand
        $padding_x = -10;
        if (isset($this->params['padding_x'])) {
            $padding_x = (int) $this->params['padding_x'];
        }

        $padding_y = -10;
        if (isset($this->params['padding_y'])) {
            $padding_y = (int) $this->params['padding_y'];
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

        $image_width = $this->media->getWidth();
        $image_height = $this->media->getHeight();
        $brand_width = $brand->getWidth();
        $brand_height = $brand->getHeight();

        switch ($hpos) {
            case 'left':
                $dstX = 0;
                break;
            case 'center':
                $dstX = (int) (($image_width - $brand_width) / 2);
                break;
            case 'right':
            default:
                $dstX = $image_width - $brand_width;
        }

        switch ($vpos) {
            case 'top':
                $dstY = 0;
                break;
            case 'middle':
                $dstY = (int) (($image_height - $brand_height) / 2);
                break;
            case 'bottom':
            default:
                $dstY = $image_height - $brand_height;
        }

        imagealphablending($gdimage, true);
        imagecopy($gdimage, $gdbrand, $dstX + $padding_x, $dstY + $padding_y, 0, 0, $brand_width, $brand_height);

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
