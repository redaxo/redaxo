<?php

$out = '';

foreach ($this->elements as $element) {

    $id         = isset($element['id'])     && $element['id'] != ''     ? ' id="' . $element['id'] . '"' : '';
    $label      = isset($element['label'])  && $element['label'] != ''  ? '<dt>' . $element['label'] . '</dt>' : '';
    $field      = isset($element['field'])  && $element['field'] != ''  ? $element['field']   : '';
    
    $before     = isset($element['before']) ? $element['before'] : '';
    $after      = isset($element['after'])  ? $element['after']  : '';
    
    $left_side  = isset($element['left'])   ? $element['left']   : '';
    $right_side = isset($element['right'])  ? $element['right']  : '';
    
    $header     = isset($element['header']) ? $element['header'] : '';
    $footer     = isset($element['footer']) ? $element['footer'] : '';
    
    $note       = isset($element['note'])   ? '<dd class="rex-note">' . $element['note'] . '</dd>' : '';

    $classes = '';

    $error = '';
    if (isset($element['error']) && $element['error'] != '') {
        $classes .= ' rex-form-error';
        $error  = '<dd class="rex-error">' . $element['error'] . '</dd>';
    }
    if (isset($element['required']) && $element['required']) {
        $classes .= ' rex-required';
    }
    if (isset($element['class']) && $element['class'] != '') {
        $classes .= ' ' . $element['class'];
    }

    if ($left_side != '') {

        $class = 'rex-form-input-group-addon';
        if (preg_match('@class=[\'|"]rex-button[^"\']@', $left_side)) {

            $class = 'rex-form-input-group-button';

        }

        $field = '<span class="' . $class . '">' . $left_side . '</span>' . $field;

    }

    if ($right_side != '') {

        $class = 'rex-form-input-group-addon';
        if (preg_match('@class=[\'|"]rex-button[^"\']@', $right_side)) {

            $class = 'rex-form-input-group-button';

        }

        $field = $field . '<span class="' . $class . '">' . $right_side . '</span>';

    }

    $field = '<div class="rex-form-input-group">' . $field . '</div>';

    $out .= $header;
    $out .= '<dl class="rex-form' . $classes . '"' . $id . '>';
    $out .= $label;
    $out .= '<dd>' . $before . $field . $after . '</dd>';
    $out .= $note;
    $out .= $error;
    $out .= '</dl>';
    $out .= $footer;

}

echo $out;
