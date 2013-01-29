<?php

$out = '';

foreach ($this->elements as $element) {

  $id     = isset($element['id'])     && $element['id'] != ''     ? ' id="' . $element['id'] . '"' : '';
  $label  = isset($element['label'])  && $element['label'] != ''  ? '<dt>' . $element['label'] . '</dt>' : '';
  $field  = isset($element['field'])  && $element['field'] != ''  ? $element['field']   : '';

  $before = isset($element['before']) ? $element['before']  : '';
  $after  = isset($element['after'])  ? $element['after']   : '';

  $header = isset($element['header']) ? $element['header']  : '';
  $footer = isset($element['footer']) ? $element['footer']  : '';

  $note   = isset($element['note'])   ? '<dd class="rex-note">' . $element['note'] . '</dd>' : '';

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

  $out .= $header;
  $out .= '<dl class="rex-form' . $classes . '"' . $id . '>';
  $out .= $label;
  $out .= '<dd>' . $before . $field . $after . '</dd>';
  $out .= $note;
  $out .= $error;
  $out .= '</dl>';
  $out .= $footer;

}

$group = isset($this->group) ? $this->group : false;
$flush = isset($this->flush) ? $this->flush : false;
if ($group || $flush) {
  $classes = array();
  $classes[] = $group ? 'rex-form-group' : '';
  $classes[] = $flush ? 'rex-form-flush' : '';

  echo '<div class="' . trim(implode(' ', $classes)) . '">';
  echo $out;
  echo '</div>';
} else {
  echo $out;
}
