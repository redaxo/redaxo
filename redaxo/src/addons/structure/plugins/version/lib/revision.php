<?php

class rex_article_revision
{
  const
    LIVE = 0, // live revision
    WORK = 1; // working copy

  static public function copyContent($article_id, $clang, $from_revision_id, $to_revision_id)
  {
    if ($from_revision_id == $to_revision_id)
      return false;

    // clear the revision to which we will later copy all slices
    $dc = rex_sql::factory();
    // $dc->debugsql = 1;
    $dc->setQuery('delete from '.rex::getTablePrefix().'article_slice where article_id='.$article_id.' and clang='.$clang.' and revision='.$to_revision_id);

    $gc = rex_sql::factory();
    $gc->setQuery("select * from ".rex::getTablePrefix()."article_slice where article_id='$article_id' and clang='$clang' and revision='$from_revision_id' ORDER by ctype, prior");

    $cols = rex_sql::factory();
    $cols->setquery("SHOW COLUMNS FROM ".rex::getTablePrefix()."article_slice");
    foreach($gc as $slice)
    {
      $ins = rex_sql::factory();
      // $ins->debugsql = 1;
      $ins->setTable(rex::getTablePrefix()."article_slice");

      foreach($cols as $col)
      {
        $colname = $col->getValue("Field");
        $ins->setValue($colname, $slice->getValue($colname));
      }

      $ins->setValue('id', 0); // trigger auto increment
      $ins->setValue('revision', $to_revision_id);
      $ins->addGlobalCreateFields();
      $ins->addGlobalUpdateFields();
      $ins->insert();
    }

    rex_article_cache::delete($article_id);
    return true;
  }
}
