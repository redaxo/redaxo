<?php

/**
 * Layout Fuß des Backends
 * @package redaxo4
 * @version svn:$Id$
 */

$bottomfragment = new rex_fragment();
echo $bottomfragment->parse('layout/bottom');
unset($bottomfragment);