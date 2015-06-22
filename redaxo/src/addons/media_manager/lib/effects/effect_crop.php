<?php

/**
 * Schneidet einen Ausschnitt aus einem Bild heraus. Es wird dabei nicht skaliert.
 *
 * @author staabm
 *
 * @package redaxo\media-manager
 */

class rex_effect_crop extends rex_effect_abstract
{
    public function execute()
    {
        $this->media->asImage();

        $gdimage = $this->media->getImage();
        $w = $this->media->getWidth();
        $h = $this->media->getHeight();

        if (empty($this->params['width']) || $this->params['width'] < 0 ||
            empty($this->params['height']) || $this->params['height'] < 0
        ) {
            return;
        }

        // das original-bild ist kleiner als das zu croppende format
        if ($this->params['width'] > $w || $this->params['height'] > $h) {
            return;
        }

        $offset_width = 0;
        $offset_height = 0;
        if (empty($this->params['offset_width'])) {
            $this->params['offset_width'] = 0;
        }
        if (empty($this->params['offset_height'])) {
            $this->params['offset_height'] = 0;
        }

        switch ($this->params['vpos']) {
            case 'top':
                $offset_height += $this->params['offset_height'];
                break;
            case 'bottom':
                $offset_height = (int) (($h - $this->params['height'])) + $this->params['offset_height'];
                break;
            case 'middle':
            default: // center
                $offset_height = (int) (($h - $this->params['height']) / 2) + $this->params['offset_height'];
                break;
        }

        switch ($this->params['hpos']) {
            case 'left':
                $offset_width += $this->params['offset_width'];
                break;
            case 'right':
                $offset_width = (int) ($w - $this->params['width']) + $this->params['offset_width'];
                break;
            case 'center':
            default: // center
                $offset_width = (int) (($w - $this->params['width']) / 2) + $this->params['offset_width'];
                break;
        }

        // create cropped image
        if (function_exists('ImageCreateTrueColor')) {
            $des = @ImageCreateTrueColor($this->params['width'], $this->params['height']);
        } else {
            $des = @ImageCreate($this->params['width'], $this->params['height']);
        }

        if (!$des) {
            return;
        }

        // Transparenz erhalten
        $this->keepTransparent($des);
        imagecopyresampled($des, $gdimage, 0, 0, $offset_width, $offset_height, $this->params['width'], $this->params['height'], $this->params['width'], $this->params['height']);

        $this->media->setImage($des);
        $this->media->refreshImageDimensions();
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_crop_width'),
                'name' => 'width',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_crop_height'),
                'name' => 'height',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_crop_offset_width'),
                'name' => 'offset_width',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_crop_offset_height'),
                'name' => 'offset_height',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_brand_hpos'),
                'name' => 'hpos',
                'type' => 'select',
                'options' => ['left', 'center', 'right'],
                'default' => 'center',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_brand_vpos'),
                'name' => 'vpos',
                'type' => 'select',
                'options' => ['top', 'middle', 'bottom'],
                'default' => 'middle',
            ],
        ];
    }
}
