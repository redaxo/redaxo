<?php

/**
 * @package redaxo\media-manager
 */
class rex_effect_workspace extends rex_effect_abstract
{

    private
        $options,
        $script;

    public function __construct()
    {
        $this->options = array(
            'top',
            'topleft',
            'left',
            'bottomleft',
            'bottom',
            'bottomright',
            'right',
            'topright',
            'center'
        );

        $this->script = '
<script type="text/javascript">
<!--

(function($) {
    $(function() {
        var $fx_workspace_select_trans = $("#media_manager_rex_effect_workspace_set_transparent_select");
        var $fx_workspace_bg_r = $("#media_manager_rex_effect_workspace_bg_r_text").parent().parent();
        var $fx_workspace_bg_g = $("#media_manager_rex_effect_workspace_bg_g_text").parent().parent();
        var $fx_workspace_bg_b = $("#media_manager_rex_effect_workspace_bg_b_text").parent().parent();

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
})(jQuery);

//--></script>';

    }

    public function execute()
    {

        $gdimage = $this->media->getImage();
        $w = $this->media->getWidth();
        $h = $this->media->getHeight();

        $this->params['width'] = (int) $this->params['width'];
        if ($this->params['width'] < 0) {
            $this->params['width'] = $w;
        }

        $this->params['height'] = (int) $this->params['height'];
        if ($this->params['width'] < 0) {
            $this->params['height'] = $h;
        }

        $this->params['bg_r'] = (int) $this->params['bg_r'];
        if (!isset($this->params['bg_r']) || $this->params['bg_r'] > 255 || $this->params['bg_r'] < 0 ) {
            $this->params['bg_r'] = 255;
        }

        $this->params['bg_g'] = (int) $this->params['bg_g'];
        if (!isset($this->params['bg_g']) || $this->params['bg_g'] > 255 || $this->params['bg_g'] < 0 ) {
            $this->params['bg_g'] = 255;
        }

        $this->params['bg_b'] = (int) $this->params['bg_b'];
        if (!isset($this->params['bg_b']) || $this->params['bg_b'] > 255 || $this->params['bg_b'] < 0 ) {
            $this->params['bg_b'] = 255;
        }

        $trans = false;
        if ($this->params['set_transparent'] != 'colored') {
            if ($this->media->getFormat() != 'GIF' && $this->media->getFormat() != 'PNG') {
                $this->media->setFormat('PNG');
            }
            $trans = true;
        }

        $workspace = imagecreatetruecolor($this->params['width'], $this->params['height']);
        if ($trans) {
            imagealphablending($workspace, false);
            $transparent = imagecolorallocatealpha($workspace, 0, 0, 0, 127);
            imagefill($workspace, 0, 0, $transparent);
            imagesavealpha($workspace, true);
            imagealphablending($workspace, true);
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

        ImageCopy($workspace, $gdimage, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
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

    private function keepTransparent($des)
    {
        $image = $this->media;
        if ($image->getFormat() == 'PNG') {
            imagealphablending($des, false);
            imagesavealpha($des, true);
        } elseif ($image->getFormat() == 'GIF') {
            $gdimage = $image->getImage();
            $colorTransparent = imagecolortransparent($gdimage);
            imagepalettecopy($gdimage, $des);
            if ($colorTransparent > 0) {
                imagefill($des, 0, 0, $colorTransparent);
                imagecolortransparent($des, $colorTransparent);
            }
            imagetruecolortopalette($des, true, 256);
        }
    }

    public function getParams()
    {
        return array(
            array(
                'label' => rex_i18n::msg('media_manager_effect_resize_width'),
                'name' => 'width',
                'type' => 'int',
            ),
            array(
                'label' => rex_i18n::msg('media_manager_effect_resize_height'),
                'name' => 'height',
                'type' => 'int'
            ),
            array(
                'label' => rex_i18n::msg('media_manager_effect_brand_hpos'),
                'name' => 'hpos',
                'type'  => 'select',
                'options'  => array('left', 'center', 'right'),
                'default' => 'left'
            ),
            array(
                'label' => rex_i18n::msg('media_manager_effect_brand_vpos'),
                'name' => 'vpos',
                'type'  => 'select',
                'options'  => array('top', 'middle', 'bottom'),
                'default' => 'top'
            ),
            array(
                'label' => rex_i18n::msg('media_manager_effect_mirror_background_color'),
                'name' => 'set_transparent',
                'type' => 'select',
                'options' => array('colored', 'transparent'),
                'default' => 'colored',
                'suffix' => $this->script
            ),
            array(
                'label' => rex_i18n::msg('media_manager_effect_mirror_background_r'),
                'name' => 'bg_r',
                'type' => 'int',
            ),
            array(
                'label' => rex_i18n::msg('media_manager_effect_mirror_background_g'),
                'name' => 'bg_g',
                'type' => 'int',
            ),
            array(
                'label' => rex_i18n::msg('media_manager_effect_mirror_background_b'),
                'name' => 'bg_b',
                'type' => 'int',
            ),
        );
    }
}
