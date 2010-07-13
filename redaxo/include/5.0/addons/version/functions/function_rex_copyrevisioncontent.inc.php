<?php

/**
 * Version
 *
 * @author jan@kristinus.de
 *
 * @package redaxo4
 * @version svn:$Id$
 */

if(!function_exists("rex_copyRevisionContent"))
{
	function rex_copyRevisionContent($article_id,$clang,$from_revision_id, $to_revision_id, $from_re_sliceid = 0, $to_revision_delete = FALSE)
	{
	  global $REX;
	
	  if($to_revision_delete)
	  {
	  	$dc = rex_sql::factory();
	    // $dc->debugsql = 1;
	  	$dc->setQuery('delete from '.$REX['TABLE_PREFIX'].'article_slice where article_id='.$article_id.' and clang='.$clang.' and revision='.$to_revision_id);
	  }
	
	  if ($from_revision_id == $to_revision_id)
	    return false;
	  $gc = rex_sql::factory();
	  // $gc->debugsql = 1;
	  $gc->setQuery("select * from ".$REX['TABLE_PREFIX']."article_slice where re_article_slice_id='$from_re_sliceid' and article_id='$article_id' and clang='$clang' and revision='$from_revision_id'");
	  if ($gc->getRows() == 1)
	  {
	    // letzt slice_id des ziels holen ..
	    $glid = rex_sql::factory();
	    // $glid->debugsql = 1;
	    $glid->setQuery("
					select 
						r1.id, r1.re_article_slice_id
	        from 
						".$REX['TABLE_PREFIX']."article_slice as r1
					left join ".$REX['TABLE_PREFIX']."article_slice as r2 on r1.id = r2.re_article_slice_id
	        where 
						r1.article_id = $article_id and r1.clang = $clang and 
						r2.id is NULL and 
						r1.revision='$to_revision_id';");
	    if ($glid->getRows() == 1)
	      $to_last_slice_id = $glid->getValue("r1.id");
	    else
	      $to_last_slice_id = 0;
	    $ins = rex_sql::factory();
	    // $ins->debugsql = 1;
	    $ins->setTable($REX['TABLE_PREFIX']."article_slice");
	    $cols = rex_sql::factory();
	    $cols->setquery("SHOW COLUMNS FROM ".$REX['TABLE_PREFIX']."article_slice");
	    for ($j = 0; $j < $cols->rows; $j ++, $cols->next())
	    {
	      $colname = $cols->getValue("Field");
	      if ($colname == "re_article_slice_id") $value = $to_last_slice_id;
	      elseif ($colname == "revision") $value = $to_revision_id;
	      elseif ($colname == "createdate") $value = time();
	      elseif ($colname == "updatedate") $value = time();
	      elseif ($colname == "createuser") $value = $REX["USER"]->getValue("login");
	      elseif ($colname == "updateuser") $value = $REX["USER"]->getValue("login");
	      else
	        $value = $gc->getValue($colname);
	      if ($colname != "id")
	        $ins->setValue($colname, $ins->escape($value));
	    }
	    $ins->insert();
	    // id holen und als re setzen und weitermachen..
	    rex_copyRevisionContent($article_id,$clang,$from_revision_id, $to_revision_id, $gc->getValue("id"));
	    return true;
	  }
	  rex_generateArticle($article_id);
	  return true;
	}
}