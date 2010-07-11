<?php

class rex_effect_abstract
{
	var $image = array(); // rex_image
	var $params = array(); // effekt parameter
	
	function setImage(&$image)
	{
    if(!rex_image::isValid($image))
    {
      trigger_error('Given image is not a valid rex_image_abstract', E_USER_ERROR);
    }
		$this->image = &$image;
	}
		
	function setParams($params)
	{
		$this->params = $params;
	}	
	
	function execute()
	{
	  // exectute effect on $this->img
	}
	
	function getParams()
	{
	  // returns an array of parameters which are required for the effect
	}
}