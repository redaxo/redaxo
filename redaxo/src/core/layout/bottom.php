<?php

use Redaxo\Core\Backend\Controller;
use Redaxo\Core\Core;
use Redaxo\Core\Util\Timer;
use Redaxo\Core\View\Fragment;

$curPage = Controller::requireCurrentPageObject();

if (!$curPage->hasLayout()) {
    if (rex_request::isPJAXRequest()) {
        echo '</section>';
    }

    return;
}

?>

</section></div><?php

if (rex_request::isPJAXContainer('#rex-js-page-container')) {
    return;
}

echo '</div>';

if ('login' !== Controller::getCurrentPage()) {
    $footerfragment = new Fragment();
    $footerfragment->setVar('time', Core::getProperty('timer')->getFormattedDelta(Timer::SEC));
    echo $footerfragment->parse('core/footer.php');
    unset($footerfragment);
}

$bottomfragment = new Fragment();
echo $bottomfragment->parse('core/bottom.php');
unset($bottomfragment);
