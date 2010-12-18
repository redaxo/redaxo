<?php

/**
 * Creditsseite. Auflistung der Credits an die Entwickler von REDAXO und den AddOns.
 * @package redaxo4
 * @version svn:$Id$
 */

include_once $REX['SRC_PATH'].'/core/functions/function_rex_other.inc.php';
include_once $REX['SRC_PATH'].'/core/functions/function_rex_addons.inc.php';

$addons = array();
foreach (OOAddon::getRegisteredAddons() as $addon)
{
  $isActive    = OOAddon::isActivated($addon);
  $version     = OOAddon::getVersion($addon);
  $author      = OOAddon::getAuthor($addon);
  $supportPage = OOAddon::getSupportPage($addon);

  if ($isActive) $cl = 'rex-clr-grn';
  else $cl = 'rex-clr-red';

  if ($version)   $version       = '['.$version.']';
  if ($author)    $author        = htmlspecialchars($author);
  if (!$isActive) $author        = $REX['I18N']->msg('credits_addon_inactive');
  
  $OOAddon =  new stdClass();
  $OOAddon->name = $addon;
  $OOAddon->version = $version;
  $OOAddon->author = $author;
  $OOAddon->supportpage = $supportPage;
  $OOAddon->class = $cl;

  $plugins = array();
  if($isActive)
  {
    foreach(OOPlugin::getAvailablePlugins($addon) as $plugin)
    {
      $isActive    = OOPlugin::isActivated($addon, $plugin);
      $version     = OOPlugin::getVersion($addon, $plugin);
      $author      = OOPlugin::getAuthor($addon, $plugin);
      $supportPage = OOPlugin::getSupportPage($addon, $plugin);

      if ($isActive) $cl = 'rex-clr-grn';
      else $cl = 'rex-clr-red';

      if ($version)   $version       = '['.$version.']';
      if ($author)    $author        = htmlspecialchars($author);
      if (!$isActive) $author        = $REX['I18N']->msg('credits_addon_inactive');

      $OOPlugin =  new stdClass();
      $OOPlugin->name = $plugin ;
      $OOPlugin->version = $version;
      $OOPlugin->author = $author;
      $OOPlugin->supportpage = $supportPage;
      $OOPlugin->class = $cl;
      $plugins []= $OOPlugin;
    }
  }
  
  $OOAddon->plugins = $plugins; 
  $addons[]=$OOAddon;
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
