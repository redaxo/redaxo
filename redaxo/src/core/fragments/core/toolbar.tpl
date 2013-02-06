<?php

$content = isset($this->content) ? $this->content : '';
$flush   = isset($this->flush) ? $this->flush : false;

$classes = array('rex-toolbar');
if ($flush) {
  $classes[] = 'rex-form-flush';
}

if ($content != '') {
  echo '<section class="' . trim(implode(' ', $classes)) . '">
          <div class="rex-toolbar-inner">
          ' . $content . '
          </div>
        </section>';
}
