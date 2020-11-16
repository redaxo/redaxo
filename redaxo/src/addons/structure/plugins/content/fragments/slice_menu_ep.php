<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */

$fragment = new rex_fragment();
$fragment->setVar('buttons', $this->items, false);
$fragment->setVar('size', 'xs', false);
echo $fragment->parse('core/buttons/button_group.php');
