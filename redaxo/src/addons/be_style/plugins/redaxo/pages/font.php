<?php

$font = rex_request('be_style_' . $mypage . '_font', 'string');


$fonts = [
    'entypo' => [
        'family' => 'entypo',
        'filename' => 'entypo-webfont',
        'svgID' => 'entypo',
        'weight' => 'normal',
        'style' => 'normal',
    ]
];


if ($font != '' && isset($fonts[$font])) {

    $output = '
@font-face {
    font-family: "' . $fonts[$font]['family'] . '";
    font-style: "' . $fonts[$font]['style'] . '";
    font-weight: "' . $fonts[$font]['weight'] . '";
    src: url("' . rex_url::pluginAssets('be_style', $mypage, $font) . '/' . $fonts[$font]['filename'] . '.eot");
    src: url("' . rex_url::pluginAssets('be_style', $mypage, $font) . '/' . $fonts[$font]['filename'] . '.eot?#iefix") format("eot"),
    url("' . rex_url::pluginAssets('be_style', $mypage, $font) . '/' . $fonts[$font]['filename'] . '.woff") format("woff"),
    url("' . rex_url::pluginAssets('be_style', $mypage, $font) . '/' . $fonts[$font]['filename'] . '.ttf") format("truetype"),
    url("' . rex_url::pluginAssets('be_style', $mypage, $font) . '/' . $fonts[$font]['filename'] . '.svg#' . $fonts[$font]['svgID'] . '") format("svg");
}';

    rex_response::sendContent($output, 'text/css');
    exit;
}
