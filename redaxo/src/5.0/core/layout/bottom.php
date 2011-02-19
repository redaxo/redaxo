<?php

/**
 * Layout Fuß des Backends
 * @package redaxo4
 * @version svn:$Id$
 */

$bottomfragment = new rex_fragment();
echo $bottomfragment->parse('rex_bottom');
unset($bottomfragment);