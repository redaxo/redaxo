<?php

use Redaxo\Core\Backend\Controller;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\View;

echo View::title(I18n::msg('cronjob_title'));

Controller::includeCurrentPageSubPath();