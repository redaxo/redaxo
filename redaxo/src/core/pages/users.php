<?php

use Redaxo\Core\Backend\Controller;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\View;

echo View::title(I18n::msg('user_management'));

Controller::includeCurrentPageSubPath();