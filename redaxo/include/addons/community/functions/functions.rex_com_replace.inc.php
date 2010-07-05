<?php

// replace funktion
// - ersetzt user felder

rex_register_extension('OUTPUT_FILTER', 'rex_com_replace');

function rex_com_replace($params)
{

	global $REX;
	$content = $params['subject'];

	if (isset($REX["COM_USER"]) && is_object($REX["COM_USER"]) && !$REX["REDAXO"])
	{

		$replace_array = array(
			"id" => $REX["COM_USER"]->getValue("id"),
			"login" => $REX["COM_USER"]->getValue("login"),
			"firstname" => $REX["COM_USER"]->getValue("firstname"),
			"name" => $REX["COM_USER"]->getValue("name"),
			"email" => $REX["COM_USER"]->getValue("email"),
		);
	
		foreach($replace_array as $search => $value)
		{
			$content = str_replace("###".$search."###", $value, $content);
		}
	
	}
	
	return $content;
	
}

function rex_formatter($value,$format="datetime",$type="datetime")
{
	$return = $value;

	if ($type=="timestamp")
	{
		$value = date("Y-m-d H:i:s",$value);
	}

	if ($format == "cent2euro")
	{
		$value = number_format(($value/100), 2, ',', '');;
		$return = $value." EUR";
	}elseif ($format == "datetime")
	{
		// 2000-24-12 13:12:11
		$y = substr($value,0,4);
		$m = substr($value,5,2);
		$d = substr($value,8,2);
		$return = "$d.$m.$y";
		$h = substr($value,11,2);
		$m = substr($value,14,2);
		$s = substr($value,17,2);
		if ($h != "" && $m != "") $return .= " $h:$m"."h";
	}

	return $return;
}




?>