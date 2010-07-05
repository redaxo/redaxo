<?php

class rex_xform_validate_customfunction extends rex_xform_validate_abstract 
{

  function enterObject(&$warning, $send, &$warning_messages)
  {
    if($send=="1")
    {
    	
      $f = $this->elements[3];
      $l = $this->elements[2];
      $p = $this->elements[4];
      
      foreach($this->obj_array as $Object)
      {
      	if(function_exists($f))
      	{
      		if($f($l,$Object->getValue(),$p))
      		{
            $warning["el_" . $Object->getId()] = $this->params["error_class"];
            $warning_messages[] = $this->elements[5];
      		}
      	}else
      	{
      		$warning["el_" . $Object->getId()] = $this->params["error_class"];
          $warning_messages[] = 'ERROR: customfunction "'.$f.'" not found';
      	}
      	
      }
    }
  }
  
  function getDescription()
  {
    return "customfunction -> prueft Ÿber customfunc, beispiel: validate|customfunction|label|functionname|weitere_parameter|warning_message";
  }
  
  function getLongDesription()
  {
  	
  	
  }

}

?>