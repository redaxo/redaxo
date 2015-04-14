<?php

$out = '';

foreach ($this->elements as $element) {

    $id         = isset($element['id'])         ? ' id="' . $element['id'] . '"' : '';
    $label      = isset($element['label'])      ? $element['label'] : '<label></label>';
    $field      = isset($element['field'])      ? $element['field'] : '';
    $note       = isset($element['note'])       ? '<span class="rex-form-note">' . $element['note'] . '</span>' : '';
    $highlight  = isset($element['highlight'])  ? $element['highlight'] : false;

    if ($field != '') {
        $match = $highlight ? '<mark>$2</mark>' : '$2';
        $label = preg_replace('@(<label\b[^>]*>)(.*?)(</label>)@', '$1' . $field . $match . '$3', $label);
    }

    $classes = '';

    $error = '';
    if (isset($element['error']) && $element['error'] != '') {
        $classes .= ' has-error';
        $error  = '<dd class="rex-error">' . $element['error'] . '</dd>';
    }
    if (isset($element['required']) && $element['required']) {
        $classes .= ' rex-is-required';
    }

    $out .= '<div class="radio' . $classes . '"' . $id . '>';
    $out .= $label;
    $out .= $note;
    $out .= '</div>';
}

echo $out;
