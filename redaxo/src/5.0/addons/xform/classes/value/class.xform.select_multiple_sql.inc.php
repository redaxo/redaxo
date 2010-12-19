<?php

class rex_xform_select_multiple_sql extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{

		// ***** SELECT FESTLEGEN
		
		$SEL = new rex_select();
		$SEL->setName('FORM[' . $this->params["form_name"] . '][el_' . $this->id . '][]');
		$SEL->setId("el_" . $this->id);
		$SEL->setSize(5);
		$SEL->setMultiple(1);


		// ***** SQL - ROHDATEN ZIEHEN

		$sql = $this->elements[5];
		$teams = rex_sql::factory();
		$teams->debugsql = $this->params["debug"];
		$teams->setQuery($sql);
		for ($t = 0; $t < $teams->getRows(); $t++)
		{
			$SEL->addOption($teams->getValue($this->elements[7]), $teams->getValue($this->elements[6]));
			$teams->next();
		}
		
		$wc = "";
		// if (isset($warning["el_" . $this->getId()])) $wc = $warning["el_" . $this->getId()];
		$SEL->setStyle('class="multipleselect ' . $wc . '"');
		

		// ***** EINGELOGGT ODER NICHT SETZEN

		if ($send == 0)
		{
			// erster aufruf
			// Daten ziehen
			
			if ($this->params["main_id"]>0)
			{
				$this->value = array();
				$g = rex_sql::factory();
				$g->debugsql = $this->params["debug"];
				$g->setQuery('select '.$this->elements[3].' from '.$this->elements[1].' where '.$this->elements[2].'='.$this->params["main_id"]);
				$gg = $g->getArray();
				if (is_array($gg))
				{
					foreach($gg as $g)
					{
						$this->value[] = $g[$this->elements[3]];
					}
				}
			}
		}

		// ***** AUSWAHL SETZEN
		if (is_array($this->value))
		{
			foreach($this->value as $val) $SEL->setSelected($val);
		}
		

		// ***** AUSGEBEN

		$form_output[] = '
			<p class="formmultipleselect">
				<label class="multipleselect ' . $wc . '" for="el_' . $this->id . '" >' . $this->elements[4] . '</label>
				' . $SEL->get() . '
			</p>';
		
	}
	
	function postAction(&$email_elements, &$sql_elements)
	{
	
		$id = -1;
		if (isset($email_elements["ID"]) && $email_elements["ID"]>0) $id = (int) $email_elements["ID"];
		if (isset($this->params["main_id"]) && $this->params["main_id"]>0) $id = (int) $this->params["main_id"];
	
		if ($id >0)
		{
	
			// alte eintraege loeschen
			// neue eintraege setzen
			$g = rex_sql::factory();
			$g->debugsql = $this->params["debug"];
			$g->setQuery('delete from '.$this->elements[1].' where '.$this->elements[2].'='.$id);
			
			if (is_array($this->value))
			{
				foreach($this->value as $val)
				{
					$g->setQuery('insert into '.$this->elements[1].' set '.$this->elements[3].'="'.$val.'", '.$this->elements[2].'='.$id);
				}
			}
		
		}
	}
	
	function getDescription()
	{
		return "select_multiple_sql -> Beispiel: select_multiple_sql|rex_rel_user_city|user_id|city_id|	StŠdte *:|select * from city order by name|id|name";
	}
}

?>