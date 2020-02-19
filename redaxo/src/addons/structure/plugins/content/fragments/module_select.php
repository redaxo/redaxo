<?php
/**
 * Discussion Issue #1174
 * Manipulate this fragment to influence the selection of modules on the slice.
 * By default the core fragment is used.
 *
 * @var bool   $block
 * @var string $button_label
 * @var array  $items        array contains all modules
 *             [0]        the index of array
 *             - [id]     the module id
 *             - [title]  the module name
 *             - [href]   the module url
 */

$this->subfragment('/core/dropdowns/dropdown.php');
