<?php

class rex_xform_lang_textarea extends rex_xform_abstract
{

	function getLangDivider()
	{
		return "^^^^°°°°";
	}

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{	
		global $REX;

		$text = array();
		if(is_array($this->getvalue()))
		{
			foreach($this->getvalue() as $k => $t)
			{
				$text[$k] = $t;
			}
		}elseif(is_string($this->getvalue()) and $this->getvalue() != "")
		{
			$text = explode(rex_xform_lang_textarea::getLangDivider(),$this->getValue());
		}

		$wc = "";
		if (isset($warning["el_" . $this->getId()])) 
			$wc = $warning["el_" . $this->getId()];
		
		$tmp = '
		<div class="formtextarea formlangtextarea " id="form_p_'.$this->getId().'">
			<p><label class="textarea ' . $wc . '" for="el_' . $this->getId() . '" >' . $this->elements[2] . '</label></p>
			';
		
		
		$tmp .= '<div class="tabs">';	
		$tmp .= '<ul class="navi-tab">';	
		foreach($REX['CLANG'] as $l => $lang)
		{
			$tmp .= '<li><a id="tab_a_'.$l.'" href="#tab_'.$l.'">'.$lang.'</a></li>';
		}		
		$tmp .= '</ul>';
		
		foreach($REX['CLANG'] as $l => $lang)
		{
			$t = "";
			if(isset($text[$l]))
				$t = $text[$l];
			
			$tmp .= '<p class="tab" id="tab_'.$l.'">
				<textarea class="textarea ' . $wc . '" name="FORM[' . $this->params["form_name"] . '][el_' . $this->id . ']['.$l.']" id="el_' . $this->getId() . '_'.$l.'" cols="80" rows="10">' . 
				htmlspecialchars(stripslashes($t)) . 
				'</textarea>
				</p>
				';
		}
			
		$tmp .= '</div>';
		$tmp .= '</div>';

		$script = '
			<script type="text/javascript">
			jQuery(function($) {
			        var tabContainers = $(\'#form_p_'.$this->getId().' div.tabs > p.tab\');
			        
			        $(\'#form_p_'.$this->getId().' div.tabs .navi-tab a\').click(function () {
			                tabContainers.hide().filter(this.hash).show();
			                $(\'#form_p_'.$this->getId().' div.tabs .navi-tab a\').removeClass(\'active\');
			                $(this).addClass(\'active\');
			                return false;
			        }).filter(\'#tab_a_'.$REX["CUR_CLANG"].'\').click();
			        
			});
			</script>';

		$form_output[] = $tmp.$script;

		$this->setValue(implode($this->getLangDivider(),$text));

		$email_elements[$this->elements[1]] = stripslashes($this->getValue());
		if (!isset($this->elements[3]) || $this->elements[3] != "no_db") 
			$sql_elements[$this->elements[1]] = $this->value;
	}
	
	function getDescription()
	{
		return "textarea -> Beispiel: lang_textarea|label|FieldLabel|[no_db]";
	}
	
	function getDefinitions()
	{
    return array(
            'type' => 'value',
            'name' => 'lang_textarea',
            'values' => array(
              array( 'type' => 'name',   'label' => 'Feld' ),
              array( 'type' => 'text',    'label' => 'Bezeichnung'),
              array( 'type' => 'no_db',   'label' => 'Datenbank',  'default' => 1),
            ),
            'description' => 'Ein mehrzeiliges mehrsprachiges Textfeld als Eingabe',
            'dbtype' => 'text'
      );
	}
}

?>