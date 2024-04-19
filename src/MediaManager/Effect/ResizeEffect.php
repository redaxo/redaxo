<?php

namespace Redaxo\Core\MediaManager\Effect;

use Redaxo\Core\Http\Response;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\Str;

use function in_array;

class ResizeEffect extends AbstractEffect
{
    private const OPTIONS = ['maximum', 'minimum', 'exact'];

    public function execute()
    {
        $this->media->asImage();

        $gdimage = $this->media->getImage();
        $w = (int) $this->media->getWidth();
        $h = (int) $this->media->getHeight();

        if (!isset($this->params['style']) || !in_array($this->params['style'], self::OPTIONS)) {
            $this->params['style'] = 'maximum';
        }

        // relatives resizen
        if (isset($this->params['width']) && str_ends_with(trim($this->params['width']), '%')) {
            $this->params['width'] = round($w * ((int) rtrim($this->params['width'], '%') / 100));
        } elseif ($this->params['width'] ?? false) {
            $this->params['width'] = (int) $this->params['width'];
        }
        if (isset($this->params['height']) && str_ends_with(trim($this->params['height']), '%')) {
            $this->params['height'] = round($h * ((int) rtrim($this->params['height'], '%') / 100));
        } elseif ($this->params['height'] ?? false) {
            $this->params['height'] = (int) $this->params['height'];
        }

        if ('maximum' == $this->params['style']) {
            $this->resizeMax($w, $h);
        } elseif ('minimum' == $this->params['style']) {
            $this->resizeMin($w, $h);
        }
        // warp => nichts tun

        // ----- not enlarge image
        if ($w <= $this->params['width'] && $h <= $this->params['height'] && 'not_enlarge' == $this->params['allow_enlarge']) {
            $this->params['width'] = $w;
            $this->params['height'] = $h;
            $this->keepTransparent($gdimage);
            return;
        }

        if (!isset($this->params['width']) || '' === $this->params['width']) {
            $this->params['width'] = $w;
        }

        if (!isset($this->params['height']) || '' === $this->params['height']) {
            $this->params['height'] = $h;
        }

        $des = @imagecreatetruecolor($this->params['width'], $this->params['height']);
        if (!$des) {
            return;
        }

        // Transparenz erhalten
        $this->keepTransparent($des);
        imagecopyresampled($des, $gdimage, 0, 0, 0, 0, $this->params['width'], $this->params['height'], $w, $h);

        $this->media->setImage($des);
        $this->media->refreshImageDimensions();
    }

    /**
     * @param int $width
     * @param int $height
     * @return void
     */
    private function resizeMax($width, $height)
    {
        if (!empty($this->params['height']) && !empty($this->params['width'])) {
            $imgRatio = $width / $height;
            $resizeRatio = $this->params['width'] / $this->params['height'];

            if ($imgRatio >= $resizeRatio) {
                // --- width
                $this->params['height'] = ceil($this->params['width'] / $width * $height);
            } else {
                // --- height
                $this->params['width'] = ceil($this->params['height'] / $height * $width);
            }
        } elseif (!empty($this->params['height'])) {
            $imgFactor = $height / $this->params['height'];
            $this->params['width'] = ceil($width / $imgFactor);
        } elseif (!empty($this->params['width'])) {
            $imgFactor = $width / $this->params['width'];
            $this->params['height'] = ceil($height / $imgFactor);
        }
    }

    /**
     * @param int $width
     * @param int $height
     * @return void
     */
    private function resizeMin($width, $height)
    {
        if (!empty($this->params['height']) && !empty($this->params['width'])) {
            $imgRatio = $width / $height;
            $resizeRatio = $this->params['width'] / $this->params['height'];

            if ($imgRatio < $resizeRatio) {
                // --- width
                $this->params['height'] = ceil($this->params['width'] / $width * $height);
            } else {
                // --- height
                $this->params['width'] = ceil($this->params['height'] / $height * $width);
            }
        } elseif (!empty($this->params['height'])) {
            $imgFactor = $height / $this->params['height'];
            $this->params['width'] = ceil($width / $imgFactor);
        } elseif (!empty($this->params['width'])) {
            $imgFactor = $width / $this->params['width'];
            $this->params['height'] = ceil($height / $imgFactor);
        }
    }

    public function getName()
    {
        return I18n::msg('media_manager_effect_resize');
    }

    public function getParams()
    {
        return [
            [
                'label' => I18n::msg('media_manager_effect_resize_width'),
                'name' => 'width',
                'type' => 'int',
            ],
            [
                'label' => I18n::msg('media_manager_effect_resize_height'),
                'name' => 'height',
                'type' => 'int',
            ],
            [
                'label' => I18n::msg('media_manager_effect_resize_style'),
                'name' => 'style',
                'type' => 'select',
                'options' => self::OPTIONS,
                'default' => 'fit',
                'suffix' => '
<script type="text/javascript" nonce="' . Response::getNonce() . '">
<!--

$(function() {
    var $fx_resize_select_style = $("#rex-' . strtolower(Str::normalize(self::class, '-')) . '-style-select");
    var $fx_resize_enlarge = $("#rex-' . strtolower(Str::normalize(self::class, '-')) . '-allow-enlarge-select").closest(".rex-form-group");

    $fx_resize_select_style.change(function(){
        if(jQuery(this).val() == "exact")
        {
            $fx_resize_enlarge.hide();
        }else
        {
            $fx_resize_enlarge.show();
        }
    }).change();
});

//--></script>',
            ],
            [
                'label' => I18n::msg('media_manager_effect_resize_imgtosmall'),
                'name' => 'allow_enlarge',
                'type' => 'select',
                'options' => ['enlarge', 'not_enlarge'],
                'default' => 'enlarge',
            ],
        ];
    }
}
