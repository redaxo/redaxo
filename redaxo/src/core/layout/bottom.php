</section><?php

/**
 * Layout Fuß des Backends
 * @package redaxo5
 * @version svn:$Id$
 */



$footerfragment = new rex_fragment();
echo $footerfragment->parse('backend_footer');
unset($footerfragment);

$bottomfragment = new rex_fragment();
echo $bottomfragment->parse('backend_bottom');
unset($bottomfragment);