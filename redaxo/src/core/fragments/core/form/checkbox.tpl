<?php

$out = '';

foreach ($this->elements as $element) {

  $id         = isset($element['id'])         ? ' id="' . $element['id'] . '"' : '';
  $label      = isset($element['label'])      ? $element['label'] : '<label></label>';
  $field      = isset($element['field'])      ? $element['field'] : '';
  $note       = isset($element['note'])       ? '<span class="rex-note">' . $element['note'] . '</span>' : '';
  $highlight  = isset($element['highlight'])  ? $element['highlight'] : false;

  if ($field != '') {
    $match = $highlight ? '<em class="rex-highlight">$2</em>' : '$2';
    $label = preg_replace('@(<label\b[^>]*>)(.*?)(</label>)@', '$1' . $field . $match . '$3', $label);
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

  $out .= '<div class="rex-form-choice' . $classes . '"' . $id . '>';
  $out .= $label;
  $out .= $note;
  $out .= '</div>';
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
