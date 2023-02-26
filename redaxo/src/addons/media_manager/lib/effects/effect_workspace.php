<?php

/**
 * @package redaxo\media-manager
 */
class rex_effect_workspace extends rex_effect_abstract
{
    public function execute()
    {
        $this->media->asImage();

        $gdimage = $this->media->getImage();
        $w = (int) $this->media->getWidth();
        $h = (int) $this->media->getHeight();

        $this->params['width'] = (int) $this->params['width'];
        if ($this->params['width'] <= 0) {
            $this->params['width'] = $w;
        }

        $this->params['height'] = (int) $this->params['height'];
        if ($this->params['height'] <= 0) {
            $this->params['height'] = $h;
        }

        $this->params['bg_r'] = (int) $this->params['bg_r'];
        if ($this->params['bg_r'] > 255 || $this->params['bg_r'] < 0) {
            $this->params['bg_r'] = 255;
        }

        $this->params['bg_g'] = (int) $this->params['bg_g'];
        if ($this->params['bg_g'] > 255 || $this->params['bg_g'] < 0) {
            $this->params['bg_g'] = 255;
        }

        $this->params['bg_b'] = (int) $this->params['bg_b'];
        if ($this->params['bg_b'] > 255 || $this->params['bg_b'] < 0) {
            $this->params['bg_b'] = 255;
        }

        $trans = false;
        if ('colored' != $this->params['set_transparent']) {
            if (!$this->media->formatSupportsTransparency()) {
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

        // Abstand vom Rand
        $paddingX = 0;
        if (isset($this->params['padding_x'])) {
            $paddingX = (int) $this->params['padding_x'];
        }
        $paddingY = 0;
        if (isset($this->params['padding_y'])) {
            $paddingY = (int) $this->params['padding_y'];
        }

        $paramsHeight = (int) $this->params['height'];
        $paramsWidth = (int) $this->params['width'];

        // Bild als Hintergrund ------------------------------
        if ('image' == $this->params['set_transparent']) {
            $bgimage = rex_path::media((string) $this->params['bgimage']);
            if (!is_file($bgimage)) {
                return;
            }
            $bg = new rex_managed_media($bgimage);
            $bg->asImage();
            $workspace = $bg->getImage();
            $this->keepTransparent($workspace);
            $paramsHeight = (int) $bg->getHeight();
            $paramsWidth = (int) $bg->getWidth();
        }

        $dstY = 0;
        switch ($this->params['vpos']) {
            case 'top':
                break;
            case 'bottom':
                $dstY = $paramsHeight - $h;
                break;
            case 'middle':
            default: // center
                $dstY = (int) (($paramsHeight - $h) / 2);
                break;
        }
        $dstX = 0;
        switch ($this->params['hpos']) {
            case 'left':
                break;
            case 'right':
                $dstX = $paramsWidth - $w;
                break;
            case 'center':
            default: // center
                $dstX = (int) (($paramsWidth - $w) / 2);
                break;
        }

        $dstX += $paddingX;
        $dstY += $paddingY;

        imagecopy($workspace, $gdimage, $dstX, $dstY, 0, 0, $w, $h);
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
                'label' => rex_i18n::msg('media_manager_effect_workspace_bg'),
                'name' => 'set_transparent',
                'type' => 'select',
                'options' => ['colored', 'transparent', 'image'],
                'default' => 'colored',
                'suffix' => '
<script type="text/javascript" nonce="' . rex_response::getNonce() . '">
<!--

$(function() {
    var $fx_workspace_select_trans = $("#media-manager-rex-effect-workspace-set-transparent-select");
    var $fx_workspace_width = $("#media-manager-rex-effect-workspace-width-text");
	var $fx_workspace_height = $("#media-manager-rex-effect-workspace-height-text");
    var $fx_workspace_bgimage = $("#REX_MEDIA_1").closest(".rex-form-group");
    var $fx_workspace_padding_x = $("#media-manager-rex-effect-workspace-padding-x-text").closest(".rex-form-group");
    var $fx_workspace_padding_y = $("#media-manager-rex-effect-workspace-padding-y-text").closest(".rex-form-group");
	var $fx_workspace_bg_r = $("#media-manager-rex-effect-workspace-bg-r-text").closest(".rex-form-group");
    var $fx_workspace_bg_g = $("#media-manager-rex-effect-workspace-bg-g-text").closest(".rex-form-group");
    var $fx_workspace_bg_b = $("#media-manager-rex-effect-workspace-bg-b-text").closest(".rex-form-group");

    $fx_workspace_select_trans.change(function(){
		$fx_workspace_bg_r.hide();
		$fx_workspace_bg_g.hide();
		$fx_workspace_bg_b.hide();
		$fx_workspace_bgimage.hide();
		$fx_workspace_width.show().parent().find(".form-control-static").hide();
		$fx_workspace_height.show().parent().find(".form-control-static").hide();

		if(jQuery(this).val() == "colored"){
            $fx_workspace_bg_r.show();
            $fx_workspace_bg_g.show();
            $fx_workspace_bg_b.show();
        }

		if(jQuery(this).val() == "image"){
			$fx_workspace_bgimage.show();
			$fx_workspace_width.hide().parent().find(".form-control-static").show();
			$fx_workspace_height.hide().parent().find(".form-control-static").show();
		}

    }).change();
});

//--></script>',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_resize_width'),
                'name' => 'width',
                'type' => 'int',
                'suffix' => '<p class="form-control-static">'.rex_i18n::msg('media_manager_effect_workspace_bgimage_size').'</p>',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_resize_height'),
                'name' => 'height',
                'type' => 'int',
                'suffix' => '<p class="form-control-static">'.rex_i18n::msg('media_manager_effect_workspace_bgimage_size').'</p>',
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
                'label' => rex_i18n::msg('media_manager_effect_brand_padding_x'),
                'name' => 'padding_x',
                'type' => 'int',
                'default' => '0',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_brand_padding_y'),
                'name' => 'padding_y',
                'type' => 'int',
                'default' => '0',
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
            [
                'label' => rex_i18n::msg('media_manager_effect_workspace_bgimage'),
                'name' => 'bgimage',
                'type' => 'media',
                'default' => '',
            ],
        ];
    }
}
