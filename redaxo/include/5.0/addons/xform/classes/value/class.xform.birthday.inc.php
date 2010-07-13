<?php

// TODO: Formatierung optional änderbar
// Format: 1972-11-19

class rex_xform_birthday extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{
		
		$day = 0;
		$month = 0;
		$year = 0;
		
		if (@strlen($this->value) == 10)
		{
			$day = (int) substr($this->value,8,2);
			$month = (int) substr($this->value,5,2);
			$year = (int) substr($this->value,0,4);
		}else
		{
			if (isset($_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id]["day"])) $day = (int) $_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id]["day"];
			if (isset($_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id]["month"])) $month = (int) $_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id]["month"];
			if (isset($_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id]["year"])) $year = (int) $_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id]["year"];
		}
		
		$formname = 'FORM['.$this->params["form_name"].'][el_'.$this->id.']';

		$isodatum = sprintf ("%04d-%02d-%02d", $year, $month, $day);
		$datum = $isodatum;

		$twarning = "";
		if (isset($this->elements[4]) && $this->elements[4]==1 && !checkdate($month,$day,$year) && $send == 1)
		{
			$twarning = 'border:1px solid #f99;background-color:#f9f3f3;';
			$warning[] = "Geburtsdatum ist falsch";
		}else
		{
			$email_elements[$this->elements[1]] = "$day.$month.$year";
			$sql_elements[$this->elements[1]] = $datum;
		}
		

		
		$out = "";
		$out .= '
		<p class="formbirthday">
					<label class="select" for="el_'.$this->id.'" >'.$this->elements[2].'</label>';
					
		$dsel = new rex_select;
		$dsel->setName($formname.'[day]');
		$dsel->setStyle("width:50px;".$twarning);
		$dsel->setId('el_'.$this->id.'_day');
		$dsel->setSize(1);
		$dsel->addOption("TT","0");
		for($i=1;$i<32;$i++)
		{
			$dsel->addOption($i,$i);
		}
		$dsel->setSelected($day);
		$out .= $dsel->get();

		$msel = new rex_select;
		$msel->setName($formname.'[month]');
		$msel->setStyle("width:50px;".$twarning);
		$msel->setId('el_'.$this->id.'_month');
		$msel->setSize(1);
		$msel->addOption("MM","0");
		for($i=1;$i<13;$i++)
		{
			$msel->addOption($i,$i);
		}
		$msel->setSelected($month);
		$out .= $msel->get();

		$ysel = new rex_select;
		$ysel->setName($formname.'[year]');
		$ysel->setStyle("width:80px;".$twarning);
		$ysel->setId('el_'.$this->id.'_year');
		$ysel->setSize(1);
		$ysel->addOption("YYYY","0");
		for($i=1930;$i<2000;$i++)
		{
			$ysel->addOption($i,$i);
		}
		$ysel->setSelected($year);
		$out .= $ysel->get();

		$out .= '</p>';

		$form_output[] = $out;

	}
	function getDescription()
	{
		return "birthday -> Beispiel: birthday|feldname|Text *|[format: Y-m-d]|Pflicht";
	}
}

?>