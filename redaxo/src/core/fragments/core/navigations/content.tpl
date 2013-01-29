<?php
/*
  Tabnavi  -> rex-navi-tab

  ->right = "text right from navi"
  ->left = "text left from navi"

  ->navigaion_left = left navi objekts
  ->navigaion_right = left navi objekts

*/

$navigations = array();

if (isset($this->navigation_left)) {
  $navigations['left'] = $this->navigation_left;

}

if (isset($this->navigation_right)) {
  $navigations['right'] = $this->navigation_right;

}

foreach ($navigations as $nav_key => $navigation) {

  foreach ($navigation as $navi) {
    if (isset($navi['children']) && count($navi['children']) > 0) {
      $navigations['children'] = $navi['children'];
    }
  }
}



foreach ($navigations as $nav_key => $navigation) {

  $li = array();
  foreach ($navigation as $navi) {

    $li_a = '<li ';

    if (isset($navi['itemClasses']) && is_array($navi['itemClasses']) && count($navi['itemClasses']) > 0 && isset($navi['itemClasses'][0]) && $navi['itemClasses'][0] != '') {
      $li_a .= ' class="' . implode(' ', $navi['itemClasses']) . '"';
    }

    if (isset($navi['itemAttr']) && is_array($navi['itemAttr']) && count($navi['itemAttr']) > 0) {
      foreach ($navi['itemAttr'] as $n => $v) {
        if ($v != '') {
          $li_a .= ' ' . $n . '="' . $v . '"';
        }
      }
    }

    $li_a .= '>';

    if (isset($navi['href']) && $navi['href'] != '') {
      $li_a .= '<a href="' . $navi['href'] . '"';
      if (isset($navi['linkClasses']) && is_array($navi['linkClasses']) && count($navi['linkClasses']) > 0 && isset($navi['itemClasses'][0]) && $navi['itemClasses'][0] != '') {
        $li_a .= ' class="rex-navi-content-item ' . implode(' ', $navi['linkClasses']) . '"';
      } else {
        $li_a .= ' class="rex-navi-content-item"';
      }
      if (isset($navi['linkAttr']) && is_array($navi['linkAttr']) && count($navi['linkAttr']) > 0) {
        foreach ($navi['linkAttr'] as $n => $v) {
          if ($v != '') {
            $li_a .= ' ' . $n . '="' . $v . '"';
          }
        }
      }
      $li_a .= '>';
    }

    $li_a .= $navi['title'];

    if (isset($navi['href']) && $navi['href'] != '') {
      $li_a .= '</a>';
    }

    $li_a .= '</li>';
    $li[] = $li_a;
  }


  $navigations[$nav_key] = implode($li);

}







echo '<div class="rex-navi-content">';

// left navi
if (isset($navigations['right'])) {
  echo '<div class="rex-navi-content-right">';
  echo '<ul class="rex-navi-content-items">';
  echo $navigations['right'];
  echo '</ul>';
  if (isset($this->text_right) && $this->text_right != '') {
    echo '<span class="rex-navi-content-text">' . $this->text_right . '</span>';
  }
  echo '</div>';
}


// left text
if (isset($this->text_left) && $this->text_left != '') {
  echo '<span class="rex-navi-content-text">' . $this->text_left . '</span>';
}

// left navi
if (isset($navigations['left'])) {
  echo '<ul class="rex-navi-content-items">';
  echo $navigations['left'];
  echo '</ul>';
}

echo '</div>';



if (isset($navigations['children'])) {

  echo '
<div class="rex-navi-content-head">

  <ul class="rex-piped">' . $navigations['children'] . '
    <li><a class="rex-active" href="">Vorhandene aktualisieren</a></li>
    <li><a href="">Neue hinzuf√ºgen</a></li>
    <li><a href="">Eigene hochladen</a></li>
  </ul>

</div>';
}
