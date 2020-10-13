<?php


if (rex::isBackend() && rex::getUser()) {
    rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $ep) {
        $s = '</head>';
        $r = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0-beta.20/dist/shoelace/shoelace.css">
            <script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0-beta.20/dist/shoelace/shoelace.esm.js"></script>
            <style>
            html {
                font-size: inherit;
            }
            </style>
            </head>';
        $ep->setSubject(str_replace($s, $r, $ep->getSubject()));
    });
}
