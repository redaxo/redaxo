<?php

class rex_clang_service
{
  /**
   * Erstellt eine Clang
   *
   * @param $id   Id der Clang
   * @param $name Name der Clang
   *
   * @return TRUE bei Erfolg, sonst FALSE
   */
  static public function addCLang($id, $name)
  {
    global $REX;

    if(isset($REX['CLANG'][$id])) return FALSE;

    $REX['CLANG'][$id] = $name;
    $file = rex_path::cache('clang.cache');
    rex_file::putCache($file, $REX['CLANG']);

    $firstLang = rex_sql::factory();
    $firstLang->setQuery("select * from ".rex::getTablePrefix()."article where clang='0'");
    $fields = $firstLang->getFieldnames();

    $newLang = rex_sql::factory();
    // $newLang->debugsql = 1;
    foreach($firstLang as $firstLangArt)
    {
      $newLang->setTable(rex::getTablePrefix()."article");

      foreach($fields as $key => $value)
      {
        if ($value == 'pid')
        echo ''; // nix passiert
        else
        if ($value == 'clang')
        $newLang->setValue('clang', $id);
        else
        if ($value == 'status')
        $newLang->setValue('status', '0'); // Alle neuen Artikel offline
        else
        $newLang->setValue($value, $firstLangArt->getValue($value));
      }

      $newLang->insert();
    }

    $newLang = rex_sql::factory();
    $newLang->setTable(rex::getTablePrefix()."clang");
    $newLang->setValue('id', $id);
    $newLang->setValue('name', $name);
    $newLang->insert();

    // ----- EXTENSION POINT
    rex_extension::registerPoint('CLANG_ADDED','',array ('id' => $id, 'name' => $name));

    return TRUE;
  }

  /**
   * Ändert eine Clang
   *
   * @param $id   Id der Clang
   * @param $name Name der Clang
   *
   * @return TRUE bei Erfolg, sonst FALSE
   */
  static public function editCLang($id, $name)
  {
    global $REX;

    if(!isset($REX['CLANG'][$id])) return false;

    $REX['CLANG'][$id] = $name;
    $file = rex_path::cache('clang.cache');
    rex_file::putCache($file, $REX['CLANG']);

    $editLang = rex_sql::factory();
    $editLang->setTable(rex::getTablePrefix()."clang");
    $editLang->setValue('id', $id);
    $editLang->setValue('name', $name);
    $editLang->update();

    // ----- EXTENSION POINT
    rex_extension::registerPoint('CLANG_UPDATED','',array ('id' => $id, 'name' => $name));

    return TRUE;
  }

  /**
   * Löscht eine Clang
   *
   * @param $id Zu löschende ClangId
   *
   * @return TRUE bei Erfolg, sonst FALSE
   */
  static public function deleteCLang($clang)
  {
    global $REX;

    if ($clang == 0 || !isset($REX['CLANG'][$clang]))
    return FALSE;

    $clangName = $REX['CLANG'][$clang];
    unset ($REX['CLANG'][$clang]);

    $del = rex_sql::factory();
    $del->setQuery("delete from ".rex::getTablePrefix()."article where clang='$clang'");
    $del->setQuery("delete from ".rex::getTablePrefix()."article_slice where clang='$clang'");
    $del->setQuery("delete from ".rex::getTablePrefix()."clang where id='$clang'");

    // ----- EXTENSION POINT
    rex_extension::registerPoint('CLANG_DELETED','',
    array (
      'id' => $clang,
      'name' => $clangName,
    )
    );

    rex_generateAll();

    return TRUE;
  }

  /**
   * Schreibt Spracheigenschaften in die Datei include/clang.inc.php
   *
   * @return TRUE bei Erfolg, sonst eine Fehlermeldung
   */
  static public function generateCache()
  {
    global $REX;

    $lg = rex_sql::factory();
    $lg->setQuery("select * from ".rex::getTablePrefix()."clang order by id");

    $REX['CLANG'] = array();
    foreach($lg as $lang)
    {
      $REX['CLANG'][$lang->getValue("id")] = $lang->getValue("name");
    }

    $file = rex_path::cache('clang.cache');
    if(rex_file::putCache($file, $REX['CLANG']) === FALSE)
    {
      return 'Datei "'.$file.'" hat keine Schreibrechte';
    }
    return TRUE;
  }
}