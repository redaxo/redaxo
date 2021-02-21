<?php

// Todo:
// - Vergrößern erlauben oder nicht. aber eher als Modul einsetzen, also
// fit

/**
 * @package redaxo\media-manager
 */
class rex_effect_resize extends rex_effect_abstract
{
    private $options;
    private $script;

    public function __construct()
    {
        $this->options = ['maximum', 'minimum', 'exact'];

        $this->script = '
<script type="text/javascript">
<!--

$(function() {
    var $fx_resize_select_style = $("#media-manager-rex-effect-resize-style-select");
    var $fx_resize_enlarge = $("#media-manager-rex-effect-resize-allow-enlarge-select").closest(".rex-form-group");

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

//--></script>';
    }

    public function execute()
    {
        $this->media->asImage();

        $gdimage = $this->media->getImage();
        $w = $this->media->getWidth();
        $h = $this->media->getHeight();

        if (!isset($this->params['style']) || !in_array($this->params['style'], $this->options)) {
            $this->params['style'] = 'maximum';
        }

        // relatives resizen
        if ('%' === substr(trim($this->params['width']), -1)) {
            $this->params['width'] = round($w * ((int) rtrim($this->params['width'], '%') / 100));
        }
        if ('%' === substr(trim($this->params['height']), -1)) {
            $this->params['height'] = round($h * ((int) rtrim($this->params['height'], '%') / 100));
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

        if (!isset($this->params['width'])) {
            $this->params['width'] = $w;
        }

        if (!isset($this->params['height'])) {
            $this->params['height'] = $h;
        }

        if (function_exists('ImageCreateTrueColor')) {
            $des = @imagecreatetruecolor($this->params['width'], $this->params['height']);
        } else {
            $des = @imagecreate($this->params['width'], $this->params['height']);
        }

        if (!$des) {
            return;
        }

        // Transparenz erhalten
        $this->keepTransparent($des);
        imagecopyresampled($des, $gdimage, 0, 0, 0, 0, $this->params['width'], $this->params['height'], $w, $h);

        $this->media->setImage($des);
        $this->media->refreshImageDimensions();
    }

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
        return rex_i18n::msg('media_manager_effect_resize');
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_resize_width'),
                'name' => 'width',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_resize_height'),
                'name' => 'height',
                'type' => 'int',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_resize_style'),
                'name' => 'style',
                'type' => 'select',
                'options' => $this->options,
                'default' => 'fit',
                'suffix' => $this->script,
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_resize_imgtosmall'),
                'name' => 'allow_enlarge',
                'type' => 'select',
                'options' => ['enlarge', 'not_enlarge'],
                'default' => 'enlarge',
            ],
        ];
    }
}
