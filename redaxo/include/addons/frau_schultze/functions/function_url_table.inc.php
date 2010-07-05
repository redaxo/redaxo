<?php



// Url zurueckgeben
if (!function_exists('a724_getTableUrl'))
{
	function a724_getTableUrl($table, $id)
	{
		if ($table == '')
			return false;
			
		if ((int)$id <= 0)
			return false;
		
		$params = array();
		$params['table'] = $table;
		$params['id'] = $id;
		
		if(file_exists(A724_URL_TABLE_PATHLIST))
		{
			require (A724_URL_TABLE_PATHLIST);
			
			foreach ($URLPATH as $table => $urls)
			{
				if ($table = $params['table'])
				{
					foreach ($urls as $id => $url)
					{
						if ($id == $params['id'])
						{
							return $url;
							break;
						}
					}
				}
			}
		}
	}
}


// Id zurueckgeben
if (!function_exists('a724_getTableId'))
{
	function a724_getTableId($table, $url)
	{
		if ($table == '')
			return false;
			
		if ($url == '')
			return false;
		
		$params['table'] = $table;
		$params['url'] = $url;
			
		if(file_exists(A724_URL_TABLE_PATHLIST))
		{
			require (A724_URL_TABLE_PATHLIST);
			
			foreach ($URLPATH as $table => $urls)
			{
				if ($table = $params['table'])
				{
					foreach ($urls as $id => $url)
					{
						if ($url == $params['url'])
						{
							return $id;
							break;
						}
					}
					break;	
				}
			}
		}
	}
}