<?php

class rex_effect_mirror extends rex_effect_abstract
{
	var $options;

	function rex_effect_mirror()
	{
		$this->script = '
<script type="text/javascript">
<!--

(function($) {
	$(function() {
		var $fx_mirror_select_trans = $("#image_manager_rex_effect_mirror_set_transparent_select");
		var $fx_mirror_bg_r = $("#image_manager_rex_effect_mirror_bg_r_text").parent().parent();
		var $fx_mirror_bg_g = $("#image_manager_rex_effect_mirror_bg_g_text").parent().parent();
		var $fx_mirror_bg_b = $("#image_manager_rex_effect_mirror_bg_b_text").parent().parent();

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

//--></script>';

	}

	function execute()
	{

		$gdimage =& $this->image->getImage();
		
		$w = $this->image->getWidth();
		$h = $this->image->getHeight();

		if(substr(trim($this->params['height']), -1) === '%')
		{
			$this->params['height'] = round($h * (rtrim($this->params['height'], '%') / 100));
		}else
		{
			$this->params['height'] = (int) $this->params['height'];
		}
		if($this->params['height']<1)
		{
			$this->params['height'] = round($h/2);
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

		if($this->params["set_transparent"] != "colored")
		{
			$this->image->img["format"] = "PNG";
		}

		$trans = false;
		if($this->image->img["format"] == "PNG")
		{
				$trans = true;
		}
		
		$gdimage = $this->imagereflection ( $gdimage, $this->params["height"], $trans, array ($this->params["bg_r"], $this->params["bg_g"], $this->params["bg_b"]) );
		$this->image->refreshDimensions();
		return;

	}

	function getParams()
	{
		global $REX,$I18N;

		return array(
			array(
				'label'=>$I18N->msg('im_fx_mirror_height'),		// Length in Pixel or Prozent
				'name' => 'height',
				'type' => 'int',
			),
			array(
				'label'=>$I18N->msg('im_fx_mirror_background_color'),
				'name' => 'set_transparent',
				'type' => 'select',
				'options' => array('colored', 'transparent / png24'),
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
	
	function imagereflection(&$src_img, $reflection_height = 50, $trans = FALSE, $bgcolor) {
	
	  $src_height = imagesy($src_img);
	  $src_width = imagesx($src_img);
	  $dest_height = $src_height + $reflection_height;
	  $dest_width = $src_width;
	 
	  $reflected = imagecreatetruecolor($dest_width, $dest_height);
	  if($trans)
	  {
	  	imagealphablending($reflected, false);
	  	imagesavealpha($reflected, true);
	  }else
	  {
		  // und mit Hintergrundfarbe füllen
		  imagefill($reflected, 0, 0, imagecolorallocate($reflected, $bgcolor[0], $bgcolor[1], $bgcolor[2]));
	  }
	  	 
	  imagecopy($reflected, $src_img, 0, 0, 0, 0, $src_width, $src_height);
	  $alpha_step = 80 / $reflection_height;
	  for ($y = 1; $y <= $reflection_height; $y++) {
	  
	    for ($x = 0; $x < $dest_width; $x++) {
	      $rgba = imagecolorat($src_img, $x, $src_height - $y);
	      $alpha = ($rgba & 0x7F000000) >> 24;
	      $alpha =  max($alpha, 47 + ($y * $alpha_step));
	      $rgba = imagecolorsforindex($src_img, $rgba);
	      $rgba = imagecolorallocatealpha($reflected, $rgba['red'], $rgba['green'], $rgba['blue'], $alpha);
	      imagesetpixel($reflected, $x, $src_height + $y - 1, $rgba);
	    }
	  }

	  return $reflected;
	}
		
	
	
	
	
	
	

}