<?php

/**
 * Dient zur Ausgabe des Sprachen-blocks
 * @package redaxo5
 */
$num_clang = rex_clang::count();

$button = '';
$items  = array();
if ($num_clang > 1) {
  $i = 1;
  foreach (rex_clang::getAll() as $id => $_clang) {

    if (rex::getUser()->getComplexPerm('clang')->hasPerm($id)) {
      $item = array();
      $item['title']  = rex_i18n::translate($_clang->getName());
      $item['href']   = rex_url::currentBackendPage() . '&amp;clang=' . $id . $language_add . '&amp;ctype=' . $ctype;
      if ($id == $clang) {
        $item['active'] = true;

        $button = rex_i18n::translate($_clang->getName());
      }
      $items[] = $item;
    }
    $i++;
  }

  $fragment = new rex_fragment();
  $fragment->setVar('class', 'rex-language');
  $fragment->setVar('button', $button);
  $fragment->setVar('button_title', rex_i18n::msg('languages'));
  $fragment->setVar('header', rex_i18n::msg('clang_select'));
  $fragment->setVar('items', $items, false);
  $fragment->setVar('check', true);

  if (rex::getUser()->isAdmin())
    $fragment->setVar('footer', '<a href="' . rex_url::backendPage('system/lang') . '"><span class="rex-icon rex-icon-language"></span> ' . rex_i18n::msg('languages_edit') . '</a>', false);

  echo $fragment->parse('core/navigations/drop.tpl');

  unset($fragment);
}
