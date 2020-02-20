<?php

/*
    Tabnavi  -> rex-navi-tab

    ->right = "text right from navi"
    ->left = "text left from navi"

    ->navigaion_left = left navi objekts
    ->navigaion_right = left navi objekts

*/

$navigations = [];

if (isset($this->left)) {
    $navigations['left'] = $this->left;
}

if (isset($this->right)) {
    $navigations['right'] = array_reverse($this->right);
}

foreach ($navigations as $nav_key => $navigation) {
    foreach ($navigation as $navi) {
        if (isset($navi['active']) && $navi['active'] && isset($navi['children']) && count($navi['children']) > 0) {
            $navigations['children'] = $navi['children'];
        }
    }
}

foreach ($navigations as $nav_key => $navigation) {
    $li = [];
    foreach ($navigation as $navi) {
        $li_a = '';

        $attributes = [];

        if ('right' == $nav_key) {
            if (isset($navi['itemClasses']) && is_array($navi['itemClasses'])) {
                array_unshift($navi['itemClasses'], 'pull-right');
            } else {
                $navi['itemClasses'] = ['pull-right'];
            }
        }

        if (isset($navi['itemAttr']['class']) && '' != $navi['itemAttr']['class']) {
            if (!in_array($navi['itemAttr']['class'], $navi['itemClasses'])) {
                array_unshift($navi['itemClasses'], $navi['itemAttr']['class']);
            }
            unset($navi['itemAttr']['class']);
        }

        if (isset($navi['active']) && $navi['active']) {
            if (isset($navi['itemClasses']) && is_array($navi['itemClasses'])) {
                array_unshift($navi['itemClasses'], 'active');
            } else {
                $navi['itemClasses'] = ['active'];
            }
        }

        if (isset($navi['itemClasses']) && is_array($navi['itemClasses']) && count($navi['itemClasses']) > 0 && isset($navi['itemClasses'][0]) && '' != $navi['itemClasses'][0]) {
            $attributes['class'] = implode(' ', $navi['itemClasses']);
        }

        if (isset($navi['itemAttr']) && is_array($navi['itemAttr']) && count($navi['itemAttr']) > 0) {
            foreach ($navi['itemAttr'] as $key => $value) {
                if ('' != $value) {
                    $attributes[$key] = $value;
                }
            }
        }

        $li_a .= '<li' . rex_string::buildAttributes($attributes) . '>';

        if (isset($navi['href']) && '' != $navi['href']) {
            $attributes = [];
            $attributes['href'] = $navi['href'];

            if (isset($navi['linkClasses']) && is_array($navi['linkClasses']) && count($navi['linkClasses']) > 0 && isset($navi['linkClasses'][0]) && '' != $navi['linkClasses'][0]) {
                $attributes['class'] = implode(' ', $navi['linkClasses']);
            }

            if (isset($navi['linkAttr']) && is_array($navi['linkAttr']) && count($navi['linkAttr']) > 0) {
                foreach ($navi['linkAttr'] as $key => $value) {
                    if ('' != $value) {
                        $attributes[$key] = $value;
                    }
                }
            }

            $li_a .= '<a' . rex_string::buildAttributes($attributes) . '>';
        }

        if (isset($navi['icon']) && '' != $navi['icon']) {
            $li_a .= '<i class="' . $navi['icon'] . '"></i> ';
        }

        $li_a .= $navi['title'];

        if (isset($navi['href']) && '' != $navi['href']) {
            $li_a .= '</a>';
        }

        $li_a .= '</li>';
        $li[] = $li_a;
    }

    $navigations[$nav_key] = implode('', $li);
}

$out = '';

$tabs = '';
$tabs .= $navigations['left'] ?? '';
$tabs .= $navigations['right'] ?? '';
$out .= '' == $tabs ? '' : '<ul class="nav nav-tabs">' . $tabs . '</ul>';

if (isset($navigations['children'])) {
    $out .= '<nav class="navbar navbar-default"><ul class="nav navbar-nav">' . $navigations['children'] . '</ul></nav>';
}

if ('' != $out) {
    echo '<div' . ((isset($this->id) && '' != $this->id) ? ' id="' .  $this->id . '"' : '') . ' class="nav rex-page-nav">' . $out . '</div>';
}
