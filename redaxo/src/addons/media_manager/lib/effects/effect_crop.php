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
        $w = (int) $this->media->getWidth();
        $h = (int) $this->media->getHeight();

        if (empty($this->params['width']) || $this->params['width'] < 0 ||
            empty($this->params['height']) || $this->params['height'] < 0
        ) {
            return;
        }

        // das original-bild ist kleiner als das zu croppende format
        if ($this->params['width'] > $w && $this->params['height'] > $h) {
            return;
        }

        $offsetWidth = 0;
        $offsetHeight = 0;
        if (empty($this->params['offset_width'])) {
            $this->params['offset_width'] = 0;
        }
        if (empty($this->params['offset_height'])) {
            $this->params['offset_height'] = 0;
        }

        $cropW = (int) min($this->params['width'], $w);
        $cropH = (int) min($this->params['height'], $h);

        switch ($this->params['vpos']) {
            case 'top':
                $offsetHeight += $this->params['offset_height'];
                break;
            case 'bottom':
                $offsetHeight = (int) ($h - $cropH) + $this->params['offset_height'];
                break;
            case 'middle':
            default: // center
                $offsetHeight = (int) (($h - $cropH) / 2) + $this->params['offset_height'];
                break;
        }

        switch ($this->params['hpos']) {
            case 'left':
                $offsetWidth += $this->params['offset_width'];
                break;
            case 'right':
                $offsetWidth = (int) ($w - $cropW) + $this->params['offset_width'];
                break;
            case 'center':
            default: // center
                $offsetWidth = (int) (($w - $cropW) / 2) + $this->params['offset_width'];
                break;
        }

        $des = @imagecreatetruecolor($cropW, $cropH);
        if (!$des) {
            return;
        }

        $this->keepTransparent($des);
        imagecopyresampled($des, $gdimage, 0, 0, $offsetWidth, $offsetHeight, $cropW, $cropH, $cropW, $cropH);

        $this->media->setImage($des);
        $this->media->refreshImageDimensions();
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_crop');
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
