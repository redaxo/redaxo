<?php

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
