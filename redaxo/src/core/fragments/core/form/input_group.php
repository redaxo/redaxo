<?php

$out = '';

foreach ($this->elements as $element) {
    $id = isset($element['id']) && '' != $element['id'] ? ' id="' . $element['id'] . '"' : '';
    $field = isset($element['field']) && '' != $element['field'] ? $element['field'] : '';
    $left_side = $element['left'] ?? '';
    $right_side = $element['right'] ?? '';
    // special for bootstrap-select
    $before = $element['before'] ?? '';
    $after = $element['after'] ?? '';

    $classes = '';

    if (isset($element['class']) && '' != $element['class']) {
        $classes .= ' ' . $element['class'];
    }

    if ('' != $left_side) {
        $class = 'input-group-addon';
        if (preg_match('@class=[\'|"]btn[^"\']@', $left_side)) {
            $class = 'input-group-btn';
        }

        $field = '<span class="' . $class . '">' . $left_side . '</span>' . $field;
    }

    if ('' != $right_side) {
        $class = 'input-group-addon';
        if (preg_match('@class=[\'|"]btn[^"\']@', $right_side)) {
            $class = 'input-group-btn';
        }

        $field = $field . '<span class="' . $class . '">' . $right_side . '</span>';
    }

    $out .= '<div class="input-group' . $classes . '"' . $id . '>' . $before . $field . $after . '</div>';
}

echo $out;
