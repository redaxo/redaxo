<?php

$curPage = rex_be_controller::getCurrentPageObject()->getPage();

if (!$curPage->hasLayout()) {
  return;
}

?>
</section><?php


$fragment = new rex_fragment();
$fragment->setVar('navigation', $navigation, false);
echo $fragment->parse('core/navigation.tpl');

$sidebar = rex_extension::registerPoint('PAGE_SIDEBAR', '');
if ($sidebar != '') {
  $sidebarfragment = new rex_fragment();
  $sidebarfragment->content = $sidebar;
  echo $sidebarfragment->parse('core/sidebar.tpl');
  unset($sidebarfragment);
}

unset($fragment);



$footerfragment = new rex_fragment();
$footerfragment->setVar('time', rex::getProperty('timer')->getFormattedDelta(rex_timer::SEC));
echo $footerfragment->parse('core/footer.tpl');
unset($footerfragment);

if (!rex_request::isPJAXContainer('#rex-page')) {
  $bottomfragment = new rex_fragment();
  echo $bottomfragment->parse('core/bottom.tpl');
  unset($bottomfragment);
}
