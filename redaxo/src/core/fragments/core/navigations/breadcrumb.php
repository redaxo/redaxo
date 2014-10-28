<?php
/*
    Vars

*/

// --------------------- List Items
$list_items = [];

if (isset($this->title) && $this->title != '') {

    $list_items[] = '<li class="rex-breadcrumb-title">' . $this->title . '</li>';

}

$items  = $this->items;

if (count($items) > 0) {
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

} else {

        $list_items[] = '<li>' . rex_i18n::msg('root_level') . '</li>';

}


echo '<div class="rex-breadcrumb"><ol class="breadcrumb">' . implode('', $list_items) . '</ol></div>';
