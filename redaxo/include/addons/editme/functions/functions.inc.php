<?php

/*
 * ŸberprŸft, ob Feld vorhanden ist.
 *
 *
 */

function rex_em_checkField($l,$v,$p)
{
	global $REX;
	$q = 'select * from '.$REX['TABLE_PREFIX'].'em_field where table_name='.$p.' and '.$l.'="'.$v.'" LIMIT 1';
	$c = rex_sql::factory();
	// $c->debugsql = 1;
	$c->setQuery($q);
	if($c->getRows()>0)
	{
		// FALSE -> Warning = TRUE;
		return TRUE;
	}else
	{
		return FALSE;
	}
}

function rex_em_checkLabelInTable($l,$v,$p)
{
	global $REX;
	$q = 'select * from '.$REX['TABLE_PREFIX'].'em_table where '.$l.'="'.$v.'" LIMIT 1';
	$c = rex_sql::factory();
	// $c->debugsql = 1;
	$c->setQuery($q);
	if($c->getRows()>0)
	{
		// FALSE -> Warning = TRUE;
		return TRUE;
	}else
	{
		return FALSE;
	}
}



function rex_em_generateAll()
{
	global $REX;

	$types = rex_xform::getTypeArray();

	$tables = rex_em_getTables();

	foreach($tables as $table)
	{
		$name = $table['name'];
		$tablename = rex_em_getTableName($table['name']);
		$fields = rex_em_getFields($table['name']);
			
		// ********** Table schon vorhanden ?, wenn nein, dann anlegen
		$c = rex_sql::factory();
		// $c->debugsql = 1;
		$c->setQuery('CREATE TABLE IF NOT EXISTS `'.$tablename.'` ( `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY )');
			
		// Felder merken, erstellen und eventuell loeschen
		$c->setQuery('SHOW COLUMNS FROM `'.$tablename.'`');
		$saved_columns = $c->getArray();

		foreach($fields as $field)
		{
			$type_name = $field["type_name"];
			$type_id = $field["type_id"];

			if($type_id == "value")
			{
				$type_label = $field["f1"];
				$dbtype = $types[$type_id][$type_name]['dbtype'];

				if($dbtype != "none" && $dbtype != "")
				{
					// Column schon vorhanden ?
					$add_column = TRUE;
					foreach($saved_columns as $uu => $vv)
					{
						if ($vv["Field"] == $type_label)
						{
							$add_column = FALSE;
							unset($saved_columns[$uu]);
							break;
						}
					}

					// Column erstellen
					if($add_column)
					$c->setQuery('ALTER TABLE `'.$tablename.'` ADD `'.$type_label.'` '.$dbtype);
				}
					
			}

		}

		// Loeschen von Spalten ohne Zuweisung
		foreach($saved_columns as $uu => $vv)
		{
			if ($vv["Field"] != "id")
			{
				$c->setQuery('ALTER TABLE `'.$tablename.'` DROP `'.$vv["Field"].'` ');
			}
		}

	}
}

function rex_em_getTables()
{
	global $REX;
	$tb = rex_sql::factory();
	$tb->setQuery('select * from '.$REX['TABLE_PREFIX'].'em_table order by prio,name');
	return $tb->getArray();
}

/**
 * Returns the database tablename from the internal table name
 */
function rex_em_getTableName($internalTableName)
{
	global $REX;
	return $REX['TABLE_PREFIX'].'em_data_'.$internalTableName;
}

function rex_em_getFields($table_name)
{
	global $REX;
	$tb = rex_sql::factory();
	$tb->setQuery('select * from '.$REX['TABLE_PREFIX'].'em_field where table_name="'.$table_name.'" order by prio');
	return $tb->getArray();
}



?>