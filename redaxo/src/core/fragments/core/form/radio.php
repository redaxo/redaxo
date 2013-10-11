<?php

$group = isset($this->group) ? $this->group : false;
$flush = isset($this->flush) ? $this->flush : false;

$fragment = new rex_fragment();
$fragment->setVar('elements', $this->elements, false);
$fragment->setVar('group', $group);
$fragment->setVar('flush', $flush);
echo $fragment->parse('core/form/checkbox.php');
