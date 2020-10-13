<?php

$context = $this->getVar('context');

// --------------------------------------------- TITLE
echo rex_view::title(rex_i18n::msg('title_structure'));

// --------------------------------------------- Languages
$this->subfragment('/macro/clang-switch.php');
