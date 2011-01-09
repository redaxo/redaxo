<?php
function rex_com_formatter($val = '',$type = 'date')
{
	$return = $val;
	if ($type == 'date')
	{
		$return = date('d.m.Y',$val);
	
	}elseif ($type == 'datetime')
	{
		$return = date('d.m.Y H:i',$val).'h';
	}
	return $return;
}
?>