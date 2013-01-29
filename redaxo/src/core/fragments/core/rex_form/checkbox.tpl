<?php

$fragment = new rex_fragment();
$fragment->setVar('elements', $this->elements, false);
echo $fragment->parse('core/form/checkbox.tpl');
