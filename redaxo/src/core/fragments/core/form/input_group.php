<?php

$out = '';

foreach ($this->elements as $element) {
    $id = isset($element['id'])     && $element['id'] != ''     ? ' id="' . $element['id'] . '"' : '';
    $label = isset($element['label'])  && $element['label'] != ''  ? $element['label'] : '';
    $field = isset($element['field'])  && $element['field'] != ''  ? $element['field']   : '';

    $before = isset($element['before']) ? $element['before'] : '';
    $after = isset($element['after'])  ? $element['after']  : '';

    $left_side = isset($element['left'])   ? $element['left']   : '';
    $right_side = isset($element['right'])  ? $element['right']  : '';

    $header = isset($element['header']) ? $element['header'] : '';
    $footer = isset($element['footer']) ? $element['footer'] : '';

    $note = isset($element['note'])   ? '<span class="help-block">' . $element['note'] . '</span>' : '';

    $classes = '';

    $error = '';
    if (isset($element['error']) && $element['error'] != '') {
        $classes .= ' has-error';
        $error = '<span class="help-block text-danger">' . $element['error'] . '</span>';
    }
    if (isset($element['required']) && $element['required']) {
        $classes .= ' rex-is-required';
    }
    if (isset($element['class']) && $element['class'] != '') {
        $classes .= ' ' . $element['class'];
    }

    if ($left_side != '') {
        $class = 'input-group-addon';
        if (preg_match('@class=[\'|"]btn[^"\']@', $left_side)) {
            $class = 'input-group-btn';
        }

        $field = '<span class="' . $class . '">' . $left_side . '</span>' . $field;
    }

    if ($right_side != '') {
        $class = 'input-group-addon';
        if (preg_match('@class=[\'|"]btn[^"\']@', $right_side)) {
            $class = 'input-group-btn';
        }

        $field = $field . '<span class="' . $class . '">' . $right_side . '</span>';
    }

    $form_group = ($label == '' && $before == '' && $after == '' && $note == '' && $error == '') ? false : true;

    $out .= $header;
    $out .= $form_group ? '<div class="form-group">' : '';
    $out .= $label;
    $out .= $before;
    $out .= '<div class="input-group' . $classes . '"' . $id . '>' . $field . '</div>';
    $out .= $after;
    $out .= $note;
    $out .= $error;
    $out .= $form_group ? '</div>' : '';
    $out .= $footer;
}

echo $out;
