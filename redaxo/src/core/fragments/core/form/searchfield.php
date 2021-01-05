<?php

$id = $clear = $placeholder = '';
$class = 'input-group input-group-xs has-feedback form-clear-button';
$clear = rex_i18n::msg('search_clear');
$placeholder = ' placeholder="' . rex_i18n::msg('search_placeholder') . '"';

if ($this->id) {
    $id = ' id="' . $this->id .'"';
}
if ($this->placeholder) {
    $placeholder = ' placeholder = "' . $this->placeholder . '"';
}
if ($this->class) {
    $class = ' class = "' . $this->class . '"';
}

echo '<div class="'. $class . '"' . $id . '>
      <span class="input-group-addon clear-button"><i class="fa fa-search "></i></span>
      <input class="form-control" type="text" autofocus ' . $placeholder . '>
      <span title="' . $clear . '" class="form-control-clear fa fa-times-circle form-control-feedback hidden"></span>
</div>';
