<?php

use Redaxo\Core\View\Fragment;

/**
 * @var Fragment $this
 * @psalm-scope-this rex_fragment
 */
$fragment = new Fragment();
$fragment->setVar('buttons', $this->items, false);
$fragment->setVar('size', 'xs', false);
echo $fragment->parse('core/buttons/button_group.php');
