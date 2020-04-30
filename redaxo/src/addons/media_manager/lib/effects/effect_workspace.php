<?php

/**
 * @package redaxo\media-manager
 */
class rex_effect_workspace extends rex_effect_abstract
{
    private $options;
    private $script;

    public function __construct()
    {
        $this->options = [
            'top',
            'topleft',
            'left',
            'bottomleft',
            'bottom',
            'bottomright',
            'right',
            'topright',
            'center',
        ];

        $this->script = '
<script type="text/javascript">
<!--

$(function() {
    var $fx_workspace_select_trans = $("#media-manager-rex-effect-workspace-set-transparent-select");
    var $fx_workspace_bg_r = $("#media-manager-rex-effect-workspace-bg-r-text").closest(".rex-form-group");
    var $fx_workspace_bg_g = $("#media-manager-rex-effect-workspace-bg-g-text").closest(".rex-form-group");
    var $fx_workspace_bg_b = $("#media-manager-rex-effect-workspace-bg-b-text").closest(".rex-form-group");

    $fx_workspace_select_trans.change(function(){
        if(jQuery(this).val() != "colored")
        {
            $fx_workspace_bg_r.hide();
            $fx_workspace_bg_g.hide();
            $fx_workspace_bg_b.hide();
        }else
        {
            $fx_workspace_bg_r.show();
            $fx_workspace_bg_g.show();
            $fx_workspace_bg_b.show();
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

        $this->params['width'] = (int) $this->params['width'];
        if ($this->params['width'] <= 0) {
            $this->params['width'] = $w;
        }

        $this->params['height'] = (int) $this->params['height'];
        if ($this->params['height'] <= 0) {
            $this->params['height'] = $h;
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

        $trans = false;
        if ('colored' != $this->params['set_transparent']) {
            if ('gif' != $this->media->getFormat() && 'png' != $this->media->getFormat() && 'webp' != $this->media->getFormat()) {
                $this->media->setFormat('png');
            }
            $trans = true;
        }

        $workspace = imagecreatetruecolor($this->params['width'], $this->params['height']);
        if ($trans) {
            $transparent = imagecolorallocatealpha($workspace, 0, 0, 0, 127);
            imagefill($workspace, 0, 0, $transparent);
            $this->keepTransparent($workspace);
        } else {
            imagefill($workspace, 0, 0, imagecolorallocate($workspace, $this->params['bg_r'], $this->params['bg_g'], $this->params['bg_b']));
        }

        $src_w = $w;
        $src_h = $h;
        $dst_x = 0;
        $dst_y = 0;
        $src_x = 0;
        $src_y = 0;

        switch ($this->params['vpos']) {
            case 'top':
                break;
            case 'bottom':
                $dst_y = (int) $this->params['height'] - $h;
                break;
            case 'middle':
            default: // center
                $dst_y = (int) ($this->params['height'] / 2) - ($h / 2);
                break;
        }

        switch ($this->params['hpos']) {
            case 'left':
                break;
            case 'right':
                $dst_x = (int) $this->params['width'] - $w;
                break;
            case 'center':
            default: // center
                $dst_x = (int) ($this->params['width'] / 2) - ($w / 2);
                break;
        }

        imagecopy($workspace, $gdimage, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
        $this->media->setImage($workspace);
        $this->media->refreshImageDimensions();

        // Transparenz erhalten
        /*
        $this->keepTransparent($des);
        imagecopyresampled($des, $gdimage, 0, 0, 0, 0, $this->params['width'], $this->params['height'], $w, $h);

        $gdimage = $des;
        $this->image->refreshDimensions();
        */
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_workspace');
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
                'label' => rex_i18n::msg('media_manager_effect_mirror_background_color'),
                'name' => 'set_transparent',
                'type' => 'select',
                'options' => ['colored', 'transparent'],
                'default' => 'colored',
                'suffix' => $this->script,
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
}
