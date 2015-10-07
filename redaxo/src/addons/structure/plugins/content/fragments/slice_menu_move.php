<?php

$fragment = new rex_fragment();
$fragment->setVar('buttons', $this->items, false);
$fragment->setVar('size', 'xs', false);
echo $fragment->parse('core/buttons/button_group.php');
