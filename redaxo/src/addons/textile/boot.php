<?php

/**
 * Textile Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 *
 * @var rex_addon $this
 */

if (rex::isBackend()) {
    rex_view::addCssFile($this->getAssetsUrl('textile.css'));
}
