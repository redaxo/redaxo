<?php

use Redaxo\Core\Translation\I18n;

echo rex_view::title(I18n::msg('system'));

rex_be_controller::includeCurrentPageSubPath();
