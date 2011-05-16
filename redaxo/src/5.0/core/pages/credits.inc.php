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

echo '
<div class="rex-area rex-mab-10">
  <h3 class="rex-hl2">REDAXO '. rex::getProperty('version').'.'.rex::getProperty('subversion').'.'.rex::getProperty('minorversion') .'</h3>

  <div class="rex-area-content">

  <p class="rex-tx1">
    <b>Jan Kristinus</b>, jan.kristinus@redaxo.de<br />
    Erfinder und Kernentwickler<br />
    Yakamara Media GmbH &amp; Co KG, <a href="http://www.yakamara.de" onclick="window.open(this.href); return false;">www.yakamara.de</a>
  </p>

  <p class="rex-tx1">
    <b>Markus Staab</b>, markus.staab@redaxo.de<br />
    Kernentwickler<br />
    REDAXO, <a href="http://www.redaxo.org" onclick="window.open(this.href); return false;">www.redaxo.org</a>
  </p>

  <p class="rex-tx1">
    <b>Gregor Harlan</b>, gregor.harlan@redaxo.de<br />
    Kernentwickler<br />
    meyerharlan, <a href="http://meyerharlan.de" onclick="window.open(this.href); return false;">www.meyerharlan.de</a>
  </p>

  <p class="rex-tx1">
    <b>Thomas Blum</b>, thomas.blum@redaxo.de<br />
    Layout/Design Entwickler<br />
    blumbeet - web.studio, <a href="http://www.blumbeet.com" onclick="window.open(this.href); return false;">www.blumbeet.com</a>
  </p>
  </div>
</div>';


echo '
<div class="rex-area">

  <table class="rex-table"  summary="'. rex_i18n::msg("credits_summary") .'">
    <caption>'. rex_i18n::msg("credits_caption") .'</caption>
    <thead>
    <tr>
      <th>'. rex_i18n::msg("credits_name") .'</th>
      <th>'. rex_i18n::msg("credits_version") .'</th>
      <th>'. rex_i18n::msg("credits_author") .'</th>
      <th>'. rex_i18n::msg("credits_supportpage") .'</th>
    </tr>
    </thead>

    <tbody>';

    foreach($addons as $addon)
    {
      echo '
      <tr class="rex-addon">
        <td class="rex-col-a"><span class="'. $addon->class .'">'. $addon->name .'</span> [<a href="index.php?page=addon&amp;subpage=help&amp;addonname='. $addon->name .'">?</a>]</td>
        <td class="rex-col-b '. $addon->class .'">'. $addon->version .'</td>
        <td class="rex-col-c '. $addon->class .'">'. $addon->author .'</td>
        <td class="rex-col-d '. $addon->class .'">';

        if ($addon->supportpage)
        {
          echo '<a href="http://'. $addon->supportpage .'" onclick="window.open(this.href); return false;">'. $addon->supportpage .'</a>';
        }

      echo '
        </td>
      </tr>';

      foreach($addon->plugins as $plugin)
      {
        echo '
        <tr class="rex-plugin">
          <td class="rex-col-a"><span class="'. $plugin->class .'">'. $plugin->name .'</span> [<a href="index.php?page=addon&amp;subpage=help&amp;addonname='. $addon->name .'&amp;pluginname='. $plugin->name .'">?</a>]</td>
          <td class="rex-col-b '. $plugin->class .'">'. $plugin->version .'</td>
          <td class="rex-col-c '. $plugin->class .'">'. $plugin->author .'</td>
          <td class="rex-col-d '. $plugin->class .'">';

          if ($plugin->supportpage)
          {
            echo '<a href="http://'. $plugin->supportpage .'" onclick="window.open(this.href); return false;">'. $plugin->supportpage .'</a>';
          }
        echo '
          </td>
        </tr>';
      }
    }
    echo '
    </tbody>
  </table>
</div>';
