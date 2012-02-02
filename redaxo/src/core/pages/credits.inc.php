<?php

/**
 * Creditsseite. Auflistung der Credits an die Entwickler von REDAXO und den AddOns.
 * @package redaxo5
 */

$addons = array();
foreach (rex_addon::getRegisteredAddons() as $addon)
{
  $isActive    = $addon->isActivated();
  $version     = $addon->getVersion();
  $author      = $addon->getAuthor();
  $supportPage = $addon->getSupportPage();

  if ($isActive)
  {
    $cl = 'rex-active';
  }
  else
  {
    $cl = 'rex-inactive';
  }

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

      if ($isActive)
      {
        $cl = 'rex-active';
      }
      else
      {
        $cl = 'rex-inactive';
      }

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

echo rex_view::title(rex_i18n::msg("credits"), "");

$content_1 = '';
$content_2 = '';

$content_1 .= '
  <h2>REDAXO</h2>
  
  <h3>Jan Kristinus <span>jan.kristinus@redaxo.org</span></h3>
  <p>
    '. rex_i18n::msg('credits_inventor') .' &amp '. rex_i18n::msg('credits_developer') .'<br />
    Yakamara Media GmbH &amp; Co KG, <a href="http://www.yakamara.de" onclick="window.open(this.href); return false;">www.yakamara.de</a>
  </p>

  <h3>Markus Staab <span>markus.staab@redaxo.org</span></h3>
  <p>'. rex_i18n::msg('credits_developer') .'<br />
    REDAXO, <a href="http://www.redaxo.org" onclick="window.open(this.href); return false;">www.redaxo.org</a>
  </p>

  <h3>Gregor Harlan <span>gregor.harlan@redaxo.org</span></h3>
  <p>'. rex_i18n::msg('credits_developer') .'<br />
    meyerharlan, <a href="http://meyerharlan.de" onclick="window.open(this.href); return false;">www.meyerharlan.de</a>
  </p>';

$content_2 .= '
  <h2>'. rex::getVersion() .'</h2>

  <h3>Ralph Zumkeller <span>info@redaxo.org</span></h3>
  <p>'. rex_i18n::msg('credits_designer') .'<br />
    Yakamara Media GmbH &amp; Co KG, <a href="http://www.yakamara.de" onclick="window.open(this.href); return false;">www.yakamara.de</a>
  </p>

  <h3>Thomas Blum <span>thomas.blum@redaxo.org</span></h3>
  <p>HTML/CSS<br />
    blumbeet - web.studio, <a href="http://www.blumbeet.com" onclick="window.open(this.href); return false;">www.blumbeet.com</a>
  </p>';


echo rex_view::contentBlock($content_1, $content_2);


$content = '';

$content .= '

  <table id="rex-table-credits-addons" class="rex-table" summary="'. rex_i18n::msg("credits_summary") .'">
    <caption>'. rex_i18n::msg("credits_caption") .'</caption>
    <thead>
    <tr>
      <th class="rex-name">'. rex_i18n::msg("credits_name") .'</th>
      <th class="rex-version">'. rex_i18n::msg("credits_version") .'</th>
      <th class="rex-author">'. rex_i18n::msg("credits_author") .'</th>
      <th class="rex-support">'. rex_i18n::msg("credits_supportpage") .'</th>
    </tr>
    </thead>

    <tbody>';

    foreach($addons as $addon)
    {
      $content .= '
      <tr class="rex-addon">
        <td class="rex-name"><span class="'. $addon->class .'">'. $addon->name .'</span> [<a href="index.php?page=addon&amp;subpage=help&amp;addonname='. $addon->name .'">?</a>]</td>
        <td class="rex-version '. $addon->class .'">'. $addon->version .'</td>
        <td class="rex-author '. $addon->class .'">'. $addon->author .'</td>
        <td class="rex-support '. $addon->class .'">';

        if ($addon->supportpage)
        {
          $content .= '<a href="http://'. $addon->supportpage .'" onclick="window.open(this.href); return false;">'. $addon->supportpage .'</a>';
        }

      $content .= '
        </td>
      </tr>';

      foreach($addon->plugins as $plugin)
      {
        $content .= '
        <tr class="rex-plugin">
          <td class="rex-name"><span class="'. $plugin->class .'">'. $plugin->name .'</span> [<a href="index.php?page=addon&amp;subpage=help&amp;addonname='. $addon->name .'&amp;pluginname='. $plugin->name .'">?</a>]</td>
          <td class="rex-version '. $plugin->class .'">'. $plugin->version .'</td>
          <td class="rex-author '. $plugin->class .'">'. $plugin->author .'</td>
          <td class="rex-support '. $plugin->class .'">';

          if ($plugin->supportpage)
          {
            $content .= '<a href="http://'. $plugin->supportpage .'" onclick="window.open(this.href); return false;">'. $plugin->supportpage .'</a>';
          }
          
        $content .= '
          </td>
        </tr>';
      }
    }
    $content .= '
    </tbody>
  </table>';



echo rex_view::contentBlock($content, '', 'block');