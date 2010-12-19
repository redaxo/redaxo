<?php

class rex_xform_be_em_relation extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{
		global $REX;

		// ---------- CONFIG & CHECK

		$this->be_em = array();
		$this->be_em["source_table"] = substr($this->params["main_table"],12); // "rex_em_data_" wegcutten
		$this->be_em["label"] = $this->elements[2];	// HTML Bezeichnung
		$this->be_em["target_table"] = $this->elements[3]; // Zieltabelle
		$this->be_em["target_field"] = $this->elements[4]; // Zielfield welches angezeigt wird.
		$this->be_em["relation_type"] = (int) $this->elements[5]; // single = 0 / multiple = 1 / popup = 2
		if($this->be_em["relation_type"] > 2)	{ $this->be_em["relation_type"] = 0; }
		$this->be_em["eoption"] = (int) $this->elements[6]; // "Leer" Option
		if($this->be_em["eoption"] != 1) { $this->be_em["eoption"] = 0; }
		$disabled = FALSE;
    
    if($this->be_em["relation_type"] == 2)
    {
    	if($this->params["main_id"] < 1)
    	{
    		$text = 'Diesen Bereich k&ouml;nnen Sie erst bearbeiten, wenn der Datensatz angelegt wurde.';
    		
    	}else
    	{
    		
    		$link = 'javascript:em_openRelation('.$this->getId().',\''.$this->be_em["target_table"].'\',\'id'.
    		'&rex_em_filter['.$this->be_em["target_field"].']='.$this->params["main_id"].
        '&rex_em_set['.$this->be_em["target_field"].']='.$this->params["main_id"].
    		'\');';
    		
        $text = '<a href="'.$link.'">'.
        'Link'.
        '</a>';
    	}
    	
    	 $form_output[] = '
        <p class="formhtml">
          <label class="select " for="el_' . $this->getId() . '" >' . $this->be_em["label"] . '</label>
          <input type="hidden" name="FORM[' . $this->params["form_name"] . '][el_' . $this->getId() . '][]" id="REX_RELATION_'.$this->getId().'" />
          <span>'.$text.'</span>
        </p>';
    
      return;
    }
    
    
    
    
    
    
    // ---------- Datensatz existiert bereits, Values aus verknŸpfungstabelle holen
		if($this->params["main_id"] > 0 && $send == 0)
		{
		$vs = rex_sql::factory();
		$sss->debugsql = $this->params["debug"];
    	$vs->setQuery('select target_id as id from '.$REX['TABLE_PREFIX'].'em_relation where source_table="'.$this->be_em["source_table"].'" and source_name="'.$this->getName().'" and source_id="'.$this->params["main_id"].'"');
    	$v = $vs->getArray();
    	$values = array();
      if(count($v)>0) foreach($v as $w) { $values[$w["id"]] = $w["id"]; };
      
      // Fallback - wenn nichts da, dann vielleicht im Datensatz selbst.
      // Nur nštig wenn man Daten aus anderen Tabellen importiert hat und
      // man die Datenstruktur angleichen mšchte.
      if(trim($this->getValue()) != "") { $values = array_merge($values, explode(",",$this->getValue())); }

      // Neue Daten speichern
      $this->setValue($values);
    }
		
    // ---------- Fertigsets einbauen. Sind quasi fest eingebrannte Werte
    if(isset($this->params["rex_em_set"][$this->getName()]))
    {
    	$values = $this->getValue();
    	$values[] = $this->params["rex_em_set"][$this->getName()];
    	$this->setValue($values);
    	$disabled = TRUE;
    }
		
		// ---------- Value angleichen -> immer Array mit IDs daraus machen
		if(!is_array($this->getValue()))
		{
			if(trim($this->getValue()) == "")
			{
				$this->setValue(array());
			}else
			{
				$this->setValue(explode(",",$this->getValue()));
			}
		}
		// Ab hier ist Value immer Array

		// Values prŸfen

		$sql = 'select id,'.$this->be_em["target_field"].' from '.$REX['TABLE_PREFIX'].'em_data_'.$this->be_em["target_table"];
		$value_names = array();
		if(count($this->getValue())>0)
		{
			$addsql = '';
			foreach($this->getValue() as $v)
			{
				if($addsql != "")
				{
					$addsql .= ' OR ';
				}
				$addsql .= ' id='.$v.'';
			}

			if($addsql != "")
			{
				$sql .= ' where '.$addsql;
			}
			$values = array();
			$vs = rex_sql::factory();
			$sss->debugsql = $this->params["debug"];
			$vs->setQuery($sql);
			foreach($vs->getArray() as $v)
			{
				$value_names[$v["id"]] = $v[$this->be_em["target_field"]];
				$values[] = $v["id"];
			}
			$this->setValue($values);
		}

    if($send == 1 && $this->be_em["eoption"] == 0 && count($this->getValue()) == 0)
    {
      // Error. Fehlermeldung ausgeben
      $this->params["warning"][] = $this->elements[7];
      $this->params["warning_messages"][] = $this->elements[7];
      $wc = $this->params["error_class"];
    }
		
		$wc = "";
		if (isset($warning["el_" . $this->getId()]))
		{
			$wc = $warning["el_" . $this->getId()];
		}


		// ----- SELECT BOX
		$sss = rex_sql::factory();
		$sss->debugsql = $this->params["debug"];
		$sss->setQuery('select * from '.$REX['TABLE_PREFIX'].'em_data_'.$this->be_em["target_table"].' order by '.$this->be_em["target_field"]);

		$SEL = new rex_select();
		$SEL->setName('FORM[' . $this->params["form_name"] . '][el_' . $this->id . '][]');
		$SEL->setId("el_" . $this->id);

		$SEL->setDisabled($disabled);
		$SEL->setSize(1);

		// mit --- keine auswahl ---

		if($this->be_em["relation_type"] == 1)
		{
			$SEL->setMultiple(TRUE);
			$SEL->setSize(5);
		}elseif($this->be_em["eoption"]==1)
		{
			$SEL->addOption("-", "");
		}

		foreach($sss->getArray() as $v)
		{
			$SEL->addOption( $v[$this->be_em["target_field"]],  $v["id"]);
		}

		// var_dump($this->getValue());
		$SEL->setSelected($this->getValue());

		$form_output[] = '
        <p class="formselect">
          <label class="select ' . $wc . '" for="el_' . $this->id . '" >' . $this->be_em["label"] . '</label>
          ' . $SEL->get() . '
        </p>';

		$email_elements[$this->getName()] = stripslashes(implode(",",$this->getValue()));
		$sql_elements[$this->getName()] = implode(",",$this->getValue());

		return;

	}




	/*
	 * postAction wird nach dem Speichern ausgefŸhrt
	 * hier wird entsprechend der entities
	 */
	function postAction(&$email_elements, &$sql_elements)
	{
    global $REX;

		$source_id = -1;
		if (isset($email_elements["ID"]) && $email_elements["ID"] > 0)
		{
			$source_id = (int) $email_elements["ID"];
		}
		if ($source_id < 1 && isset($this->params["main_id"]) && $this->params["main_id"] > 0)
		{
			$source_id = (int) $this->params["main_id"];
		}

		if($source_id < 1 || $this->params["main_table"] == "")
		{
			return FALSE;
		}

		// ----- Value angleichen -> immer Array mit IDs daraus machen
		$values = array();
		if(!is_array($this->getValue()))
		{
			if(trim($this->getValue()) != "")
			{
				$values = explode(",",$this->getValue());
			}
		}else
		{
			$values = $this->getValue();
		}

		// ----- Datensaetze aus der Relationstabelle lšschen
		$d = rex_sql::factory();
		$sss->debugsql = $this->params["debug"];
		$d->setQuery('delete from '.$REX['TABLE_PREFIX'].'em_relation where source_table="'.$this->be_em["source_table"].'" and source_name="'.$this->getName().'" and source_id="'.$source_id.'"');

		// ----- Datensaetze in die Relationstabelle eintragen
		if(count($values)>0)
		{

			$i = rex_sql::factory();
			$sss->debugsql = $this->params["debug"];
			foreach($values as $v)
			{
				$i->setTable($REX['TABLE_PREFIX'].'em_relation');
				$i->setValue('source_table', $this->be_em["source_table"]);
				$i->setValue('source_name', $this->getName());
				$i->setValue('source_id', $source_id);
				$i->setValue('target_table', $this->be_em["target_table"]);
				$i->setValue('target_id', $v);
				$i->insert();
			}

		}
			
	}

	/*
	 * Allgemeine Beschreibung
	 */
	function getDescription()
	{
		// label,bezeichnung,tabelle,tabelle.feld,relationstype,style,no_db
		// return "be_em_relation -> Beispiel: ";
		return "";
	}

	function getDefinitions()
	{
		return array(
						'type' => 'value',
						'name' => 'be_em_relation',
						'values' => array(
							array( 'type' => 'name',		'label' => 'Name' ),
							array( 'type' => 'text',		'label' => 'Bezeichnung'),
							array( 'type' => 'table',		'label' => 'Ziel Tabelle'),
							array( 'type' => 'table.field',	'label' => 'Ziel Tabellenfeld zur Anzeige'),
							array( 'type' => 'select',    'label' => 'Mehrfachauswahl', 'default' => '', 'definition' => 'single=0;multiple=1;popup=2' ),
							array( 'type' => 'boolean',		'label' => 'Mit "Leer-Option"' ),
					    array( 'type' => 'text',    'label' => 'Fehlermeldung wenn "Leer-Option" nicht aktiviert ist.'),
						),
						'description' => 'Hiermit kann man Verkn&uuml;pfungen zu anderen Tabellen setzen',
						'dbtype' => 'text'
						);
	}


}

?>