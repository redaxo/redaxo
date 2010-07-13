<?php

// Todo:


// resize_workspace
// - position top,topleft,left,bottomleft,bottom,bottomright,right,topright 




class rex_effect_workspace extends rex_effect_abstract
{

	function rex_effect_workspace()
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
		var $fx_workspace_select_trans = $("#image_manager_rex_effect_workspace_set_transparent_select");
		var $fx_workspace_bg_r = $("#image_manager_rex_effect_workspace_bg_r_text").parent().parent();
		var $fx_workspace_bg_g = $("#image_manager_rex_effect_workspace_bg_g_text").parent().parent();
		var $fx_workspace_bg_b = $("#image_manager_rex_effect_workspace_bg_b_text").parent().parent();

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

	function execute()
	{
	
		$gdimage =& $this->image->getImage();
		$w = $this->image->getWidth();
		$h = $this->image->getHeight();
	
		$this->params["width"] = (int) $this->params["width"];
		if($this->params["width"] < 0)
		{
			$this->params["width"] = $w;
		}

		$this->params["height"] = (int) $this->params["height"];
		if($this->params["width"] < 0)
		{
			$this->params["height"] = $h;
		}
	
		$this->params["bg_r"] = (int) $this->params["bg_r"];
		if(!isset($this->params["bg_r"]) || $this->params["bg_r"]>255 || $this->params["bg_r"] <0 )
		{
			$this->params["bg_r"] = 255;
		}

		$this->params["bg_g"] = (int) $this->params["bg_g"];
		if(!isset($this->params["bg_g"]) || $this->params["bg_g"]>255 || $this->params["bg_g"] <0 )
		{
			$this->params["bg_g"] = 255;
		}

		$this->params["bg_b"] = (int) $this->params["bg_b"];
		if(!isset($this->params["bg_b"]) || $this->params["bg_b"]>255 || $this->params["bg_b"] <0 )
		{
			$this->params["bg_b"] = 255;
		}
	
		$trans = false;
		if($this->params["set_transparent"] != "colored")
		{
			if($this->image->img["format"] != "GIF" && $this->image->img["format"] != "PNG")
			{
				$this->image->img["format"] = "PNG";
			}
			$trans = true;
		}
	
		$workspace = imagecreatetruecolor($this->params["width"], $this->params["height"]);
		if($trans)
		{
			imagealphablending($workspace, false);
			$transparent = imagecolorallocatealpha($workspace, 0, 0, 0, 127);
			imagefill($workspace, 0, 0, $transparent);
			imagesavealpha($workspace,true);
			imagealphablending($workspace, true);
		}else
		{
			// und mit Hintergrundfarbe füllen
			imagefill($workspace, 0, 0, imagecolorallocate($workspace, $this->params["bg_r"], $this->params["bg_g"], $this->params["bg_b"]));
		}
	
		$src_w = $w;
		$src_h = $h;
		$dst_x = 0;
		$dst_y = 0;
		$src_x = 0;
		$src_y = 0;
		
		switch($this->params["vpos"])
		{
			case("top"):
				break;
			case("bottom"):
				$dst_y = (int) $this->params["height"] - $h;
				break;
			case("middle"):
			default: // center
				$dst_y = (int) ($this->params["height"]/2) - ($h/2);
				break;
		}

		switch($this->params["hpos"])
		{
			case("left"):
				break;
			case("right"):
				$dst_x = (int) $this->params["width"] - $w;
				break;
			case("center"):
			default: // center
				$dst_x = (int) ($this->params["width"]/2) - ($w/2);
				break;
		}
		
		ImageCopy ($workspace, $gdimage, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
		$gdimage = $workspace;
		$this->image->refreshDimensions();

		return;

		// Transparenz erhalten
		/*
		$this->keepTransparent($des);
		imagecopyresampled($des, $gdimage, 0, 0, 0, 0, $this->params['width'], $this->params['height'], $w, $h);

		$gdimage = $des;
		$this->image->refreshDimensions();
		*/
	}
	
	function keepTransparent($des)
	{
		$image = $this->image;
		if ($image->getFormat() == 'PNG')
		{
			imagealphablending($des, false);
			imagesavealpha($des, true);
		}else if ($image->getFormat() == 'GIF')
		{
			$gdimage =& $image->getImage();
			$colorTransparent = imagecolortransparent($gdimage);
			imagepalettecopy($gdimage, $des);
			if($colorTransparent>0)
			{
				imagefill($des, 0, 0, $colorTransparent);
				imagecolortransparent($des, $colorTransparent);
			}
			imagetruecolortopalette($des, true, 256);
		}
	}

	function getParams()
	{
		global $REX,$I18N;
		
		return array(
			array(
				'label'=>$I18N->msg('imanager_effect_resize_width'),
				'name' => 'width',
				'type' => 'int',
			),
			array(
				'label'=>$I18N->msg('imanager_effect_resize_height'),
				'name' => 'height',
				'type' => 'int'
			),
			array(
				'label' => $I18N->msg('imanager_effect_brand_hpos'),
				'name' => 'hpos',
				'type'	=> 'select',
				'options'	=> array('left','center','right'),
				'default' => 'left'
			),
			array(
				'label' => $I18N->msg('imanager_effect_brand_vpos'),
				'name' => 'vpos',
				'type'	=> 'select',
				'options'	=> array('top','middle','bottom'),
				'default' => 'top'
			),
			array(
				'label'=>$I18N->msg('im_fx_mirror_background_color'),
				'name' => 'set_transparent',
				'type' => 'select',
				'options' => array('colored', 'transparent'),
				'default' => 'colored',
				'suffix' => $this->script
			),
			array(
				'label'=>$I18N->msg('im_fx_mirror_background_r'),
				'name' => 'bg_r',
				'type' => 'int',
			),
			array(
				'label'=>$I18N->msg('im_fx_mirror_background_g'),
				'name' => 'bg_g',
				'type' => 'int',
			),
			array(
				'label'=>$I18N->msg('im_fx_mirror_background_b'),
				'name' => 'bg_b',
				'type' => 'int',
			),
		);
	}
}