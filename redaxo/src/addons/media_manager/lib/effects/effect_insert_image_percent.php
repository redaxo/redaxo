<?php

/**
 * Branded ein Bild mit einem Wasserzeichen.
 *
 * @package redaxo\media-manager
 */
class rex_effect_insert_image_percent extends rex_effect_abstract
{
    public function execute()
    {
        $this->media->asImage();

        // -------------------------------------- CONFIG
        $brandimage = rex_path::media($this->params['brandimage']);
        if (!is_file($brandimage)) {
            return;
        }

        // Deckkraft in Prozent
        $opacityPercent = 50;
        if (isset($this->params['opacity_percent'])) {
            $opacityPercent = (int) $this->params['opacity_percent'];
        }
        $opacity = intval(127 * (100 - $opacityPercent) / 100);

        // Breite in Prozent des Bildes
        $widthPercentX = 30;
        if (isset($this->params['width_percent_x'])) {
            $widthPercentX = (int) $this->params['width_percent_x'];
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
        var_dump($imageWidth, $brandWidth, $widthPercentX);
        
        $brandInsertedWidth = (int) ($imageWidth * $widthPercentX / 100);
        $brandInsertedHeight = (int) ($brandHeight * ($brandInsertedWidth / $brandWidth));

        $des = @imagecreatetruecolor($brandInsertedWidth, $brandInsertedHeight);
        if (!$des) {
            return;
        }
        $this->keepTransparent($des);
        imagealphablending($des, false);
        imagecopyresampled($des, $gdbrand, 0, 0, 0, 0, $brandInsertedWidth, $brandInsertedHeight, $brandWidth, $brandHeight);
        imagefilter($des, IMG_FILTER_COLORIZE, 0, 0, 0, $opacity);

        $dstX = match ($hpos) {
            'left' =>  $paddingX,
            'right' => $imageWidth - $brandInsertedWidth + $paddingX,
            'center' => (int) (($imageWidth - $brandInsertedWidth) / 2),
            default => $paddingX,
        };

        $dstY = match ($vpos) {
            'top' => 0 - $paddingY,
            'middle' => (int) (($imageHeight - $brandInsertedHeight) / 2) + $paddingY,
            default => $imageHeight - $brandInsertedHeight + $paddingY,
        };

        imagealphablending($gdimage, true);
        imagecopy($gdimage, $des, $dstX, $dstY, 0, 0, $brandInsertedWidth, $brandInsertedHeight);

        $this->media->setImage($gdimage);
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_insert_image_percent');
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
                'label' => rex_i18n::msg('media_manager_effect_brand_opacity_percent'),
                'name' => 'opacity_percent',
                'type' => 'int',
                'default' => '50',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_brand_width_percent_x'),
                'name' => 'width_percent_x',
                'type' => 'int',
                'default' => '30',
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
