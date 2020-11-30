<?php

$curPage = rex_be_controller::getCurrentPageObject();

if (!$curPage->hasLayout()) {
    return;
}

?>

</section></div><?php

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
