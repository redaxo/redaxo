<?php

namespace PHPSTORM_META;

override(
    \rex_user::getComplexPerm(0),
    map([
        'clang' => \rex_clang_perm::class,
        'media' => \rex_media_perm::class,
        'module' => \rex_module_perm::class,
        'structure' => \rex_structure_perm::class,
    ])
);
