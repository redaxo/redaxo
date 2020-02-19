<?php

/*
    Vars

    "title" of items list will not be escaped, the caller is responsible todo so.
*/

// --------------------- List Items
$list_items = [];

if (isset($this->title) && '' != $this->title) {
    $list_items[] = '<li class="rex-breadcrumb-title">' . $this->title . '</li>';
}

$items = $this->items;

if (count($items) > 0) {
    foreach ($items as $item) {
        $list_item = '';

        if (isset($item['title']) && '' != $item['title']) {
            $list_item .= $item['title'];
        }

        if (isset($item['href']) && '' != $item['href']) {
            $list_item = '<a href="' . $item['href'] . '">' . $list_item . '</a>';
        }

        $list_items[] = '<li>' . $list_item . '</li>';
    }
}

echo '<div' . ((isset($this->id) && '' != $this->id) ? ' id="' .  $this->id . '"' : '') . ' class="rex-breadcrumb"><ol class="breadcrumb">' . implode('', $list_items) . '</ol></div>';
