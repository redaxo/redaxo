<?php

class rex_xform_select_datetime extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{

		$day = date("d");
		$month = date("m");
		$year = date("Y");
		$hour = date("H");
		$min = date("i");
		$sec = 0;
		
		if (!is_array($this->getValue()) && strlen($this->getValue()) == 19)
		{
			if($a = explode(" ",$this->getValue()))
			{
				if($d = explode("-",$a[0]))
				{
					$year = (int) $d[0];
					$month = (int) $d[1];
					$day = (int) $d[2];
				}
				if(isset($a[1]))
				{
					if($d = explode(":",$a[1]))
					{
						$hour = (int) $d[0];
						$min = (int) $d[1];
						$sec = (int) $d[2];
					}
				}			
			}
		}
		
		$formname = 'FORM['.$this->params["form_name"].'][el_'.$this->id.']';

		$twarning = "";
		if (!checkdate($month,$day,$year) && $send == 1)
		{
			$twarning = 'border:1px solid #f99; background-color:#f9f3f3;';
			$warning[] = "Datum ist falsch";
		}
		
		$isodatum = sprintf ("%04d-%02d-%02d %02d:%02d:%02d", $year, $month, $day, $hour, $min, 0);

		$email_elements[$this->getName()] = $isodatum;
		$sql_elements[$this->getName()] = $isodatum;
		
		$out = "";
		$out .= '
		<p class="form_select_datetime">
					<label class="select" for="el_'.$this->getId().'" >'.$this->elements[2].'</label>';
				
		$dsel = new rex_select;
		$dsel->setName($formname.'[day]');
		$dsel->setStyle($twarning);
		$dsel->setAttribute('class', 'formdate-day');
		$dsel->setId('el_'.$this->id.'');
		$dsel->setSize(1);
		// $dsel->addOption("TT","0");
		for($i=1;$i<32;$i++)
		{
			$dsel->addOption($i,$i);
		}
		$dsel->setSelected($day);
		$out .= '<div class="day"><span>Tag:</span>'.$dsel->get().'</div>';

		$msel = new rex_select;
		$msel->setName($formname.'[month]');
		$msel->setStyle($twarning);
		$msel->setAttribute('class', 'formdate-month');
		$msel->setId('el_'.$this->id.'_month');
		$msel->setSize(1);
		// $msel->addOption("MM","0");
		for($i=1;$i<13;$i++)
		{
			$msel->addOption($i,$i);
		}
		$msel->setSelected($month);
		$out .= '<div class="month"><span>Monat:</span>'.$msel->get().'</div>';

		$y = explode(",",$this->elements[3]);
		$year_start = (int) @$y[0];
		$year_end = (int) @$y[1];
		
		if ($year_start == 0) 
			$year_start = date("Y")-5;
		if ($year_end == 0) 
			$year_start = date("Y")+5;

		if ($year_end<$year_start) 
			$year_end = $year_start;

		$ysel = new rex_select;
		$ysel->setName($formname.'[year]');
		$ysel->setStyle($twarning);
		$ysel->setAttribute('class', 'formdate-year');
		$ysel->setId('el_'.$this->id.'_year');
		$ysel->setSize(1);
		// $ysel->addOption("YYYY","0");
		for($i=$year_start;$i<=$year_end;$i++)
		{
			$ysel->addOption($i,$i);
		}
		$ysel->setSelected($year);
		$out .= '<div class="year"><span>Jahr:</span>'.$ysel->get().'</div>';

		$hsel = new rex_select;
		$hsel->setName($formname.'[hour]');
		$hsel->setStyle($twarning);
		$hsel->setAttribute('class', 'formdate-hour');
		$hsel->setId('el_'.$this->id.'_hour');
		$hsel->setSize(1);
		// $hsel->addOption("HH","00");
		for($i=0;$i<24;$i++)
		{
			$hsel->addOption(str_pad($i,2,'0',STR_PAD_LEFT),str_pad($i,2,'0',STR_PAD_LEFT));
		}
		$hsel->setSelected($hour);
		$out .= '<div class="hour"><span>Stunde:</span>'.$hsel->get().'h</div>';

		$msel = new rex_select;
		$msel->setName($formname.'[min]');
		$msel->setStyle($twarning);
		$msel->setAttribute('class', 'formdate-minute');
		$msel->setId('el_'.$this->id.'_min');
		$msel->setSize(1);
		// $msel->addOption("MM","0");
		
		$mmm = array();
		if(isset($this->elements[4]) && $this->elements[4] != "")
			$mmm = explode(",",trim($this->elements[4]));
		
		if(count($mmm)>0)
		{
			foreach($mmm as $m)
			{
				$msel->addOption($m,$m);
			}
		}else
		{
			for($i=0;$i<61;$i++)
			{
				$msel->addOption(str_pad($i,2,'0',STR_PAD_LEFT),str_pad($i,2,'0',STR_PAD_LEFT));
			}
		}
		$msel->setSelected($min);
		$out .= '<div class="minute"><span>Minute:</span>'.$msel->get().'m</div>';

		$out .= '</p>';

		$form_output[] = $out;

	}
	function getDescription()
	{
		return "select_datetime -> Beispiel: select_datetime|feldname|Text *|jahrstart,jahrsende|minutenformate 00,15,30,45";
	}
	
	function preValidateAction()
	{
		if(is_array($this->getValue()))
		{
			$a = $this->getValue();
			$day = (int) @$a["day"];
			$month = (int) @$a["month"];
			$year = (int) @$a["year"];
			$hour = (int) @$a["hour"];
			$min = (int) @$a["min"];
			
			$r = 
				str_pad($year, 4, "0", STR_PAD_LEFT)."-".
				str_pad($month, 2, "0", STR_PAD_LEFT)."-".
				str_pad($day, 2, "0", STR_PAD_LEFT)." ".
				str_pad($hour, 2, "0", STR_PAD_LEFT).":".
				str_pad($min, 2, "0", STR_PAD_LEFT).":00";
			
			$this->setValue($r);
		}
	}
	
	
	
	
}

?>