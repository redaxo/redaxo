<?php

/**
 * Creditsseite. Auflistung der Credits an die Entwickler von REDAXO und den AddOns.
 * @package redaxo5
 * @version svn:$Id$
 */

include_once rex_path::core('/functions/function_rex_other.inc.php');
include_once rex_path::core('/functions/function_rex_addons.inc.php');

$addons = array();
foreach (rex_ooAddon::getRegisteredAddons() as $addon)
{
  $isActive    = rex_ooAddon::isActivated($addon);
  $version     = rex_ooAddon::getVersion($addon);
  $author      = rex_ooAddon::getAuthor($addon);
  $supportPage = rex_ooAddon::getSupportPage($addon);

  if ($isActive) $cl = 'rex-clr-grn';
  else $cl = 'rex-clr-red';

  if ($version)   $version       = '['.$version.']';
  if ($author)    $author        = htmlspecialchars($author);
  if (!$isActive) $author        = rex_i18n::msg('credits_addon_inactive');

  $rex_ooAddon =  new stdClass();
  $rex_ooAddon->name = $addon;
  $rex_ooAddon->version = $version;
  $rex_ooAddon->author = $author;
  $rex_ooAddon->supportpage = $supportPage;
  $rex_ooAddon->class = $cl;

  $plugins = array();
  if($isActive)
  {
    foreach(rex_ooPlugin::getAvailablePlugins($addon) as $plugin)
    {
      $isActive    = rex_ooPlugin::isActivated($addon, $plugin);
      $version     = rex_ooPlugin::getVersion($addon, $plugin);
      $author      = rex_ooPlugin::getAuthor($addon, $plugin);
      $supportPage = rex_ooPlugin::getSupportPage($addon, $plugin);

      if ($isActive) $cl = 'rex-clr-grn';
      else $cl = 'rex-clr-red';

      if ($version)   $version       = '['.$version.']';
      if ($author)    $author        = htmlspecialchars($author);
      if (!$isActive) $author        = rex_i18n::msg('credits_addon_inactive');

      $rex_ooPlugin =  new stdClass();
      $rex_ooPlugin->name = $plugin ;
      $rex_ooPlugin->version = $version;
      $rex_ooPlugin->author = $author;
      $rex_ooPlugin->supportpage = $supportPage;
      $rex_ooPlugin->class = $cl;
      $plugins []= $rex_ooPlugin;
    }
  }

  $rex_ooAddon->plugins = $plugins;
  $addons[]=$rex_ooAddon;
  //  echo '
//      <tr class="rex-addon">
//        <td class="rex-col-a"><span class="'.$cl.'">'.htmlspecialchars($addon).'</span> [<a href="index.php?page=addon&amp;subpage=help&amp;addonname='.$addon.'">?</a>]</td>
//        <td class="rex-col-b '.$cl.'">'. $version .'</td>
//        <td class="rex-col-c'.$cl.'">'. $author .'</td>
//        <td class="rex-col-d'.$cl.'">'. $supportPage .'</td>
//      </tr>';

}

rex_title(rex_i18n::msg("credits"), "");

$coreCredits = new rex_fragment();
echo $coreCredits->parse('core_page_credits_core');
unset($coreCredits);

$addonCredits = new rex_fragment();
$addonCredits->setVar('addons', $addons);
echo $addonCredits->parse('core_page_credits_addons');
unset($addonCredits);
