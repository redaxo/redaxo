<?php

$curPage = rex_be_controller::requireCurrentPageObject();

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

if ('login' !== rex_be_controller::getCurrentPage()) {
    $footerfragment = new rex_fragment();
    $footerfragment->setVar('time', rex::getProperty('timer')->getFormattedDelta(rex_timer::SEC));
    echo $footerfragment->parse('core/footer.php');
    unset($footerfragment);
}

$bottomfragment = new rex_fragment();
echo $bottomfragment->parse('core/bottom.php');
unset($bottomfragment);
