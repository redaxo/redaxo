<?php

/**
 * @package redaxo\media-manager
 */
class rex_effect_filter_colorize extends rex_effect_abstract
{
    public function execute()
    {
        $this->params['filter_r'] = (int) $this->params['filter_r'];
        if ($this->params['filter_r'] < 0) {
            return;
        }
        $this->params['filter_g'] = (int) $this->params['filter_g'];
        if ($this->params['filter_g'] < 0) {
            return;
        }
        $this->params['filter_b'] = (int) $this->params['filter_b'];
        if ($this->params['filter_b'] < 0) {
            return;
        }

        $this->media->asImage();
        $img = $this->media->getImage();

        if (!($t = imagecolorstotal($img))) {
            $t = 256;
            imagetruecolortopalette($img, true, $t);
        }
        $imagex = imagesx($img);
        $imagey = imagesy($img);

        $gdimage = $this->media->getImage();
        $w = $this->media->getWidth();
        $h = $this->media->getHeight();

        $srcX = (int) ceil($w);
        $srcY = (int) ceil($h);
        $dstX = $srcX;
        $dstY = $srcY;
        $dstIm = imagecreatetruecolor($dstX, $dstY);

        imagecopyresampled($dstIm, $gdimage, 0, 0, 0, 0, $dstX, $dstY, $srcX, $srcY);
        for ($y = 0; $y < $srcY; ++$y) {
            for ($x = 0; $x < $srcX; ++$x) {
                $rgb = imagecolorat($dstIm, $x, $y);
                $TabColors = imagecolorsforindex($dstIm, $rgb);
                $colorR = (int) floor($TabColors['red'] * $this->params['filter_r'] / 255);
                $colorG = (int) floor($TabColors['green'] * $this->params['filter_g'] / 255);
                $colorB = (int) floor($TabColors['blue'] * $this->params['filter_b'] / 255);
                $newcol = imagecolorallocate($dstIm, $colorR, $colorG, $colorB);
                imagesetpixel($dstIm, $x, $y, $newcol);
            }
        }
        $this->media->setImage($dstIm);
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_colorize');
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_colorize_r'),
                'name' => 'filter_r',
                'type' => 'int',
                'default' => '',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_colorize_g'),
                'name' => 'filter_g',
                'type' => 'int',
                'default' => '',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_colorize_b'),
                'name' => 'filter_b',
                'type' => 'int',
                'default' => '',
            ],
        ];
    }
}
