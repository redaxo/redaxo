<?php

echo rex_view::title('Debug AddOn');
echo rex_view::info('<a href="'. rex_url::backendPage('system/settings') .'">'.rex_i18n::msg('debug_activate_debugmode').'</a>');

echo rex_view::warning(rex_i18n::msg('debug_mode_note'));
