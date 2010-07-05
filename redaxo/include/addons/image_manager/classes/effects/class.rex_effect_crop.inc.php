<?php

/**
 * Schneidet einen Ausschnitt aus einem Bild heraus. Es wird dabei nicht skaliert. 
 * 
 * @author staabm
 */
class rex_effect_crop extends rex_effect_abstract
{
  var $options;
  
  function rex_effect_crop()
  {
    $this->options = array(
      'top_left','top_center','top_right',
      'middle_left','middle_center','middle_right',
      'bottom_left','bottom_center','bottom_right',
    );
  }
  
	function execute()
	{
    $gdimage =& $this->image->getImage();
    $w = $this->image->getWidth();
    $h = $this->image->getHeight();

		if(empty($this->params['width']) || $this->params['width'] < 0 || 
		   empty($this->params['height']) || $this->params['height'] < 0)
		{
		  return;
		}
		
		// das original-bild ist kleiner als das zu croppende format 
		if($this->params['width'] > $w || 
		   $this->params['height'] > $h)
		{
		  return;
		}
		
		if(empty($this->params['position']) || !in_array($this->params['position'],$this->options))
		{
			$this->params['position'] = 'middle_center';
		}
		$position = explode('_', $this->params['position']);
		
		
    $offset_width = 0;
    $offset_height = 0;
    if(empty($this->params['offset_width'])) $this->params['offset_width'] = 0;
    if(empty($this->params['offset_height'])) $this->params['offset_height'] = 0;
    
    // vertical position
    if($position[0] == 'top')
    {
      $offset_height += $this->params['offset_height'];
    }
    else if($position[0] == 'middle')
    {
      $offset_height = (int) (($h - $this->params['height']) / 2) + $this->params['offset_height'];
    }
    else if($position[0] == 'bottom')
    {
      $offset_height = (int) (($h - $this->params['height'])) + $this->params['offset_height'];
    }
    else
    {
      trigger_error('Unexpected vertical position "'. $position[0] .'"', E_USER_ERROR);
    }
    
    // horizontal position
    if($position[1] == 'left')
    {
      $offset_width += $this->params['offset_width'];
    }
    else if($position[1] == 'center')
    {
      $offset_width   = (int) (($w - $this->params['width']) / 2) + $this->params['offset_width'];
    }
    else if($position[1] == 'right')
    {
      $offset_width   = (int) ($w - $this->params['width']) + $this->params['offset_width'];
    }
    else
    {
      trigger_error('Unexpected horizontal position "'. $position[1] .'"', E_USER_ERROR);
    }
    
    // create cropped image
		if (function_exists('ImageCreateTrueColor'))
		{
			$des = @ImageCreateTrueColor($this->params['width'], $this->params['height']);
		}else
		{
			$des = @ImageCreate($this->params['width'], $this->params['height']);
		}

		if(!$des)
		{
			return;
		}
		
		// Transparenz erhalten
		$this->keepTransparent($des);
    imagecopyresampled($des, $gdimage, 0, 0, $offset_width, $offset_height, $this->params['width'], $this->params['height'], $this->params['width'], $this->params['height']);
		
		$gdimage = $des;
		$this->image->refreshDimensions();
	}

	function getParams()
	{
		global $REX,$I18N;

		return array(
  		array(
        'label'=>$I18N->msg('imanager_effect_crop_width'),
        'name' => 'width',
        'type' => 'int'
      ),
      array(
        'label'=>$I18N->msg('imanager_effect_crop_height'),
        'name' => 'height',
        'type' => 'int'
      ),
      array(
        'label'=>$I18N->msg('imanager_effect_crop_offset_width'),
        'name' => 'offset_width',
        'type' => 'int'
      ),
      array(
        'label'=>$I18N->msg('imanager_effect_crop_offset_height'),
        'name' => 'offset_height',
        'type' => 'int'
      ),
      array(
        'label' => $I18N->msg('imanager_effect_crop_position'),
        'name' => 'position',
        'type'  => 'select',
        'options' => $this->options,
        'useOptionValues' => true,
        'default' => 'middle_center'
      ),
    );
	}
}