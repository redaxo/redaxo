<?php

/**
 * MetaForm Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 */

$subpage = rex_be_controller::getCurrentPagePart(2);

echo rex_view::title(rex_i18n::msg('minfo_title'));

$prefix = match ($subpage) {
    'media' => 'med_',
    'categories' => 'cat_',
    'clangs' => 'clang_',
    default => 'art_',
};

$metaTable = rex_metainfo_meta_table($prefix);

require __DIR__ . '/field.php';
