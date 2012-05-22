</section><?php


$fragment = new rex_fragment();
$fragment->setVar('navigation', $navigation, false);
echo $fragment->parse('backend_navigation.tpl');

$sidebar = rex_extension::registerPoint('PAGE_SIDEBAR', '');
if ($sidebar != "")
{
  $sidebarfragment = new rex_fragment();
  $sidebarfragment->content = $sidebar;
  echo $sidebarfragment->parse('backend_sidebar.tpl');
  unset($sidebarfragment);
}

unset($fragment);



/**
 * Layout Fuß des Backends
 * @package redaxo5
 */


$footerfragment = new rex_fragment();
$footerfragment->setVar('time', rex::getProperty('timer')->getFormattedDelta(rex_timer::SEC));
echo $footerfragment->parse('backend_footer.tpl');
unset($footerfragment);

$bottomfragment = new rex_fragment();
echo $bottomfragment->parse('backend_bottom.tpl');
unset($bottomfragment);
