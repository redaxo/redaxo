<?php

/**
 * Creditsseite. Auflistung der Credits an die Entwickler von REDAXO und den AddOns.
 * @package redaxo5
 * @version svn:$Id$
 */

$addons = array();
foreach (rex_addon::getRegisteredAddons() as $addon)
{
  $isActive    = $addon->isActivated();
  $version     = $addon->getVersion();
  $author      = $addon->getAuthor();
  $supportPage = $addon->getSupportPage();

  if ($isActive) $cl = 'rex-clr-grn';
  else $cl = 'rex-clr-red';

  if ($version)   $version       = '['.$version.']';
  if ($author)    $author        = htmlspecialchars($author);
  if (!$isActive) $author        = rex_i18n::msg('credits_addon_inactive');

  $rex_ooAddon =  new stdClass();
  $rex_ooAddon->name = $addon->getName();
  $rex_ooAddon->version = $version;
  $rex_ooAddon->author = $author;
  $rex_ooAddon->supportpage = $supportPage;
  $rex_ooAddon->class = $cl;

  $plugins = array();
  if($isActive)
  {
    foreach($addon->getAvailablePlugins() as $plugin)
    {
      $isActive    = $plugin->isActivated();
      $version     = $plugin->getVersion();
      $author      = $plugin->getAuthor();
      $supportPage = $plugin->getSupportPage();

      if ($isActive) $cl = 'rex-clr-grn';
      else $cl = 'rex-clr-red';

      if ($version)   $version       = '['.$version.']';
      if ($author)    $author        = htmlspecialchars($author);
      if (!$isActive) $author        = rex_i18n::msg('credits_addon_inactive');

      $rex_ooPlugin =  new stdClass();
      $rex_ooPlugin->name = $plugin->getName() ;
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
