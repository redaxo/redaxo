<?php

// Url Pfad erstellen
define('A724_URL_TABLE_PATHLIST', $REX['INCLUDE_PATH'].'/generated/files/url_table_pathlist.php');
if (!function_exists('a724_generatePathnamesFromTable'))
{
	function a724_generatePathnamesFromTable($params)
	{
		$debug = false;
		$sql = new rex_sql();
		$results = $sql->getArray('SELECT article_id, url_table, url_table_parameters FROM rex_a724_frau_schultze WHERE url_table != "" AND url_table_parameters != ""');
		
		$URLPATH = array();
		if ($sql->getRows() >= 1)
		{
				
			a724_deletePathnamesFromTable();
			
			foreach ($results as $result)
			{
				if(is_array($result) && count($result) > 0)
				{
					$path = rex_getUrl($result['article_id']).'/';
					$path = str_replace('.html', '', $path);
					
					$table = $result['url_table'];
					$params = unserialize($result['url_table_parameters']);
					
					$col_name = $params[$table][$table."_name"];
					$col_id = $params[$table][$table."_id"];

					// Daten zum Aufbau der Urls holen
					$sqlu = new rex_sql();
					$sqlu->setDebug($debug);
					$res = $sqlu->getArray('SELECT '.$col_name.' AS name, '.$col_id.' AS id FROM '.$table);
					if ($sqlu->getRows() >= 1)
					{
						// Urls in die Datenbank schreiben
						$sqli = new rex_sql();
						$sqli->setDebug($debug);
						foreach ($res as $re)
						{
							$table_path = $path.strtolower(rex_parse_article_name($re['name'])).'.html';
							$table_id = $re['id'];
							
							$URLPATH[$result['url_table']][$table_id] = $table_path;
							
							
							$sqli->setTable('rex_a724_frau_schultze');
							$sqli->setValue('article_id', $result['article_id']);
							$sqli->setValue('status', '1');
							$sqli->setValue('url_table', $result['url_table']);
							$sqli->setValue('name', $table_path);
							$sqli->insert();
						}
					}
				}
			}
		}
		
		rex_put_file_contents(A724_URL_TABLE_PATHLIST, "<?php\n\$URLPATH = ". var_export($URLPATH, true) .";\n");

	}
}


// Alle Urls der Tabelle loeschen
if (!function_exists('a724_deletePathnamesFromTable'))
{
	function a724_deletePathnamesFromTable()
	{
		$debug = false;
		// Alle Urls der Tabelle loeschen
		$sqld = new rex_sql();
		$sqld->setDebug($debug);
		$sqld->setTable('rex_a724_frau_schultze');
		$sqld->setWhere('name != "" AND url_table != "" AND url_table_parameters = ""');
		$sqld->delete();
	}
}


// Domains umleiten
if (!function_exists('a724_frau_schultze'))
{
	function a724_frau_schultze()
	{
		global $REX, $REXPATH;
		
		// Url konnte nicht Ueber die Fullnames aufgeloest werden
		$return = array();

		$tmp = parse_url($_SERVER['REQUEST_URI']);	// Gesamt Information des Requests
		$myurl = $tmp['path'];											// Pfad der URL

		if (substr($myurl, 0, 1) == '/')
			$myurl = substr($myurl, 1);

		$my_sql = new rex_sql();
//		$my_sql->debugsql = true;
		$my_sql->setQuery('SELECT * FROM rex_a724_frau_schultze WHERE name=\''.mysql_real_escape_string($myurl).'\' AND status = 1');
		if ($my_sql->getRows() >=1)
		{
			// Es wurde eine interne Artikel ID und Sprache angegeben
			if (intval($my_sql->getValue('article_id')) > 0 && $my_sql->getValue('redirect') == 1)
			{
				// REXPATH wird auch im Backend benoetigt, z.B. beim bearbeiten von Artikeln
				$pathlist = $REX['INCLUDE_PATH'].'/generated/files/pathlist.php';
				if (file_exists($pathlist))
				{
					require_once ($pathlist);
				}
				
				$article_id = intval($my_sql->getValue('article_id'));
				$clang = intval($my_sql->getValue('clang'));

				$httptype = '303';
				if (trim($my_sql->getValue('type')) != '')
					$httptype = trim($my_sql->getValue('type')) ;
				
				if (isset($REXPATH[$article_id][$clang]))
				{
					$mvPath = '/'.$REXPATH[$article_id][$clang];
									
					$mvHost = $_SERVER['HTTP_HOST'];
					$mvScheme = 'http';
					if ($_SERVER['SERVER_PORT'] == 443)
					{
						$mvScheme = 'https';
					}
					
					if (trim($mvHost) != '')
					{
						$urlCpl = $mvScheme.'://'.$mvHost.$mvPath;
/*
						header('HTTP/1.1 303 See other');
						header('Location: '.trim($urlCpl));
*/						
						header('Location: '.trim($urlCpl), TRUE, $httptype);
						header('Content-Type: text/html');
						echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>HOSTNAME :: Moved Permanently</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
</head>
<body>
<img src="logo.gif" />
<p style="font-size:0.9em;padding-left:15px;">This page has moved to <a href="'.trim($urlCpl).'">'.str_replace('&','&amp;',trim($urlCpl)).'</a>.</p>
</body>
</html>
';
	
						exit();
					}
				}
			}
			elseif (intval($my_sql->getValue('article_id')) > 0)
			{
				$return['article_id'] = intval($my_sql->getValue('article_id'));
				$return['clang'] = intval($my_sql->getValue('clang'));
				return $return;
			}
			// Umleitung auf einen festen Namen, es ist auch ein externer Host moeglich
			elseif (trim($my_sql->getValue('url')) != '')
			{
				// URL auslesen
				$tmpURL = $my_sql->getValue('url');
				$tmpURL = parse_url($tmpURL);
				$mvHost = $_SERVER['HTTP_HOST'];
				$mvQuery = '';
				$mvScheme = 'http';
				if ($_SERVER['SERVER_PORT'] == 443)
				{
					$mvScheme = 'https';
				}
				$mvPath = '';
				if (isset($tmpURL['path']) && trim($tmpURL['path']) != '') { $mvPath = trim($tmpURL['path']);}
				if (isset($tmpURL['scheme']) && trim($tmpURL['scheme']) != '') { $mvScheme = trim($tmpURL['scheme']);}
				if (isset($tmpURL['host']) && trim($tmpURL['host']) != '') { $mvHost = trim($tmpURL['host']);}
				if (isset($tmpURL['query']) && trim($tmpURL['query']) != '') { $mvQuery = '?'.trim($tmpURL['query']);}
		

				$httptype = '303';
				if (trim($my_sql->getValue('type')) != '')
					$httptype = trim($my_sql->getValue('type')) ;
					
				if (trim($mvHost) != '')
				{
					$urlCpl = $mvScheme.'://'.$mvHost.$mvPath.$mvQuery;
/*
						header('HTTP/1.1 303 See other');
						header('Location: '.trim($urlCpl));
*/						
						header('Location: '.trim($urlCpl), TRUE, $httptype);
						header('Content-Type: text/html');
					header('Content-Type: text/html');
					echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>HOSTNAME :: see other</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
</head>
<body>
<img src="logo.gif" />
<p style="font-size:0.9em;padding-left:15px;">This page has moved to <a href="'.trim($urlCpl).'">'.str_replace('&','&amp;',trim($urlCpl)).'</a>.</p>
</body>
</html>
';

      		exit();
      	}
			}
		}
	}
}