<?php

$echo = '';

$items = $this->items;

// --------------------- List Items
$list_items = [];

if (count($items) > 0) {
    foreach ($items as $item) {
        $list_item = '';

        if (isset($item['title']) && '' != $item['title']) {
            $list_item .= $item['title'];
        }

        $attributes = '';
        if (isset($item['attributes']) && '' != trim($item['attributes'])) {
            $attributes = ' ' . trim($item['attributes']);
        }

        if (isset($item['href']) && '' != $item['href']) {
            $list_item = '<a href="' . $item['href'] . '"' . $attributes . '>' . $list_item . '</a>';
        } elseif ('' != $attributes) {
            $list_item = '<span' . $attributes . '>' . $list_item . '</span>';
        }

        $list_items[] = '<li>' . $list_item . '</li>';
    }

    $list_items = rex_extension::registerPoint(new rex_extension_point('META_NAVI', $list_items));

    if (count($list_items) > 0) {
        echo '  <div class="rex-nav-meta">
                    <ul class="nav navbar-nav navbar-right">' . implode('', $list_items) . '</ul>
                </div>';
    }
}
