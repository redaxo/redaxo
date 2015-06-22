<?php

$out = '';

foreach ($this->elements as $element) {
    $field = isset($element['field'])           ? $element['field']           : '';
    $moveButtons = isset($element['moveButtons'])     ? $element['moveButtons']     : '';
    $functionButtons = isset($element['functionButtons']) ? $element['functionButtons'] : '';

    $out .= '<div class="btn-toolbar">';

    if ($field != '') {
        $out .= '<span class="input-group">' . $field . '</span>';
    }

    if ($moveButtons != '') {
        $out .= '<div class="btn-group"><div class="btn-group-vertical">' . $moveButtons . '</div></div>';
    }

    if ($functionButtons != '') {
        $out .= '<div class="btn-group">' . $functionButtons . '</div>';
    }

    $out .= '</div>';
}

echo $out;
