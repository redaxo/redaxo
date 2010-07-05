<?php

class rex_xform_action_com_comment_rank extends rex_xform_action_abstract
{

	function execute()
	{

		// typ auslesen
		// id auslesen
		// ip + zeit + user_id check machen
		// neuen rank setzen
		
		$type = $this->action["elements"][1];
		$type_id = $this->action["elements"][2];
		
		// echo "$type, $type_id";
		
		switch($type)
		{
			case("slice"):
				$u = 'update rex_article_slice set value20=###rank### where id='.$type_id;
				break;
			case("partner"):
				$u = 'update rex_s_partner set rank=###rank### where id='.$type_id;
				break;
			case("news"):
				$u = 'update rex_s_news set rank=###rank### where id='.$type_id;
				break;
			default:
				return FALSE;
		}

		// Rankinfos holen
		$g = 'select rank from rex_com_comment where type="'.$type.'" and type_id='.$type_id.' and rank>0';
		$gg = new rex_sql;
		// $gg->debugsql = 1;
		$gg->setQuery($g);
		$gr = $gg->getArray();

		// Durchschnitt ausrechnen
		$ranks = 0;
		foreach($gr as $v){ $ranks = $ranks + $v["rank"]; }
		$ds = (int) ($ranks / count($gr));
		
		// Rank aktualisieren
		$u = str_replace('###rank###',$ds,$u);
		$gg->setQuery($u);
		
		return TRUE;
		
	}

	function getDescription()
	{
		return "action|com_comment_rank|type[slice/partner/news]|type_id";
	}

}

?>