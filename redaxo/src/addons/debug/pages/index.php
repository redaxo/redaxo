<?php

use Redaxo\Core\Translation\I18n;

echo rex_view::title('Debug AddOn');
echo rex_view::info('<a href="' . rex_url::backendPage('system/settings') . '">' . I18n::msg('debug_activate_debugmode') . '</a>');

echo rex_view::warning(I18n::msg('debug_mode_note'));
