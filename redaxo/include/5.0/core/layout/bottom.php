<?php

/**
 * Layout Fuß des Backends
 * @package redaxo4
 * @version svn:$Id$
 */

?>

  </div>
<!-- *** OUTPUT OF CONTENT - END *** -->

</div>
</div><?php /* END #rex-wrapper - nicht als HTML Kommentar setzen, sonst Bug im IE */ ?>

  <div id="rex-footer">
    <div id="rex-navi-footer">
      <ul><li class="rex-navi-first"><a href="#rex-header"<?php echo rex_tabindex() ?>>&#94;</a></li><li><a href="http://www.yakamara.de" onclick="window.open(this.href); return false;"<?php echo rex_tabindex() ?>>yakamara.de</a></li><li><a href="http://www.redaxo.de" onclick="window.open(this.href); return false;"<?php echo rex_tabindex() ?>>redaxo.de</a></li><li><a href="http://forum.redaxo.de" onclick="window.open(this.href); return false;"<?php echo rex_tabindex() ?>>forum.redaxo.de</a></li><?php if(isset($REX['USER'])) echo '<li><a href="index.php?page=credits">'.$I18N->msg('credits').'</a></li>'; ?></ul>
      <p id="rex-scripttime"><!--DYN--><?php echo rex_showScriptTime() ?> sec | <?php echo rex_formatter :: format(time(), 'strftime', 'date'); ?><!--/DYN--></p>
    </div>
  </div>
  
  <div id="rex-extra"></div>
  <div id="rex-redaxo-link"><p><a href="./" title="Wohin verlinke ich?">Wohin verlinke ich?</a></p></div>
  
  </div><!-- END #rex-website -->
   </body>
</html>