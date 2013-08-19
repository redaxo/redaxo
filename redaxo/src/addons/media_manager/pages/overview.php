<?php

/** @var rex_addon $this */

ob_start();
require __DIR__ . '/../help.php';
$content = ob_get_contents();
ob_end_clean();

echo rex_view::content('block', $content, rex_i18n::msg('media_manager_overview_title', $this->getVersion()));
