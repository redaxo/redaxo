<?php

// ---------- BREADCRUMB

// Beginne in der Wurzelkategorie
// 1 Ebene Tief
// Nicht aufklappen (hier egal da nur 1 Ebene)
// Offline ausblenden 

$category_id = 0;
$includeCurrent = TRUE;

// navigation generator erstellen
$nav = rex_navigation::factory();

echo '<div id="breadcrumb">';
if ($REX['CUR_CLANG'] == 1)
{
  echo '<p>You are here:</p>'. $nav->getBreadcrumb('Startpage', $includeCurrent, $category_id);
}
else
{
  echo '<p>Sie befinden sich hier:</p>'. $nav->getBreadcrumb('Startseite', $includeCurrent, $category_id);
}
echo '</div>';
?>