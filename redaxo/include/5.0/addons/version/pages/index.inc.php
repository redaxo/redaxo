<?php

/**
 * Version AddOn
 *
 * @author jan.kristinus@redaxo.de Jan Kristinus
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

require $REX['INCLUDE_PATH'].'/layout/top.php';

rex_title('Version AddOn');

?>
<div class="rex-addon-output">
	<h2 class="rex-hl2"><?php echo $I18N_A461->msg('code_for_module_input'); ?></h2>

	<div class="rex-addon-content">
		<p class="rex-tx1"><?php echo $I18N_A461->msg('module_intro_help'); ?></p>
		<p class="rex-tx1"><?php echo $I18N_A461->msg('module_rights'); ?></p>
	</div>

</div>

<?php
require $REX['INCLUDE_PATH'].'/layout/bottom.php';