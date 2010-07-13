<?php
	
	// Ist Modul schon vorhanden ?
	
	$searchtext = '$xform = new rex_xform';
	
	$gm = rex_sql::factory();
	$gm->setQuery('select * from '.$REX['TABLE_PREFIX'].'module where ausgabe LIKE "%'.$searchtext.'%"');
	
	$module_id = 0;
	$module_name = "";
	foreach($gm->getArray() as $module)
	{
		$module_id = $module["id"];
		$module_name = $module["name"];
	}
	
	if (isset($_REQUEST["install"]) && $_REQUEST["install"]==1)
	{
	
		$xform_module_name = "rex - X-Form";
	
		// Daten einlesen
		$in = rex_get_file_contents($REX["INCLUDE_PATH"]."/addons/xform/module/module_in.inc");
		$out = rex_get_file_contents($REX["INCLUDE_PATH"]."/addons/xform/module/module_out.inc");
	
		$mi = rex_sql::factory();
		// $mi->debugsql = 1;
		$mi->setTable("rex_module");
		$mi->setValue("eingabe",addslashes($in));
		$mi->setValue("ausgabe",addslashes($out));
	
		// altes Module aktualisieren
		if (isset($_REQUEST["module_id"]) && $module_id==$_REQUEST["module_id"])
		{
			$mi->setWhere('id="'.$module_id.'"');
			$mi->update();
			echo rex_info('Modul "'.$module_name.'" wurde aktualisiert');
		}else
		{
			$mi->setValue("name",$xform_module_name);
			$mi->insert();
			echo rex_info('XForm Modul wurde angelegt unter "'.$xform_module_name.'"');
		}
	
	}

?>

<div class="rex-addon-output">
	<h2 class="rex-hl2">Modul installieren</h2>
	<div class="rex-addon-content">
	<p>
	Um die XForm sinnvoll nutzen können, muß ein Modul erstellt werden, <br />mit welchem
	man die entsprechenden Formulare erstellen kann.
	</p>
	<ul>
		<li><a href="index.php?page=xform&amp;subpage=module&amp;install=1">Neues Modul mit der XForm installieren</a></li>
		<?php if ($module_id>0) 
			echo '<li><a href="index.php?page=xform&amp;subpage=module&amp;install=1&amp;module_id='.$module_id.'">Folgendes Modul aktualisieren "'.htmlspecialchars($module_name).'"</a></li>'; ?>
	</ul>	
	</div>
</div>
