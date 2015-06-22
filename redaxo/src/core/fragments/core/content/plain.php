<?php

$class_1 = ['rex-content'];
if (isset($this->params['flush'])) {
    $class_1[] = 'rex-flush';
}

$class_2 = ['rex-content-inner'];

echo '<section class="' . implode(' ', $class_1) . '">
  <div class="' . implode(' ', $class_2) . '">';

if ($this->title != '') {
    echo '<h2>' . $this->title . '</h2>';
}

if (count($this->content) == 1) {
    foreach ($this->content as $content) {
        echo $content;
    }
} else {
    echo '<div class="rex-grid' . count($this->content) . 'col">';
    $counter = 0;
    foreach ($this->content as $content) {
        ++$counter;
        $class = ['rex-column'];
        if ($counter == 1) {
            $class[] = 'rex-first';
        }
        if ($counter == count($this->content)) {
            $class[] = 'rex-last';
        }
        echo '<div class="' . implode(' ', $class) . '">' . $content . '</div>';
    }
    echo '</div>';
}

echo '</div>
      </section>';
