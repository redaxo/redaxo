<?PHP

// TODO :: GEHT NOCH NICHT

class rex_xform_validate_sunity_max_comment extends rex_xform_validate_abstract 
{

	function enterObject(&$warning, $send, &$warning_messages)
	{
		if($send=="1")
		{
		
		
			// type, type_id, user_id, max_anzahl, zeitraum in sek ((60*60)*24)
		
			$type = $this->xaElements[3];
			$type_id = (int) $this->xaElements[4];
			$user_id = (int) $this->xaElements[5];
			$max_anzahl = (int) $this->xaElements[6];
			$zeitraum = (int) $this->xaElements[7];
		
			$g = new rex_sql;
			// $g->debugsql = 1;
			$g->setQuery('select count(id) from rex_com_comment where type="'.$type.'" and type_id='.$type_id.' and user_id='.$user_id.' and create_datetime>'.(time()-$zeitraum));
			
			if($g->getValue('count(id)')>($max_anzahl-1))
			{
				$warning["el_" . $id_2] = $this->params["error_class"];
				$warning_messages[] = $this->xaElements[8];
			}
		
			return;
		
			$field_1 = $this->xaElements[2];
			$field_2 = $this->xaElements[3];
			foreach($this->Objects as $o)
			{
				if ($o->getDatabasefieldname() == $field_1)
				{
					$id_1 = $o->getId(); 
					$value_1 = $o->getValue();
				}
				if ($o->getDatabasefieldname() == $field_2)
				{
					$id_2 = $o->getId(); 
					$value_2 = $o->getValue();
				}
			}
			if ($value_1 != $value_2)
			{
				$warning["el_" . $id_1] = $this->params["error_class"];
				$warning["el_" . $id_2] = $this->params["error_class"];
				$warning_messages[] = $this->xaElements[4];
			}
		}
	}
	
	function getDescription()
	{
		return "sunity_max_comment -> prüft ob leer, beispiel: validate|sunity_max_comment|type|type_id|user_id|max|zeitrauminsekunden|Fehlertext";
	}
}

?>