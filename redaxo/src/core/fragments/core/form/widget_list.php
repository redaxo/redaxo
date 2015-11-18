<?php

$out = '';

foreach ($this->elements as $element) {
    $field = isset($element['field']) ? $element['field'] : '';
    $moveButtons = isset($element['moveButtons']) ? $element['moveButtons'] : '';
    $functionButtons = isset($element['functionButtons']) ? $element['functionButtons'] : '';
    $addon = trim($moveButtons . $functionButtons) == '' ? false : true;

    if (isset($element['before']) && $element['before'] != '') {
        $out .= $element['before'];
    }

    $out .= '<div class="input-group">' . $field;

    if ($addon) {
        $out .= '<span class="input-group-addon">';
        if ($moveButtons != '') {
            $out .= '<div class="btn-group-vertical">' . $moveButtons . '</div>';
        }

        if ($functionButtons != '') {
            $out .= '<div class="btn-group-vertical">' . $functionButtons . '</div>';
        }
        $out .= '</span>';
    }

    $out .= '</div>';

    if (isset($element['after']) && $element['after'] != '') {
        $out .= $element['after'];
    }
}

echo $out;
