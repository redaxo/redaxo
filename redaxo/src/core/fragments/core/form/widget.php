<?php

$out = '';

foreach ($this->elements as $element) {

    $field           = isset($element['field'])           ? $element['field']           : '';
    $functionButtons = isset($element['functionButtons']) ? $element['functionButtons'] : '';


    $out .= '<div class="input-group">';

    $out .= $field;

    if ($functionButtons != '') {
        $out .= '<span class="input-group-btn">' . $functionButtons . '</span>';
    }

    $out .= '</div>';

}

echo $out;
