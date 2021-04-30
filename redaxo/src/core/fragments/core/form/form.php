<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */

$out = '';

foreach ($this->elements as $element) {
    $id = isset($element['id']) && '' != $element['id'] ? ' id="' . $element['id'] . '"' : '';
    $label = isset($element['label']) && '' != $element['label'] ? '<dt>' . $element['label'] . '</dt>' : '';
    $field = isset($element['field']) && '' != $element['field'] ? $element['field'] : '';

    $before = $element['before'] ?? '';
    $after = $element['after'] ?? '';

    $header = $element['header'] ?? '';
    $footer = $element['footer'] ?? '';

    $note = isset($element['note']) && '' != $element['note'] ? '<p class="help-block rex-note">' . $element['note'] . '</p>' : '';

    $classes = '';

    $error = '';
    if (isset($element['error']) && '' != $element['error']) {
        $classes .= ' has-error';
        $error = '<p class="rex-form-error">' . $element['error'] . '</p>';
    }
    if (isset($element['required']) && $element['required']) {
        $classes .= ' rex-is-required';
    }
    if (isset($element['class']) && '' != $element['class']) {
        $classes .= ' ' . $element['class'];
    }

    $out .= $header;
    $out .= '<dl class="rex-form-group form-group' . $classes . '"' . $id . '>';
    $out .= $label;
    $out .= '<dd>' . $before . $field . $after . $note . $error . '</dd>';
    $out .= '</dl>';
    $out .= $footer;
}

echo $out;
