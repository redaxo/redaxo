<div class="rex-addon-output">

<h2>Boards</h2>

<div class="rex-addon-content">

<p>*********** Verwaltung fehlt noch *********** </p>

</div>

</div>


<?php

/*
$bb_boardname = rex_request("bb_boardname","string","");

echo "<table border=0 cellpadding=0 cellspacing=0 width=770 class=rex-table><tr><td class=grey><br>";

$boards = new rex_sql;
$boards->setQuery("select distinct board_id from rex_com_board");
	
if ($boards->getRows()>0)
{

	$currentboardname = "";
		
	echo "<table border=0 cellpadding=5 cellspacing=1 width=100%>";
	for ($i=0;$i<$boards->getRows();$i++)
	{
		$boardname = $boards->getValue("board_id");
		echo '<tr><td class=dgrey><b><a href="index.php?page=community&subpage=plugin.board&bb_boardname='.urlencode($boardname).'" class="black">'.$boardname.'</a></b></td></tr>';
		if ($bb_boardname == $boardname) $currentboardname = $boardname;
		$boards->next();
	}
	echo "</table><br>";
	
	if ($currentboardname!="")
	{
	
		class rex_com_board_admin extends rex_com_board{
		
			// ----- Link erstellen
			function getLink($extra_params = array())
			{
				$params = array_merge($this->addlink,$extra_params);
				return rex_getUrl($this->article_id,'',$params);
			}
		}
	
		$board = new rex_com_board_admin;
		$board->addLink("page","community");
		$board->addLink("subpage","plugin.board");
		$board->setBoardname($currentboardname);
		// $board->setUserjoin("rex_2_user on rex_5_board.user_id=rex_2_user.id","rex_2_user.login");
		$board->setAdmin();
		$board->setAnonymous(true);
		
		echo $board->showBoard();
	}

}else
{
	echo "&nbsp;&nbsp;Kein Board wurde eingetragen !<br>";	
}


echo "<br></td></tr></table>";
*/


?>