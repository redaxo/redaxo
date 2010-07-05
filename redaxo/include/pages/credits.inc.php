<?php

/**
 * Creditsseite. Auflistung der Credits an die Entwickler von REDAXO und den AddOns.
 * @package redaxo4
 * @version svn:$Id$
 */

rex_title($I18N->msg("credits"), "");

include_once $REX['INCLUDE_PATH']."/functions/function_rex_other.inc.php";
include_once $REX['INCLUDE_PATH']."/functions/function_rex_addons.inc.php";

?>
<div class="rex-area rex-mab-10">
	<h3 class="rex-hl2">REDAXO <?php echo $REX['VERSION'].'.'.$REX['SUBVERSION'].'.'.$REX['MINORVERSION'] ?></h3>

	<div class="rex-area-content">

	<p class="rex-tx1">
		<b>Jan Kristinus</b>, jan.kristinus@redaxo.de<br />
		Erfinder und Kernentwickler<br />
		Yakamara Media GmbH &amp; Co KG, <a href="http://www.yakamara.de" onclick="window.open(this.href); return false;">www.yakamara.de</a>
	</p>

	<p class="rex-tx1">
		<b>Markus Staab</b>, markus.staab@redaxo.de<br />
		Kernentwickler<br />
    REDAXO, <a href="http://www.redaxo.de" onclick="window.open(this.href); return false;">www.redaxo.de</a>
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
</div>


<div class="rex-area">

		<table class="rex-table"  summary="<?php echo $I18N->msg("credits_summary") ?>">
      <caption><?php echo $I18N->msg("credits_caption"); ?></caption>
			<thead>
			<tr>
				<th><?php echo $I18N->msg("credits_name"); ?></th>
				<th><?php echo $I18N->msg("credits_version"); ?></th>
				<th><?php echo $I18N->msg("credits_author"); ?></th>
				<th><?php echo $I18N->msg("credits_supportpage"); ?></th>
			</tr>
			</thead>

			<tbody>

		<?php

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
    	if (!$isActive) $author        = $I18N->msg('credits_addon_inactive');
    	if ($supportPage) $supportPage = '<a href="http://'.$supportPage.'" onclick="window.open(this.href); return false;">'. $supportPage .'</a>';
    	
    	echo '
    	<tr class="rex-addon">
    	  <td class="rex-col-a"><span class="'.$cl.'">'.htmlspecialchars($addon).'</span> [<a href="index.php?page=addon&amp;subpage=help&amp;addonname='.$addon.'">?</a>]</td>
    	  <td class="rex-col-b '.$cl.'">'. $version .'</td>
    	  <td class="rex-col-c'.$cl.'">'. $author .'</td>
    	  <td class="rex-col-d'.$cl.'">'. $supportPage .'</td>
  	  </tr>';
    	
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
          if (!$isActive) $author        = $I18N->msg('credits_addon_inactive');
          if ($supportPage) $supportPage = '<a href="http://'.$supportPage.'" onclick="window.open(this.href); return false;">'. $supportPage .'</a>';
          
          echo '
          <tr class="rex-plugin">
            <td class="rex-col-a"><span class="'.$cl.'">'.htmlspecialchars($plugin).'</span> [<a href="index.php?page=addon&amp;subpage=help&amp;addonname='.$addon.'&amp;pluginname='.$plugin.'">?</a>]</td>
            <td class="rex-col-b '.$cl.'">'. $version .'</td>
            <td class="rex-col-c '.$cl.'">'. $author .'</td>
            <td class="rex-col-d '.$cl.'">'. $supportPage .'</td>
          </tr>';
        }
    	}
    }
    
  	?>
    		</tbody>
    	</table>
</div>