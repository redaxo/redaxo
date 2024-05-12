<?php

use Redaxo\Core\View\Fragment;

/**
 * @var Fragment $this
 * @psalm-scope-this Fragment
 */

/*
    Vars

    "title" of items list will not be escaped, the caller is responsible todo so.
*/

// --------------------- List Items
$listItems = [];

if (isset($this->title) && '' != $this->title) {
    $listItems[] = '<li class="rex-breadcrumb-title">' . $this->title . '</li>';
}

$items = $this->items;

if (count($items) > 0) {
    foreach ($items as $item) {
        $listItem = '';

        if (isset($item['title']) && '' != $item['title']) {
            $listItem .= $item['title'];
        }

        if (isset($item['href']) && '' != $item['href']) {
            $listItem = '<a class="rex-link-expanded" href="' . rex_escape($item['href']) . '">' . $listItem . '</a>';
        }

        $listItems[] = '<li>' . $listItem . '</li>';
    }
}

echo '<div' . ((isset($this->id) && '' != $this->id) ? ' id="' . $this->id . '"' : '') . ' class="rex-breadcrumb"><ol class="breadcrumb">' . implode('', $listItems) . '</ol></div>';
