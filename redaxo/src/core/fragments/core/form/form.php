<?php

$out = '';

foreach ($this->elements as $element) {
    $id = isset($element['id'])     && $element['id'] != ''     ? ' id="' . $element['id'] . '"' : '';
    $label = isset($element['label'])  && $element['label'] != ''  ? '<dt>' . $element['label'] . '</dt>' : '';
    $field = isset($element['field'])  && $element['field'] != ''  ? $element['field']   : '';

    $before = isset($element['before']) ? $element['before']  : '';
    $after = isset($element['after'])  ? $element['after']   : '';

    $header = isset($element['header']) ? $element['header']  : '';
    $footer = isset($element['footer']) ? $element['footer']  : '';

    $note = isset($element['note']) && $element['note'] != '' ? '<p class="help-block rex-note">' . $element['note'] . '</p>' : '';

    $classes = '';

    $error = '';
    if (isset($element['error']) && $element['error'] != '') {
        $classes .= ' has-error';
        $error = '<p class="rex-form-error">' . $element['error'] . '</p>';
    }
    if (isset($element['required']) && $element['required']) {
        $classes .= ' rex-is-required';
    }
    if (isset($element['class']) && $element['class'] != '') {
        $classes .= ' ' . $element['class'];
    }

    $out .= $header;
    $out .= '<dl class="rex-form-group form-group' . $classes . '"' . $id . '>';
    $out .= $label;
    $out .= '<dd>' . $before . $field . $after . $note . $error . '</dd>';
    $out .= '</dl>';
    $out .= $footer;
}

$classes = '';
/*
$classes .= isset($this->group)  && $this->group  ? ' rex-form-group' : '';
$classes .= isset($this->flush)  && $this->flush  ? ' rex-form-flush' : '';
$classes .= isset($this->inline) && $this->inline ? ' rex-form-inline' : '';
*/
if ($classes != '') {
    echo '<div class="' . trim($classes) . '">';
    echo $out;
    echo '</div>';
} else {
    echo $out;
}
