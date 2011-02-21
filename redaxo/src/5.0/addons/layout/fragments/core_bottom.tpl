		
	</div><?php // END #rex-content ?>
		
	<div class="rex-aside" id="rex-sidebar"></div>


	<div class="rex-aside" id="rex-extra"></div>


	<div class="rex-footer" id="rex-footer">
    
    <ul><li class="rex-first"><a href="#rex-header"<?php echo rex_tabindex() ?>>&#94;</a></li><li><a href="http://www.yakamara.de" onclick="window.open(this.href); return false;"<?php echo rex_tabindex() ?>>yakamara.de</a></li><li><a href="http://www.redaxo.de" onclick="window.open(this.href); return false;"<?php echo rex_tabindex() ?>>redaxo.de</a></li><li><a href="http://forum.redaxo.de" onclick="window.open(this.href); return false;"<?php echo rex_tabindex() ?>>forum.redaxo.de</a></li><?php if(isset($REX['USER'])) echo '<li><a href="index.php?page=credits">'.$this->i18n('credits').'</a></li>'; ?></ul>
    
    <p><!--DYN--><?php echo rex_showScriptTime() ?> sec | <?php echo rex_formatter :: format(time(), 'strftime', 'date'); ?><!--/DYN--></p>

	</div>
    
    

</div><?php // END #rex-page ?>
</body>
</html>