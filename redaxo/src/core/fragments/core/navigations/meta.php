<?php

use Redaxo\Core\ExtensionPoint\Extension;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */

$items = $this->items;

// --------------------- List Items
$listItems = [];

if (count($items) > 0) {
    foreach ($items as $item) {
        $listItem = '';

        if (isset($item['title']) && '' != $item['title']) {
            $listItem .= $item['title'];
        }

        $attributes = '';
        if (isset($item['attributes']) && '' != trim($item['attributes'])) {
            $attributes = ' ' . trim($item['attributes']);
        }

        if (isset($item['href']) && '' != $item['href']) {
            $listItem = '<a href="' . rex_escape($item['href']) . '"' . $attributes . '>' . $listItem . '</a>';
        } elseif ('' != $attributes) {
            $listItem = '<span' . $attributes . '>' . $listItem . '</span>';
        }

        $listItems[] = '<li>' . $listItem . '</li>';
    }

    $listItems = Extension::registerPoint(new ExtensionPoint('META_NAVI', $listItems));

    if (count($listItems) > 0) {
        echo '  <div class="rex-nav-meta">
                    <ul class="nav navbar-nav navbar-right">' . implode('', $listItems) . '</ul>
                </div>';
    }
}
