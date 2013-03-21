<?php

$out = '';

foreach ($this->elements as $element) {

    $field  = isset($element['field'])  ? $element['field'] : '';

    $out .= $field;

}

echo '<fieldset class="rex-form-action">';
echo '<div class="rex-form-action-inner">';
echo $out;
echo '</div>';
echo '</fieldset>';
