<?php

class rex_content_service
{
  /**
   * Verschiebt einen Slice
   *
   * @param int    $slice_id  Id des Slices
   * @param int    $clang     Id der Sprache
   * @param string $direction Richtung in die verschoben werden soll
   *
   * @return string Eine Statusmeldung
   */
  static public function moveSlice($slice_id, $clang, $direction)
  {
    // ctype beachten
    // verschieben / vertauschen
    // article regenerieren.

    // check if slice id is valid
    $CM = rex_sql::factory();
    $CM->setQuery('select * from ' . rex::getTablePrefix() . "article_slice where id='$slice_id' and clang=$clang");
    if ($CM->getRows() == 1) {
      // origin value for later success-check
      $oldPrior = $CM->getValue('prior');

      // prepare sql for later saving
      $upd = rex_sql::factory();
      $upd->setTable(rex::getTablePrefix() . 'article_slice');
      $upd->setWhere(array(
        'id' => $slice_id
      ));

      // some vars for later use
      $article_id = $CM->getValue('article_id');
      $ctype = $CM->getValue('ctype');
      $slice_revision = $CM->getValue('revision');

      if ($direction == 'moveup' || $direction == 'movedown') {
        if ($direction == 'moveup') {
          $upd->setValue('prior', $CM->getValue('prior') - 1);
          $updSort = 'DESC';
        } elseif ($direction == 'movedown') {
          $upd->setValue('prior', $CM->getValue('prior') + 1);
          $updSort = 'ASC';
        }
        $upd->addGlobalUpdateFields();
        $upd->update();

        rex_sql_util::organizePriorities(
          rex::getTable('article_slice'),
          'prior',
          'article_id=' . $article_id . ' AND clang=' . $clang . ' AND ctype=' . $ctype . ' AND revision=' . $slice_revision,
          'prior, updatedate ' . $updSort
        );

        // check if the slice moved at all (first cannot be moved up, last not down)
        $CM->setQuery('select * from ' . rex::getTablePrefix() . "article_slice where id='$slice_id' and clang=$clang");
        $newPrior = $CM->getValue('prior');
        if ($oldPrior == $newPrior) {
          throw new rex_api_exception(rex_i18n::msg('slice_moved_error'));
        }

        rex_article_cache::deleteContent($article_id, $clang);
      } else {
        throw new rex_exception('rex_moveSlice: Unsupported direction "' . $direction . '"!', E_USER_ERROR);
      }
    } else {
      throw new rex_api_exception(rex_i18n::msg('slice_moved_error'));
    }

    return rex_i18n::msg('slice_moved');
  }

  /**
   * Löscht einen Slice
   *
   * @param int $slice_id Id des Slices
   *
   * @return boolean TRUE bei Erfolg, sonst FALSE
   */
  static public function deleteSlice($slice_id)
  {
    // check if slice id is valid
    $curr = rex_sql::factory();
    $curr->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article_slice WHERE id=' . $slice_id);
    if ($curr->getRows() != 1) {
      return false;
    }

    // delete the slice
    $del = rex_sql::factory();
    $del->setQuery('DELETE FROM ' . rex::getTablePrefix() . 'article_slice WHERE id=' . $slice_id);

    // reorg remaining slices
    rex_sql_util::organizePriorities(
      rex::getTable('article_slice'),
      'prior',
      'article_id=' . $curr->getValue('article_id') . ' AND clang=' . $curr->getValue('clang') . ' AND ctype=' . $curr->getValue('ctype') . ' AND revision=' . $curr->getValue('revision'),
      'prior'
    );

    // check if delete was successfull
    return $curr->getRows() == 1;
  }

  /**
   * Kopiert die Inhalte eines Artikels in einen anderen Artikel
   *
   * @param int $from_id ArtikelId des Artikels, aus dem kopiert werden (Quell ArtikelId)
   * @param int $to_id   ArtikelId des Artikel, in den kopiert werden sollen (Ziel ArtikelId)
   * @param int [$from_clang]      ClangId des Artikels, aus dem kopiert werden soll (Quell ClangId)
   * @param int [$to_clang]        ClangId des Artikels, in den kopiert werden soll (Ziel ClangId)
   * @param int [$from_re_sliceid] Id des Slices, bei dem begonnen werden soll
   *
   * @return boolean TRUE bei Erfolg, sonst FALSE
   */
  static public function copyContent($from_id, $to_id, $from_clang = 0, $to_clang = 0, $from_re_sliceid = 0, $revision = 0)
  {
    if ($from_id == $to_id && $from_clang == $to_clang)
    return false;

    $gc = rex_sql::factory();
    $gc->setQuery('select * from ' . rex::getTablePrefix() . "article_slice where article_id='$from_id' and clang='$from_clang' and revision='$revision'");

    if ($gc->getRows() > 0) {
      $ins = rex_sql::factory();
      $ins->setTable(rex::getTablePrefix() . 'article_slice');
      $ctypes = array();

      $cols = rex_sql::factory();
      // $cols->debugsql = 1;
      $cols->setquery('SHOW COLUMNS FROM ' . rex::getTablePrefix() . 'article_slice');
      foreach ($gc as $slice) {
        foreach ($cols as $col) {
          $colname = $col->getValue('Field');
          if ($colname == 'clang') $value = $to_clang;
          elseif ($colname == 'article_id') $value = $to_id;
          else
          $value = $slice->getValue($colname);

          // collect all affected ctypes
          if ($colname == 'ctype')
          $ctypes[$value] = $value;

          if ($colname != 'id')
          $ins->setValue($colname, $value);
        }

        $ins->addGlobalUpdateFields();
        $ins->addGlobalCreateFields();
        $ins->insert();
      }

      foreach ($ctypes as $ctype) {
        // reorg slices
        rex_sql_util::organizePriorities(
          rex::getTable('article_slice'),
          'prior',
          'article_id=' . $to_id . ' AND clang=' . $to_clang . ' AND ctype=' . $ctype . ' AND revision=' . $revision,
          'prior, updatedate'
        );
      }

      rex_article_cache::deleteContent($to_id, $to_clang);
      return true;
    }

    return false;
  }

  /**
   * Generiert den Artikel-Cache des Artikelinhalts.
   *
   * @param $article_id Id des zu generierenden Artikels
   * @param [$clang ClangId des Artikels]
   *
   * @return TRUE bei Erfolg, FALSE wenn eine ungütlige article_id übergeben wird, sonst eine Fehlermeldung
   */
  static public function generateArticleContent($article_id, $clang = null)
  {
    foreach (rex_clang::getAllIds() as $_clang) {
      if ($clang !== null && $clang != $_clang)
      continue;

      $CONT = new rex_article_content_base;
      $CONT->setCLang($_clang);
      $CONT->setEval(false); // Content nicht ausführen, damit in Cachedatei gespeichert werden kann
      if (!$CONT->setArticleId($article_id)) return false;

      // --------------------------------------------------- Artikelcontent speichern
      $article_content_file = rex_path::addonCache('structure', "$article_id.$_clang.content");
      $article_content = $CONT->getArticle();

      // ----- EXTENSION POINT
      $article_content = rex_extension::registerPoint('GENERATE_FILTER', $article_content,
      array(
          'id' => $article_id,
          'clang' => $_clang,
          'article' => $CONT
      )
      );

      if (rex_file::put($article_content_file, $article_content) === false) {
        return rex_i18n::msg('article_could_not_be_generated') . ' ' . rex_i18n::msg('check_rights_in_directory') . rex_path::addonCache('structure');
      }
    }

    return true;
  }
}
