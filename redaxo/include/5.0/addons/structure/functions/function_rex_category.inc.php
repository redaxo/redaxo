<?php

/**
 * Regelt die Rechte an den einzelnen Kategorien und gibt den Pfad aus
 * Kategorien = Startartikel und Bezüge
 * @package redaxo4
 * @version svn:$Id$
 */

$KATebene = 0; // aktuelle Ebene: default
$KATPATH = '|'; // Standard für path Eintragungen in DB
if (!isset($KATout)) $KATout = ''; // Variable definiert und vorbelegt wenn nicht existent

$KATPERM = false;
if ($REX['USER']->hasPerm('csw[0]') || $REX['USER']->isAdmin()) $KATPERM = true;

$KAT = rex_sql::factory();
// $KAT->debugsql = true;
$KAT->setQuery("SELECT * FROM ".$REX['TABLE_PREFIX']."article WHERE id=$category_id AND startpage=1 AND clang=$clang");

if ($KAT->getRows()!=1)
{
  // kategorie existiert nicht
  if($category_id != 0)
  {
    $category_id = 0;
    $article_id = 0;
  }
}
else
{
  // kategorie existiert

  $KPATH = explode('|',$KAT->getValue('path'));

  $KATebene = count($KPATH)-1;
  for ($ii=1;$ii<$KATebene;$ii++)
  {
    $SKAT = rex_sql::factory();
    $SKAT->setQuery('SELECT * FROM '. $REX['TABLE_PREFIX'] .'article WHERE id='. $KPATH[$ii] .' AND startpage=1 AND clang='. $clang);

    $catname = str_replace(' ', '&nbsp;', htmlspecialchars($SKAT->getValue('catname')));
    $catid = $SKAT->getValue('id');

    if ($SKAT->getRows()==1)
    {
      $KATPATH .= $KPATH[$ii]."|";
      if ($KATPERM || $REX['USER']->hasCategoryPerm($catid))
      {
        $KATout .= ' <li>: <a href="index.php?page=structure&amp;category_id='. $catid .'&amp;clang='. $clang .'"'. rex_tabindex() .'>'. $catname .'</a></li>';

        if($REX['USER']->hasPerm('csw['.$catid.']'))
        {
          $KATPERM = true;
        }
      }
    }
  }

  if ($KATPERM || $REX['USER']->hasPerm('csw['. $category_id .']') /*|| $REX['USER']->hasPerm('csr['. $category_id .']')*/)
  {
    $catname = str_replace(' ', '&nbsp;', htmlspecialchars($KAT->getValue('catname')));

    $KATout .= ' <li>: <a href="index.php?page=structure&amp;category_id='. $category_id .'&amp;clang='. $clang .'"'. rex_tabindex() .'>'. $catname .'</a></li>';
    $KATPATH .= $category_id .'|';

    if ($REX['USER']->hasPerm('csw['. $category_id .']'))
    {
      $KATPERM = true;
    }
  }
  else
  {
    $category_id = 0;
    $article_id = 0;
  }
}

$KATout = '
<!-- *** OUTPUT OF CATEGORY-TOOLBAR - START *** -->
  <ul id="rex-navi-path">
    <li>'.$I18N->msg('path').'</li>
    <li>: <a href="index.php?page=structure&amp;category_id=0&amp;clang='. $clang .'"'. rex_tabindex() .'>Homepage</a></li>
    '. $KATout .'
  </ul>
<!-- *** OUTPUT OF CATEGORY-TOOLBAR - END *** -->
';