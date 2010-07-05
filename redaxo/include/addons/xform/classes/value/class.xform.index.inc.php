<?php

class rex_xform_index extends rex_xform_abstract
{

  function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
  { 
  }
  
  function getDescription()
  {
    return "index -> Beispiel: index|label|label1,label2,label3|[no_db]|[func/md5/sha]";
  }

  function preValidateAction()
  {
  	
  	if($this->params["send"] == 1)
  	{
  	
	    // Labels auslesen
	    $index_labels = explode(",",$this->elements[2]);
	  	
	  	// echo "<pre>";var_dump($this->element_values["email"]); echo "</pre>";
	  	
	    $this->value = "";
	    
	  	foreach($this->obj as $o)
	    {
	    	  if(in_array($o->getName(),$index_labels))
           $this->value .= $o->getvalue();
	    }
	    
	    $fnc = trim($this->elements[4]);
	    if(function_exists($fnc))
	    {
	    	$this->value = call_user_func($fnc, $this->value);
	    }
	    
	  	$this->element_values["email"][$this->getName()] = $this->value;
	    if (!isset($this->elements[3]) || $this->elements[3] != "no_db") 
	      $this->element_values["sql"][$this->getName()] = $this->value;

  	}
  }
  
  function getDefinitions()
  {
    return array(
            'type' => 'value',
            'name' => 'index',
            'values' => array(
              array( 'type' => 'name',   'label' => 'Feld' ),
              array( 'type' => 'names',  'label' => 'Names, kommasepariert'),
              array( 'type' => 'no_db',   'label' => 'Datenbank',  'default' => 1),
              array( 'type' => 'select',  'label' => 'Opt. Codierfunktion', 'default' => '0', 'definition' => 'Keine Funktion=;md5=md5;sha=sha' ),
            ),
            'description' => 'Erstellt einen Index Ÿber Felder/Labels, die man selbst festlegen kann.',
            'dbtype' => 'text'
      );
  
  }
}

?>