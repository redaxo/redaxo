<?php

/**
 * Branded ein Bild mit einem Wasserzeichen
 */

class rex_effect_insert_image extends rex_effect_abstract
{
	function execute()
	{
		global $REX;
		
    // -------------------------------------- CONFIG
    $brandimage = $REX['MEDIAFOLDER'] .'/'. $this->params['brandimage'];
    if(!file_exists($brandimage) || !is_file($brandimage))
      $brandimage = dirname(__FILE__). '/../../media/brand.gif';
      
    // Abstand vom Rand
    $padding_x = -10;
    if(isset($this->params['padding_x']))
      $padding_x = (int) $this->params['padding_x'];
    
    $padding_y = -10;
    if(isset($this->params['padding_y']))
      $padding_y = (int) $this->params['padding_y'];
    
    // horizontale ausrichtung: left/center/right
    $hpos = 'right';
    if(isset($this->params['hpos']))
      $hpos = (string) $this->params['hpos'];
      
    // vertikale ausrichtung:   top/center/bottom
    $vpos = 'bottom';
    if(isset($this->params['vpos']))
      $vpos = (string) $this->params['vpos'];
    
    // -------------------------------------- /CONFIG
  
    $brand = new rex_image($brandimage);
    
    if($this->params['imagetype'] > 0)
    {
      $cachepath = $REX['INCLUDE_PATH'].'/generated/files/';
      
      $image_cacher  = new rex_image_cacher($cachepath);
      $image_manager = new rex_image_manager($image_cacher);
      
      $qry = 'SELECT name FROM '. $REX['TABLE_PREFIX'].'679_types WHERE id='. $this->params['imagetype'];
      $sql = rex_sql::factory();
      $sql->setQuery($qry);
    
      $brand = $image_manager->applyEffects($brand, $sql->getValue('name'));
    }
  
    $brand->prepare();
    $gdbrand =& $brand->getImage();
    $gdimage =& $this->image->getImage();
    
    $image_width  = $this->image->getWidth();
    $image_height = $this->image->getHeight();
    $brand_width  = $brand->getWidth();
    $brand_height = $brand->getHeight();
    
    switch($hpos)
    {
      case 'left':
        $dstX = 0;
        break;
      case 'center':
        $dstX = (int)(($image_width - $brand_width) / 2);
        break;
      case 'right':
      default:
        $dstX = $image_width - $brand_width;
    }
  
    switch($vpos)
    {
      case 'top':
        $dstY = 0;
        break;
      case 'center':
        $dstY = (int)(($image_height - $brand_height) / 2);
        break;
      case 'bottom':
      default:
        $dstY = $image_height - $brand_height;
    }
    
    imagealphablending($gdimage, true);
    imagecopy($gdimage, $gdbrand, $dstX + $padding_x, $dstY + $padding_y, 0, 0, $brand_width, $brand_height);

    $brand->destroy();
	}
	
	function getParams()
	{
		global $REX,$I18N;

    $imagetypes = array();
    
    $qry = 'SELECT id,name FROM '. $REX['TABLE_PREFIX'].'679_types ORDER BY status';
    $sql = rex_sql::factory();
    $sql->setQuery($qry);
    
    $imagetypes[] = array($I18N->msg('imanager_effect_brand_noimagetype'), 0);
    while($sql->hasNext())
    {
      $imagetypes[] = array($sql->getValue('name'), $sql->getValue('id'));
      $sql->next();
    }
    
		return array(
			array(
				'label' => $I18N->msg('imanager_effect_brand_image'),
				'name' => 'brandimage',
				'type'	=> 'media',
				'default' => ''
			),
			array(
				'label' => $I18N->msg('imanager_effect_brand_hpos'),
				'name' => 'hpos',
				'type'	=> 'select',
				'options'	=> array('right','center','left'),
			  'useOptionValues' => true,
				'default' => 'right'
			),
			array(
				'label' => $I18N->msg('imanager_effect_brand_vpos'),
				'name' => 'vpos',
				'type'	=> 'select',
				'options'	=> array('top','center','bottom'),
        'useOptionValues' => true,
			  'default' => 'bottom'
			),
			array(
				'label' => $I18N->msg('imanager_effect_brand_padding_x'),
				'name' => 'padding_x',
				'type'	=> 'int',
				'default' => '-10'
			),
			array(
				'label' => $I18N->msg('imanager_effect_brand_padding_y'),
				'name' => 'padding_y',
				'type'	=> 'int',
				'default' => '-10'
			),
      array(
        'label' => $I18N->msg('imanager_effect_brand_imagetype'),
        'name' => 'imagetype',
        'type'  => 'select',
        'options' => $imagetypes,
      ),
		);
	}

}
