<?php

// Todo:
// - Vergrößern erlauben oder nicht. aber eher als Modul einsetzen, also
// fit

class rex_effect_resize extends rex_effect_abstract
{
  private
    $options,
    $script;

  public function __construct()
  {

    $this->options = array('maximum', 'minimum', 'exact');

    $this->script = '
<script type="text/javascript">
<!--

(function($) {
  $(function() {
    var $fx_resize_select_style = $("#media_manager_rex_effect_resize_style_select");
    var $fx_resize_enlarge = $("#media_manager_rex_effect_resize_allow_enlarge_select").parent().parent();

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
})(jQuery);

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
    if (substr(trim($this->params['width']), -1) === '%') {
      $this->params['width'] = round($w * (rtrim($this->params['width'], '%') / 100));
    }
    if (substr(trim($this->params['height']), -1) === '%') {
      $this->params['height'] = round($h * (rtrim($this->params['height'], '%') / 100));
    }

    if ($this->params['style'] == 'maximum') {
      $this->resizeMax($w, $h);
    } elseif ($this->params['style'] == 'minimum') {
      $this->resizeMin($w, $h);
    } else {
      // warp => nichts tun
    }

    // ----- not enlarge image
    if ($w <= $this->params['width'] && $h <= $this->params['height'] && $this->params['allow_enlarge'] == 'not_enlarge') {
      $this->params['width'] = $w;
      $this->params['height'] = $h;
      return;
    }

    if (!isset($this->params['width'])) {
      $this->params['width'] = $w;
    }

    if (!isset($this->params['height'])) {
      $this->params['height'] = $h;
    }

    if (function_exists('ImageCreateTrueColor')) {
      $des = @ImageCreateTrueColor($this->params['width'], $this->params['height']);
    } else {
      $des = @ImageCreate($this->params['width'], $this->params['height']);
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

  private function resizeMax($w, $h)
  {
    if (!empty($this->params['height']) && !empty($this->params['width'])) {
      $img_ratio  = $w / $h;
      $resize_ratio = $this->params['width'] / $this->params['height'];

      if ($img_ratio >= $resize_ratio) {
        // --- width
        $this->params['height'] = ceil ($this->params['width'] / $w * $h);
      } else {
        // --- height
        $this->params['width']  = ceil ($this->params['height'] / $h * $w);
      }
    } elseif (!empty($this->params['height'])) {
      $img_factor  = $h / $this->params['height'];
      $this->params['width'] = ceil ($w / $img_factor);
    } elseif (!empty($this->params['width'])) {
      $img_factor  = $w / $this->params['width'];
      $this->params['height'] = ceil ($h / $img_factor);
    }
  }

  private function resizeMin($w, $h)
  {
    if (!empty($this->params['height']) && !empty($this->params['width'])) {
      $img_ratio  = $w / $h;
      $resize_ratio = $this->params['width'] / $this->params['height'];

      if ($img_ratio < $resize_ratio) {
        // --- width
        $this->params['height'] = ceil ($this->params['width'] / $w * $h);
      } else {
        // --- height
        $this->params['width']  = ceil ($this->params['height'] / $h * $w);
      }
    } elseif (!empty($this->params['height'])) {
      $img_factor  = $h / $this->params['height'];
      $this->params['width'] = ceil ($w / $img_factor);
    } elseif (!empty($this->params['width'])) {
      $img_factor  = $w / $this->params['width'];
      $this->params['height'] = ceil ($h / $img_factor);
    }
  }


  private function keepTransparent($des)
  {
    if ($this->media->getFormat() == 'PNG') {
      imagealphablending($des, false);
      imagesavealpha($des, true);
    } elseif ($this->media->getFormat() == 'GIF') {
      $gdimage = $this->media->getImage();
      $colorTransparent = imagecolortransparent($gdimage);
      imagepalettecopy($gdimage, $des);
      if ($colorTransparent > 0) {
        imagefill($des, 0, 0, $colorTransparent);
        imagecolortransparent($des, $colorTransparent);
      }
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
        'label' => rex_i18n::msg('media_manager_effect_resize_style'),
        'name' => 'style',
        'type'  => 'select',
        'options' => $this->options,
        'default' => 'fit',
        'suffix' => $this->script
      ),
      array(
        'label' => rex_i18n::msg('media_manager_effect_resize_imgtosmall'),
        'name' => 'allow_enlarge',
        'type' => 'select',
        'options' => array('enlarge', 'not_enlarge'),
        'default' => 'enlarge',
      ),
    );
  }
}
