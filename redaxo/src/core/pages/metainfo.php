<?php

use Redaxo\Core\Translation\I18n;

$subpage = rex_be_controller::getCurrentPagePart(2);

echo rex_view::title(I18n::msg('minfo_title'));

$prefix = match ($subpage) {
    'articles' => 'art_',
    'media' => 'med_',
    'categories' => 'cat_',
    'clangs' => 'clang_',
    default => '',
};

if ('' === $prefix) {
    rex_be_controller::includeCurrentPageSubPath();
} else {
    $metaTable = rex_metainfo_meta_table($prefix);
    require __DIR__ . '/metainfo.field.php';
}
