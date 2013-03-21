<?php
/*
    Vars

*/

$title = (isset($this->title) && $this->title != '') ? '<dt>' . $this->title . '</dt>' : '';

$items  = $this->items;

// --------------------- List Items
$list_items = [];
foreach ($items as $item) {

    $list_item = '';

    if (isset($item['title']) && $item['title'] != '') {
        $list_item .= $item['title'];
    }

    if (isset($item['href']) && $item['href'] != '') {
        $list_item = '<a href="' . $item['href'] . '">' . $list_item . '</a>';
    }

    $list_items[] = '<li>' . $list_item . '</li>';
}


$list_items = (count($list_items) > 0) ? '<ul>' . implode('', $list_items) . '</ul>' : '<span>' . rex_i18n::msg('root_level') . '</span>';
    echo '<dl class="rex-navi-path">' . $title . '<dd>' . $list_items . '</dd></dl>';
