<?php
$echo = '';

$items = $this->items;


// --------------------- List Items
$list_items = array();

if (count($items) > 0) {
    foreach ($items as $item) {

        $list_item = '';

        if (isset($item['title']) && $item['title'] != '') {
            $list_item .= $item['title'];
        }

        $attributes = '';
        if (isset($item['attributes']) && trim($item['attributes']) != '') {
            $attributes = ' ' . trim($item['attributes']);
        }

        if (isset($item['href']) && $item['href'] != '') {
            $list_item = '<a href="' . $item['href'] . '"' . $attributes . '>' . $list_item . '</a>';
        } elseif ($attributes != '') {
            $list_item = '<span' . $attributes . '>' . $list_item . '</span>';
        }

        $list_items[] = '<li>' . $list_item . '</li>';

    }

    $list_items = rex_extension::registerPoint('META_NAVI', $list_items);

    if (count($list_items) > 0) {
        echo '
            <section id="rex-page-meta">
                <nav class="rex-navi-meta">
                    <ul>' . implode('', $list_items) . '</ul>
                </nav>
            </section>';
    }
}
