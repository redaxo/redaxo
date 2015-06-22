<?php

$fragment = new rex_fragment();
$fragment->setVar('buttons', $this->items, false);
$fragment->setVar('vertical', true, false);
echo $fragment->parse('core/buttons/button_group.php');
