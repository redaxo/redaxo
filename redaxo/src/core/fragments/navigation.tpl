<?php
/*
  Hauptnavi   -> rex-navi-main
  Sprachen    -> rex-navi-switch
  Breadcrumb  -> rex-navi-path
  Tabnavi  -> rex-navi-tab
*/

$this->only_ul = false;

if (isset($this->type)) {
  switch ($this->type) {
    case 'tab':
    case 'tabsub':
    case 'action':
      $this->only_ul = true;
      break;
    case 'switch': break;
    case 'path': break;
    case 'slice': break;
    default: $this->type = 'main';
  }
}


if (isset($this->navigation)) {
  $this->blocks = array();
  $this->blocks[] = array('navigation' => $this->navigation);
}

foreach ($this->blocks as $block) {

  $navigation = array();
  if (isset($block['navigation'])) {
    $navigation = $block['navigation'];
  }

  $headline = array();
  if (isset($block['headline'])) {
    $headline = $block['headline'];
  }

  if (!$this->only_ul) {
    echo '<dl class="rex-navi-' . $this->type . '"><dt>';

    if (count($headline) > 0) {
      if (isset($headline['href'])) {
        echo '<a href="' . $headline['href'] . '">';
      }

      if (isset($headline['title'])) {
        echo htmlspecialchars($headline['title']);
      } else {
        echo htmlspecialchars($headline);
      }

      if (isset($headline['href'])) {
        echo '</a>';
      }
    }
    echo '</dt><dd>';
  }

  if ($this->only_ul) {
    echo '<ul class="rex-navi-' . $this->type . '">';
  } else {
    echo '<ul>';
  }

  foreach ($navigation as $navi) {
    echo '<li ';
    if (isset($navi['itemClasses']) && is_array($navi['itemClasses']) && count($navi['itemClasses']) > 0 && isset($navi['itemClasses'][0]) && $navi['itemClasses'][0] != '') {
      echo ' class="' . implode(' ', $navi['itemClasses']) . '"';
    }

    if (isset($navi['itemAttr']) && is_array($navi['itemAttr']) && count($navi['itemAttr']) > 0) {
      foreach ($navi['itemAttr'] as $n => $v) {
        if ($v != '') {
          echo ' ' . $n . '="' . $v . '"';
        }
      }
    }

    echo '>';

    if (isset($navi['href']) && $navi['href'] != '') {
      echo '<a href="' . $navi['href'] . '"';

      if (isset($navi['linkClasses']) && is_array($navi['linkClasses']) && count($navi['linkClasses']) > 0 && isset($navi['itemClasses'][0]) && $navi['itemClasses'][0] != '') {
        echo ' class="' . implode(' ', $navi['linkClasses']) . '"';
      }

      if (isset($navi['linkAttr']) && is_array($navi['linkAttr']) && count($navi['linkAttr']) > 0) {
        foreach ($navi['linkAttr'] as $n => $v) {
          if ($v != '') {
            echo ' ' . $n . '="' . $v . '"';
          }
        }
      }

      echo '>';
    }

    echo $navi['title'];

    if (isset($navi['href']) && $navi['href'] != '') {
      echo '</a>';
    }

    if (isset($navi['children']) && count($navi['children']) > 0) {
      echo '<ul>';

      foreach ($navi['children'] as $subnavi) {
        echo '<li ';

        if (isset($subnavi['itemClasses']) && is_array($subnavi['itemClasses']) && count($subnavi['itemClasses']) > 0) {
          echo ' class="' . implode(' ', $subnavi['itemClasses']) . '"';
        }

        if (isset($subnavi['itemAttr']) && is_array($subnavi['itemAttr']) && count($subnavi['itemAttr']) > 0) {
          foreach ($subnavi['itemAttr'] as $n => $v) {
            if ($v != '') {
              echo ' ' . $n . '="' . $v . '"';
            }
          }
        }

        echo '>';

        if (isset($subnavi['href']) && $navi['href'] != '') {
          echo '<a href="' . $subnavi['href'] . '"';

          if (isset($subnavi['linkClasses']) && is_array($subnavi['linkClasses']) && count($subnavi['itemClasses']) > 0) {
            echo ' class="' . implode(' ', $subnavi['linkClasses']) . '"';
          }

          if (isset($subnavi['linkAttr']) && is_array($subnavi['linkAttr']) && count($subnavi['linkAttr']) > 0) {
            foreach ($subnavi['linkAttr'] as $n => $v) {
              if ($v != '') {
                echo ' ' . $n . '="' . $v . '"';
              }
            }
          }

          echo '>';
        }

        echo $subnavi['title'];

        if (isset($navi['href']) && $navi['href'] != '') {
          echo '</a>';
        }

        echo '</li>';
      }

      echo '</ul>';
    }

    echo '</li>';
  }

  echo '</ul>';

  if (!$this->only_ul) {
    echo '</dd></dl>';
  }

}

?>
