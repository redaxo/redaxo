<?php

$curPage = rex_be_controller::getCurrentPageObject();

if (rex_request::isPJAXRequest()) {
    echo rex_minibar::getInstance()->get();
}

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

$sidebar = rex_extension::registerPoint(new rex_extension_point('PAGE_SIDEBAR', ''));
if ($sidebar != '') {
    $sidebarfragment = new rex_fragment();
    $sidebarfragment->content = $sidebar;
    echo $sidebarfragment->parse('core/sidebar.php');
    unset($sidebarfragment);
}

unset($fragment);

$bottomfragment = new rex_fragment();
echo $bottomfragment->parse('core/bottom.php');
unset($bottomfragment);
