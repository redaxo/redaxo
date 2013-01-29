<?php
/*
  Vars

  drop = down, left, right, up

  button        = (string) - Button Text
  button_title  = (string) - Button Prefix

  class         = (string) - add class
  close         = (bool) - Close Button in Droplist
  check         = (bool) - Check Icon in Droplist
  split         = (bool) - Split the Drop Button

  header        = (string) - Header Text in Droplist
  footer        = (string) - Footer Text in Droplist

  items         = (array) - Items in Droplist
                  $item = array();
                  $item['active']       = true;           // (bool)
                  $item['href']         = '#';            // (string)
                  $item['title']        = 'Title';        // (string)
                  $item['description']  = 'Description';  // (string)
                  $item['html']         = 'Markup';       // (string)

                  $items[] = $item;

*/

$class  = (isset($this->class) && $this->class != '') ? ' ' . $this->class : '';
$close  = (isset($this->close) && !$this->close) ? false : true;
$check  = (isset($this->check) && $this->check) ? true : false;

$header = isset($this->header) ? $this->header : '';
$footer = isset($this->footer) ? $this->footer : '';

$items  = $this->items;

// --------------------- List Items
$list_items = array();
foreach ($items as $item) {

  $list_item = '';

  $list_item_class = '';
  if (isset($item['active']) && $item['active']) {
    $list_item_class = ' rex-context-menu-active';
  }

  $button_text_tag = 'span';
  if ($check) {
    $list_item .= '<span class="rex-icon rex-icon-check"></span><div class="rex-context-menu-item-text">';
    $button_text_tag = 'h4';
  }

  if (isset($item['title']) && $item['title'] != '') {
    $list_item .= '<' . $button_text_tag . '>' . $item['title'] . '</' . $button_text_tag . '>';
  }

  if (isset($item['description']) && $item['description'] != '') {
    $list_item .= '<p class="rex-description">' . $item['description'] . '</p>';
  }

  if (isset($item['html']) && $item['html'] != '') {
    $list_item .= $item['html'];
  }

  if ($check) {
    $list_item .= '</div>';
  }



  if (isset($item['href']) && $item['href'] != '') {
    $list_item = '<a href="' . $item['href'] . '">' . $list_item . '</a>';
  }

  $list_items[] = '<li class="rex-context-menu-item' . $list_item_class . '">' . $list_item . '</li>';
}


// --------------------- List Header
$list_header = array();

if ($header != '') {
  $list_header[] = $header;
}
if ($close) {
  $list_header[] = '<span class="rex-icon rex-icon-close rex-js-close"></span>';
}



$list = '';

if (count($list_header) > 0) {
  $list .= '<div class="rex-context-menu-header">' . implode('', $list_header) . '</div>';
}

if (count($list_items) > 0) {
  $list .= '<ul class="rex-context-menu-list">' . implode('', $list_items) . '</ul>';
}

if ($footer != '') {
  $list .= '<div class="rex-context-menu-footer">' . $footer . '</div>';
}


$list = $list != '' ? '<div class="rex-context-menu-container">' . $list . '</div>' : '';


echo '<div class="rex-js-context-menu rex-context-menu' . $class . '">';
echo '<span class="rex-button rex-context-menu-button rex-js-context-menu-button rex-icon rex-icon-context-menu">
        <span class="rex-drop"></span>
      </span>';
echo $list;
echo '</div>';
