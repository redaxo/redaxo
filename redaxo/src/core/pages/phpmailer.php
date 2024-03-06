<?php

use Redaxo\Core\Translation\I18n;

echo rex_view::title(I18n::msg('phpmailer_title'));

rex_be_controller::includeCurrentPageSubPath();
