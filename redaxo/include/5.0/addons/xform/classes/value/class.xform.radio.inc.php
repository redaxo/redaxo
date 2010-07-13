<?php

// Dateiname: class.xform.radio.inc.php

class rex_xform_radio extends rex_xform_abstract
{
	function init()
	{
		foreach (explode(";", $this->elements[3]) as $v)
		{
			$teile = explode("=", $v);
			$wert = $teile[0];
			if (is_array($teile) && isset ($teile[1]))
			{
				$bezeichnung = $teile[1];
			}else
			{
				$bezeichnung = $teile[0];
			}
			$this->setKey($bezeichnung,$wert);
		}
	}
	
	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{
		if ($this->value == "" && !$send)
		{
			if (isset($this->elements[5])) 
				$this->value = $this->elements[5];
		}else
		{

		}

		$out = "";
		$i = 0;
		foreach($this->getKeys() as $wert => $bezeichnung)
		{
			$i++;
			$out .= '<span class="formradio">';
			$out .= '<input type="radio" name="FORM[' . $this->params["form_name"] . '][el_' . $this->id . ']" id="el_'.$this->id.'_'.$i.'" value="'.$wert.'" ';
			if ($this->value == $wert) 
			 $out .= ' checked="checked"';
			$out .= ' />';
			$out .= '<label class="radio" for="el_'.$this->id.'_'.$i.'">'.$bezeichnung.'</label>';
			$out .= '</span>';
			
		}

		$wc = "";
		if (isset($warning["el_" . $this->getId()])) 
		  $wc = $warning["el_" . $this->getId()];


		$form_output[] = ' 
			<div class="xform-element formradio form_'.$this->elements[1].' '.$wc.'">
				<label class="radio" >' . $this->elements[2] . '</label>
				<p class="radio">' .$out . '</p>
			</div>';

		$email_elements[$this->elements[1]] = stripslashes($this->value);
		if (!isset($this->elements[4]) || $this->elements[4] != "no_db") 
		  $sql_elements[$this->elements[1]] = $this->value;

	}
	
	function getDescription()
	{
		return "radio -> Beispiel: radio|gender|Geschlecht *|Frau=w;Herr=m|[no_db]|defaultwert";
	}
}