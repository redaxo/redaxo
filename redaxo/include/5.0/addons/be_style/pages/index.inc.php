<?php

include $REX["INCLUDE_PATH"]."/layout/top.php";

$subpages = array();
rex_title("be_style", $subpages);

$pluginContent = rex_register_extension_point('BE_STYLE_PAGE_CONTENT', '', array());

echo '
<div class="rex-addon-output">
  <h2 class="rex-hl2">Themes/Plugins</h2>
        
  <div class="rex-addon-content">
    '. $pluginContent .'
  </div>
</div>';

include $REX["INCLUDE_PATH"]."/layout/bottom.php";
