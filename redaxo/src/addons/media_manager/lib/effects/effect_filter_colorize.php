<?php

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

        $src_x = ceil($w);
        $src_y = ceil($h);
        $dst_x = $src_x;
        $dst_y = $src_y;
        $dst_im = imagecreatetruecolor($dst_x, $dst_y);

        imagecopyresampled($dst_im, $gdimage, 0, 0, 0, 0, $dst_x, $dst_y, $src_x, $src_y);
        for ($y = 0; $y < $src_y; ++$y) {
            for ($x = 0; $x < $src_x; ++$x) {
                $rgb = imagecolorat($dst_im, $x, $y);
                $TabColors = imagecolorsforindex($dst_im, $rgb);
                $color_r = floor($TabColors['red'] * $this->params['filter_r'] / 255);
                $color_g = floor($TabColors['green'] * $this->params['filter_g'] / 255);
                $color_b = floor($TabColors['blue'] * $this->params['filter_b'] / 255);
                $newcol = imagecolorallocate($dst_im, $color_r, $color_g, $color_b);
                imagesetpixel($dst_im, $x, $y, $newcol);
            }
        }
        $this->media->setImage($dst_im);
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
