<?php

if (!isset($this->buttons)) {
    $this->buttons['button'] = $this->button;
}

foreach ($this->buttons as $button) {

    if (!isset($button['attributes'])) {
        $button['attributes'] = [];
    }
    if (!isset($button['attributes']['class'])) {
        $button['attributes']['class'] = [];
    }
    if (!in_array('rex-button', $button['attributes']['class'])) {
        $button['attributes']['class'] = array_merge(['rex-button'], $button['attributes']['class']);
    }

    if (!isset($button['label'])) {
        $button['label'] = '';
    }
    if (isset($button['hidden_label'])) {
        $button['label'] = '<span class="rex-hidden">' . $button['hidden_label'] . '</span>';
    }

    $icon = isset($button['icon']) ? '<span class="rex-icon rex-icon-' . $button['icon'] . '"></span>' : '';


    echo '<a href="' . $button['url'] . '"' . rex_string::buildAttributes($button['attributes']) . '>' . $icon . $button['label'] . '</a>';

}
