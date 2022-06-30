<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */

$out = '';

foreach ($this->elements as $element) {
    $field = $element['field'] ?? '';

    $out .= $field;
}

echo '<div class="rex-form-panel-footer">';
echo '<div class="btn-toolbar">';
echo $out;
echo '</div>';
echo '</div>';
