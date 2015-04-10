<?php

$out = '';

foreach ($this->elements as $element) {

    $id         = isset($element['id'])         ? ' id="' . $element['id'] . '"' : '';
    $label      = isset($element['label'])      ? $element['label'] : '<label></label>';
    $field      = isset($element['field'])      ? $element['field'] : '';
    $note       = isset($element['note'])       ? '<span class="help-block">' . $element['note'] . '</span>' : '';
    $highlight  = isset($element['highlight'])  ? $element['highlight'] : false;

    if ($field != '') {
        $match = $highlight ? '<em class="rex-highlight">$2</em>' : '$2';
        $label = preg_replace('@(<label\b[^>]*>)(.*?)(</label>)@', '$1' . $field . $match . $note . '$3', $label);
    }

    $classes = '';

    $error = '';
    if (isset($element['error']) && $element['error'] != '') {
        $classes .= ' rex-form-error';
        $error  = '<dd class="rex-error">' . $element['error'] . '</dd>';
    }
    if (isset($element['required']) && $element['required']) {
        $classes .= ' rex-required';
    }

    $out .= '<div class="checkbox' . $classes . '"' . $id . '>';
    $out .= $label;
    $out .= '</div>';
}

echo $out;
