<?php

/**
 * Menupunkt nur einbinden, falls ein Plugin sich angemeldet hat
 * via BE_STYLE_PAGE_CONTENT inhalt auszugeben.
 *
 * @package redaxo\be-style
 */
function rex_be_add_page()
{
    if (rex_extension::isRegistered('BE_STYLE_PAGE_CONTENT')) {
        rex_addon::get('be_style')->setProperty('name', 'Backend Style');
    }
}
