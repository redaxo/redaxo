<?php

/**
 * @package redaxo\media-manager
 */
class rex_effect_mirror extends rex_effect_abstract
{
    public function execute()
    {
        $this->media->asImage();
        $gdimage = $this->media->getImage();

        $h = (int) $this->media->getHeight();

        if (str_ends_with(trim($this->params['height']), '%')) {
            $this->params['height'] = (int) round($h * ((int) rtrim($this->params['height'], '%') / 100));
        } else {
            $this->params['height'] = (int) $this->params['height'];
        }
        if ($this->params['height'] < 1) {
            $this->params['height'] = (int) round($h / 2);
        }

        $this->params['bg_r'] = (int) $this->params['bg_r'];
        if (!isset($this->params['bg_r']) || $this->params['bg_r'] > 255 || $this->params['bg_r'] < 0) {
            $this->params['bg_r'] = 255;
        }

        $this->params['bg_g'] = (int) $this->params['bg_g'];
        if (!isset($this->params['bg_g']) || $this->params['bg_g'] > 255 || $this->params['bg_g'] < 0) {
            $this->params['bg_g'] = 255;
        }

        $this->params['bg_b'] = (int) $this->params['bg_b'];
        if (!isset($this->params['bg_b']) || $this->params['bg_b'] > 255 || $this->params['bg_b'] < 0) {
            $this->params['bg_b'] = 255;
        }

        if ('colored' != $this->params['set_transparent']) {
            if ('webp' == $this->media->getFormat()) {
                $this->media->setFormat('webp');
            } else {
                $this->media->setFormat('png');
            }
        }

        $trans = false;
        if ('png' == $this->media->getFormat() || 'webp' == $this->media->getFormat()) {
            $trans = true;
        }

        $gdimage = $this->imagereflection($gdimage, $this->params['height'], $this->params['opacity'] ?? 100, $trans, [$this->params['bg_r'], $this->params['bg_g'], $this->params['bg_b']]);
        $this->media->setImage($gdimage);
        $this->media->refreshImageDimensions();
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_mirror');
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_mirror_height'),    // Length in Pixel or Prozent
                'name' => 'height',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_mirror_opacity'),
                'notice' => rex_i18n::msg('media_manager_effect_mirror_opacity_notice'),
                'name' => 'opacity',
                'default' => 100,
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_mirror_background_color'),
                'name' => 'set_transparent',
                'type' => 'select',
                'options' => ['colored', 'transparent / png24'],
                'default' => 'colored',
                'suffix' => '
<script type="text/javascript">
<!--

(function($) {
    $(function() {
        var $fx_mirror_select_trans = $("#media-manager-rex-effect-mirror-set-transparent-select");
        var $fx_mirror_bg_r = $("#media-manager-rex-effect-mirror-bg-r-text").closest(".rex-form-group");
        var $fx_mirror_bg_g = $("#media-manager-rex-effect-mirror-bg-g-text").closest(".rex-form-group");
        var $fx_mirror_bg_b = $("#media-manager-rex-effect-mirror-bg-b-text").closest(".rex-form-group");

        $fx_mirror_select_trans.change(function(){
            if(jQuery(this).val() != "colored")
            {
                $fx_mirror_bg_r.hide();
                $fx_mirror_bg_g.hide();
                $fx_mirror_bg_b.hide();
            }else
            {
                $fx_mirror_bg_r.show();
                $fx_mirror_bg_g.show();
                $fx_mirror_bg_b.show();
            }
        }).change();
    });
})(jQuery);

//--></script>',
            ],

            [
                'label' => rex_i18n::msg('media_manager_effect_mirror_background_r'),
                'name' => 'bg_r',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_mirror_background_g'),
                'name' => 'bg_g',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_mirror_background_b'),
                'name' => 'bg_b',
                'type' => 'int',
            ],
        ];
    }

    /**
     * @return resource
     */
    private function imagereflection(&$image, $reflectionHeight, $reflectionOpacity, $transparent, $bgColor)
    {
        $srcHeight = imagesy($image);
        $srcWidth = imagesx($image);
        $destHeight = $srcHeight + $reflectionHeight;
        $destWidth = $srcWidth;

        $reflected = imagecreatetruecolor($destWidth, $destHeight);
        if (!$reflected) {
            throw new LogicException('unable to create image');
        }
        if ($transparent) {
            imagealphablending($reflected, false);
            imagesavealpha($reflected, true);
        } else {
            // und mit Hintergrundfarbe füllen
            imagefill($reflected, 0, 0, imagecolorallocate($reflected, $bgColor[0], $bgColor[1], $bgColor[2]));
        }

        imagecopy($reflected, $image, 0, 0, 0, 0, $srcWidth, $srcHeight);

        if ($reflectionOpacity < 100) {
            $transparency = 1 - $reflectionOpacity / 100;
            imagefilter($image, IMG_FILTER_COLORIZE, 0, 0, 0, 127 * $transparency);
        }
        $alphaStep = 80 / $reflectionHeight;
        for ($y = 1; $y <= $reflectionHeight; ++$y) {
            for ($x = 0; $x < $destWidth; ++$x) {
                $rgba = imagecolorat($image, $x, $srcHeight - $y);
                $alpha = ($rgba & 0x7F000000) >> 24;
                $alpha = max($alpha, 47 + ($y * $alphaStep));
                $rgba = imagecolorsforindex($image, $rgba);
                $rgba = imagecolorallocatealpha($reflected, $rgba['red'], $rgba['green'], $rgba['blue'], $alpha);
                imagesetpixel($reflected, $x, $srcHeight + $y - 1, $rgba);
            }
        }

        return $reflected;
    }
}
