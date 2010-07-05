<?php

function rex_com_blaettern(&$gg, $aid=0, $params = array(), $anzahl = 10)
{

	$jump = rex_request("jump","int","0");
	if ($jump < 0) $jump = 0;


	// $gg->debugsql = 1;
	$g = $gg->getRows();

	$gg->setQuery($gg->query." LIMIT $jump,$anzahl");
	$l_anzahl = $gg->getRows();

	$lparams = $params;
	$lparams["jump"] = ($jump-$anzahl);

	if ($jump > 0)
	{
		// zurueck ist vorhanden
		$links = rex_getUrl($aid,0,$lparams);
	}else
	{
		// zurueck ist nicht vorhanden 
		$links = '#';
	}
		
	$bloecke = (int) (($g-1)/$anzahl)+1;
	$block = (int) $jump/$anzahl;
	$lastblock = (int) ($g/$anzahl);
	$i=0;
	$echo = array();
	for ($i=0;$i<$bloecke;$i++)
	{
		$echo[$i] = "";
		
		if ($i>0) $echo[$i] .=  ''; // <div class="Trenner">|</div>
		$zahl1 = ($i*$anzahl)+1;
		$zahl2 = ($i*$anzahl)+$anzahl;
		if ($i==$lastblock) $zahl2 = $g;
		if ($zahl2 == 0) $zahl2 = 1;

		$mparams = $params;
		$mparams["jump"] = ($i*$anzahl);

		if ($i!=$block) $echo[$i] .= '';  // <a href="'.rex_getUrl($aid,0,$mparams).'" class="Blaetternblock">
		else $echo[$i] .= ''; // <div class="Blaetternblock">
		
		$echo[$i] .= $zahl1.'-'.$zahl2;
		
		if ($i!=$block) $echo2 = $zahl1.'X'.$zahl2; // </a>
		else{
			$echo2 = $zahl1.'-'.$zahl2;
			$curblock = $i;
			$echo[$i] .= 'S'; // </div>
		}
	}
	
	$rparams = $params;
	$rparams["jump"] = ($jump+$anzahl);

	$rechts = "";
	if ($jump < ($g-$anzahl))
	{
		// weiter ist vorhanden
		$rechts .= rex_getUrl($aid,0,$rparams);
	}else 
	{
		// weiter ist nicht vorhanden
		$rechts .= '#';
	}
	
	$von = ($curblock*$anzahl)+1;
	$bis = ($curblock*$anzahl)+$anzahl;
	if ($bis>$g) $bis = $g;
	if ($g==0) $von = 0;
	
	
	$return = '
	<ul class="navi com-navi-paginate">
		<li class="com-navi-first"><a href="'.$links.'"><span>&laquo;</span></a></li>
		<li class="com-navi-other"><a>'.$von." - ".$bis.' von '.$g.'</a></li>
		<li class="com-navi-last"><a href="'.$rechts.'"><span>&raquo;</span></a></li>
	</ul>
	';
	
	return $return;

}
?>