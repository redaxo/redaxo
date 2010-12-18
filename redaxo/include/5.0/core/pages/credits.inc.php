<?php

/**
 * Creditsseite. Auflistung der Credits an die Entwickler von REDAXO und den AddOns.
 * @package redaxo4
 * @version svn:$Id$
 */

include_once $REX['SRC_PATH'].'/core/functions/function_rex_other.inc.php';
include_once $REX['SRC_PATH'].'/core/functions/function_rex_addons.inc.php';

$addons = array();
foreach (rex_ooaddon::getRegisteredAddons() as $addon)
{
  $isActive    = rex_ooaddon::isActivated($addon);
  $version     = rex_ooaddon::getVersion($addon);
  $author      = rex_ooaddon::getAuthor($addon);
  $supportPage = rex_ooaddon::getSupportPage($addon);

  if ($isActive) $cl = 'rex-clr-grn';
  else $cl = 'rex-clr-red';

  if ($version)   $version       = '['.$version.']';
  if ($author)    $author        = htmlspecialchars($author);
  if (!$isActive) $author        = $REX['I18N']->msg('credits_addon_inactive');
  
  $rex_ooaddon =  new stdClass();
  $rex_ooaddon->name = $addon;
  $rex_ooaddon->version = $version;
  $rex_ooaddon->author = $author;
  $rex_ooaddon->supportpage = $supportPage;
  $rex_ooaddon->class = $cl;

  $plugins = array();
  if($isActive)
  {
    foreach(rex_ooplugin::getAvailablePlugins($addon) as $plugin)
    {
      $isActive    = rex_ooplugin::isActivated($addon, $plugin);
      $version     = rex_ooplugin::getVersion($addon, $plugin);
      $author      = rex_ooplugin::getAuthor($addon, $plugin);
      $supportPage = rex_ooplugin::getSupportPage($addon, $plugin);

      if ($isActive) $cl = 'rex-clr-grn';
      else $cl = 'rex-clr-red';

      if ($version)   $version       = '['.$version.']';
      if ($author)    $author        = htmlspecialchars($author);
      if (!$isActive) $author        = $REX['I18N']->msg('credits_addon_inactive');

      $rex_ooplugin =  new stdClass();
      $rex_ooplugin->name = $plugin ;
      $rex_ooplugin->version = $version;
      $rex_ooplugin->author = $author;
      $rex_ooplugin->supportpage = $supportPage;
      $rex_ooplugin->class = $cl;
      $plugins []= $rex_ooplugin;
    }
  }
  
  $rex_ooaddon->plugins = $plugins; 
  $addons[]=$rex_ooaddon;
  //  echo '
//      <tr class="rex-addon">
//        <td class="rex-col-a"><span class="'.$cl.'">'.htmlspecialchars($addon).'</span> [<a href="index.php?page=addon&amp;subpage=help&amp;addonname='.$addon.'">?</a>]</td>
//        <td class="rex-col-b '.$cl.'">'. $version .'</td>
//        <td class="rex-col-c'.$cl.'">'. $author .'</td>
//        <td class="rex-col-d'.$cl.'">'. $supportPage .'</td>
//      </tr>';
  
}

rex_title($REX['I18N']->msg("credits"), "");

$coreCredits = new rex_fragment();
echo $coreCredits->parse('pages/credits/core');
unset($coreCredits);

$addonCredits = new rex_fragment();
$addonCredits->setVar('addons', $addons);
echo $addonCredits->parse('pages/credits/addons');
unset($addonCredits);
