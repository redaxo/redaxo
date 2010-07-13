<?php

class rex_xform_validate_email extends rex_xform_validate_abstract 
{

	function enterObject(&$warning, $send, &$warning_messages)
	{
		if($send=="1")
			foreach($this->obj_array as $Object)
			{
				if($Object->getValue())
				{
					if( !preg_match("#^[\w.+-]{2,}\@[\w.-]{2,}\.[a-z]{2,6}$#",$Object->getValue()) )
					{
						$warning["el_" . $Object->getId()] = $this->params["error_class"];
						$warning_messages[] = $this->elements[3];
					}
				}
			}
	}
	
	function getDescription()
	{
		return "email -> prueft ob email korrekt ist. leere email ist auch korrekt, bitte zusaetzlich mit ifempty prŸfen, beispiel: validate|email|emaillabel|warning_message ";
	}
	
	function getDefinitions()
	{
		return array(
						'type' => 'validate',
						'name' => 'email',
						'values' => array(
             	array( 'type' => 'getname',   	'label' => 'Name' ),
              array( 'type' => 'text',    		'label' => 'Fehlermeldung'),
						),
						'description' => 'Hiermit wird ein Label ŸberprŸft ob es eine E-Mail ist',
			);
	
	}
	
}