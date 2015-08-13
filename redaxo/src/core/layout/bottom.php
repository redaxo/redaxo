<?php

$curPage = rex_be_controller::getCurrentPageObject();

if (!$curPage->hasLayout()) {
    return;
}

?>

</section></div></div><?php

$sidebar = rex_extension::registerPoint(new rex_extension_point('PAGE_SIDEBAR', ''));
if ($sidebar != '') {
    $sidebarfragment = new rex_fragment();
    $sidebarfragment->content = $sidebar;
    echo $sidebarfragment->parse('core/sidebar.php');
    unset($sidebarfragment);
}

unset($fragment);

$footerfragment = new rex_fragment();
$footerfragment->setVar('time', rex::getProperty('timer')->getFormattedDelta(rex_timer::SEC));
echo $footerfragment->parse('core/footer.php');
unset($footerfragment);

if (!rex_request::isPJAXContainer('#rex-page')) {
    $bottomfragment = new rex_fragment();
    echo $bottomfragment->parse('core/bottom.php');
    unset($bottomfragment);
}
