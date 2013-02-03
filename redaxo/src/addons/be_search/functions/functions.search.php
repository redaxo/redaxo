<?php

/**
 * Hebt einen Suchtreffer $neelde im Suchergebnis $string hervor
 *
 * @param $params
 */
function rex_be_search_highlight_hit($string, $needle)
{
  return preg_replace(
    '/(.*)(' . preg_quote($needle, '/') . ')(.*)/i',
    '\\1<span class="be_search-search-hit">\\2</span>\\3',
    $string
  );
}

/**
 * Bindet ggf extensions ein
 *
 * @param $params Extension-Point Parameter
 */
function rex_be_search_extensions_handler($params)
{
  $page = $params['subject'];

  // Include Extensions
  if ($page == 'structure') {
    require_once rex_path::addon('be_search', 'extensions/extension_search_structure.php');
    rex_extension::register('PAGE_STRUCTURE_HEADER', 'rex_be_search_structure');

  } elseif ($page == 'content') {
    require_once rex_path::addon('be_search', 'extensions/extension_search_structure.php');
    rex_extension::register('PAGE_CONTENT_HEADER', 'rex_be_search_structure');
  } elseif ($page == 'mediapool') {
    require_once rex_path::addon('be_search', 'extensions/extension_search_mpool.php');
    rex_extension::register('MEDIA_LIST_TOOLBAR', 'rex_be_search_mpool');
    rex_extension::register('MEDIA_LIST_QUERY', 'rex_be_search_mpool_query');
  }
}
