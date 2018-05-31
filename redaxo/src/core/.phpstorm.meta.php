<?php

namespace PHPSTORM_META;

override(
    \rex::getProperty(0),
    map([
        'login' => \rex_backend_login::class,
        'user' => \rex_user::class,
        'timer' => \rex_timer::class,
    ])
);

override(
    \rex_user::getComplexPerm(0),
    map([
        'clang' => \rex_clang_perm::class,
        'media' => \rex_media_perm::class,
        'module' => \rex_module_perm::class,
        'structure' => \rex_structure_perm::class,
    ])
);
