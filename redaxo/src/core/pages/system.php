<?php

use Redaxo\Core\Backend\Controller;
use Redaxo\Core\Translation\I18n;

echo rex_view::title(I18n::msg('system'));

Controller::includeCurrentPageSubPath();
