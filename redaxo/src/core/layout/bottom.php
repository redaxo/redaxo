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

$bottomfragment = new rex_fragment();
echo $bottomfragment->parse('core/bottom.php');
unset($bottomfragment);
