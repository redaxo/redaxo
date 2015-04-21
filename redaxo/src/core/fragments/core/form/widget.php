<?php

$out = '';

foreach ($this->elements as $element) {

    $field           = isset($element['field'])           ? $element['field']           : '';
    $functionButtons = isset($element['functionButtons']) ? $element['functionButtons'] : '';
    $before          = isset($element['before'])          ? $element['before']          : '';
    $after           = isset($element['after'])           ? $element['after']           : '';



    $out .= $before;

    $out .= '<div class="input-group">';

    $out .= $field;

    if ($functionButtons != '') {
        $out .= '<span class="input-group-btn">' . $functionButtons . '</span>';
    }

    $out .= '</div>';


    $out .= $after;

}

echo $out;
