<?php

class rex_xform_be_mediapool extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{	
		global $REX;
		
		if(!isset($REX["xform_classes_be_mediapool"]))
			$REX["xform_classes_be_mediapool"] = 0;
		
		$REX["xform_classes_be_mediapool"]++;
		
		$i = $REX["xform_classes_be_mediapool"];
		
		if ($this->value == "" && !$send)
			if (isset($this->elements[3])) 
				$this->value = $this->elements[3];

		$wc = "";
		if (isset($warning["el_" . $this->getId()])) 
			$wc = $warning["el_" . $this->getId()];

			$form_output[] = '
		<div class="xform-element formbe_mediapool formlabel-'.$this->getName().'">
        <label class="text ' . $wc . '" for="el_' . $this->getId() . '" >' . $this->elements[2] . '</label>
        
			<div class="rex-widget">
		      <div class="rex-widget-media">
		        <p class="rex-widget-field">
		          <input type="text" class="text '.$wc.'" name="FORM['.$this->params["form_name"].'][el_'.$this->id.']" id="REX_MEDIA_'.$i.'" readonly="readonly" value="'.htmlspecialchars(stripslashes($this->value)) . '" />
		        </p>
		        <p class="rex-widget-icons rex-widget-1col">
		          <span class="rex-widget-column rex-widget-column-first">
		            <a href="#" class="rex-icon-file-open" onclick="openREXMedia('.$i.',\'\');return false;" title="Medium auswŠhlen"></a>
		            <a href="#" class="rex-icon-file-add" onclick="addREXMedia('.$i.');return false;" title="Neues Medium hinzufŸgen"></a>
		            <a href="#" class="rex-icon-file-delete" onclick="deleteREXMedia('.$i.');return false;" title="AusgewŠhltes Medium lšschen"></a>
		            <a href="#" class="rex-icon-file-view" onclick="viewREXMedia('.$i.');return false;" title="Medium auswŠhlen"></a>
		          </span>
		        </p>
		        <div class="rex-media-preview"></div>
		      </div>
		    </div>
		    <div class="rex-clearer"></div>
    </div>
  ';		
		
		
		
		
		
		
		
		
		$email_elements[$this->elements[1]] = stripslashes($this->value);

		if (!isset($this->elements[4]) || $this->elements[4] != "no_db") 
			$sql_elements[$this->elements[1]] = $this->value;
	}
	
	function getDescription()
	{
		return "be_mediapool -> Beispiel: be_mediapool|label|Bezeichnung|defaultwert|no_db";
	}
	
	function getDefinitions()
	{
		return array(
						'type' => 'value',
						'name' => 'be_mediapool',
						'values' => array(
             	array( 'type' => 'name',   'label' => 'Name' ),
              array( 'type' => 'text',    'label' => 'Bezeichnung'),
              array( 'type' => 'text', 		'label' => 'Defaultwert'),
						),
						'description' => 'Mediafeld, welches eine Datei aus dem Medienpool holt',
						'dbtype' => 'text'
			);
	}
	
	
}

?>