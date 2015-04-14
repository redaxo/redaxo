<?php

$out = '';
// Gruppierte Radios werden nochmals via form/form.php geparsed
// Bsp. Radios in der rex_form
$grouped = isset($this->grouped) ? $this->grouped : false;
$inline = isset($this->inline) ? $this->inline : false;

foreach ($this->elements as $element) {

    $id         = isset($element['id']) && $element['id'] != '' ? ' id="' . $element['id'] . '"' : '';
    $label      = isset($element['label']) ? $element['label'] : '<label></label>';
    $field      = isset($element['field']) ? $element['field'] : '';

    $before    = isset($element['before']) ? $element['before']  : '';
    $after     = isset($element['after'])  ? $element['after']   : '';
    
    $header    = isset($element['header']) ? $element['header']  : '';
    $footer    = isset($element['footer']) ? $element['footer']  : '';
    
    $note      = isset($element['note']) && $element['note'] != '' ? '<span class="help-block rex-note">' . $element['note'] . '</span>' : '';
    $highlight = isset($element['highlight']) ? $element['highlight'] : false;


    if ($field != '') {
        $match = $highlight ? '<mark>$2</mark>' : '$2';
        $label = preg_replace('@(<label\b[^>]*>)(.*?)(</label>)@', '$1' . $field . $match . $note . '$3', $label);
    }

    $classes = '';

    $error = '';
    if (isset($element['error']) && $element['error'] != '') {
        $classes .= ' has-error';
        $error  = '<dd class="rex-form-error">' . $element['error'] . '</dd>';
    }
    if (isset($element['required']) && $element['required']) {
        $classes .= ' rex-is-required';
    }

    $class = $inline ? '-inline' : '';

    if ($classes != '') {
        $out .= '<div class="' . trim($classes) . '">';
    }

    $out .= '<div class="radio' . $class . '"' . $id . '>';
    $out .= $label;
    $out .= '</div>';

    if ($classes != '') {
        $out .= '</div>';
    }
}

echo $out;
