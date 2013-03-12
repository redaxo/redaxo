<?php

$subpages = [];
echo rex_view::title('be_style', $subpages);

$pluginContent = rex_extension::registerPoint(new rex_extension_point('BE_STYLE_PAGE_CONTENT', '', []));

echo '
<div class="rex-addon-output">
    <h2 class="rex-hl2">Themes/Plugins</h2>

    <div class="rex-addon-content">
        ' . $pluginContent . '
    </div>
</div>';
