<?php

class rex_xform_select_single_sql extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{

		$SEL = new rex_select();
		$SEL->setName('FORM[' . $this->params["form_name"] . '][el_' . $this->id . ']');
		$SEL->setId("el_" . $this->id);
		$SEL->setSize(1);

		$sql = $this->elements[4];
		$teams = rex_sql::factory();
		$teams->debugsql = $this->params["debug"];
		$teams->setQuery($sql);
		$sqlnames = array();

		// mit --- keine auswahl ---
		if ($this->elements[3] != 1)
			$SEL->addOption($this->elements[3], "0");

		foreach($teams->getArray() as $t)
		{
			if(!isset($this->elements[6]) || $this->elements[6] == "")
				$v = $t['name'];
			else
				$v = $t[$this->elements[6]];

			if($this->elements[5] == "")
				$k = $t['id'];
			else
				$k = $t[$this->elements[5]];
		
			$SEL->addOption( $v, $k);
			if (isset($this->elements[7])) 
				$sqlnames[$k] = $t[$this->elements[7]];
		}

		$wc = "";
		if (isset($warning["el_" . $this->getId()])) $wc = $warning["el_" . $this->getId()];

		$SEL->setStyle(' class="select ' . $wc . '"');

		if ($this->value=="" && isset($this->elements[7]) && $this->elements[7] != "") 
			$this->value = $this->elements[7];
		$SEL->setSelected($this->value);

		$form_output[] = '
			<p class="formselect">
				<label class="select ' . $wc . '" for="el_' . $this->id . '" >' . $this->elements[2] . '</label>
				' . $SEL->get() . '
			</p>';

		$email_elements[$this->elements[1]] = stripslashes($this->value);
		if (isset($sqlnames[$this->value])) 
			$email_elements[$this->elements[1].'_SQLNAME'] = stripslashes($sqlnames[$this->value]);
		if (!isset($this->elements[8]) || $this->elements[8] != "no_db") 
			$sql_elements[$this->elements[1]] = $this->value;
		
	}
	
	function getDescription()
	{
		return "select_single_sql -> Beispiel: select_single_sql|stadt_id|BASE *:|1|select id,name from branding_rex_staedte order by name|[id]|[name]|[default]|[no_db]";
	}
}

?>