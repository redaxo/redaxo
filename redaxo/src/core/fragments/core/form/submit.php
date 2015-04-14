<?php

$out = '';

foreach ($this->elements as $element) {

    $field  = isset($element['field'])  ? $element['field'] : '';

    $out .= $field;

}

echo '<div class="rex-form-panel-footer">';
echo $out;
echo '</div>';
