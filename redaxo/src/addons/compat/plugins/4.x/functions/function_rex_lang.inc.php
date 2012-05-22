<?php

/**
 * @deprecated 5.0
 */
function rex_lang_is_utf8()
{
  return true;
}

/**
 * @see rex_i18n
 *
 * @deprecated 5.0
 */
function rex_create_lang($locale = 'de_de', $searchpath = '', $setlocale = true)
{
  global $REX;

  if ($searchpath == '')
  {
    $searchpath = rex_path::core('lang');
  }
  $lang_object = new i18n($locale, $searchpath, $setlocale);

  return $lang_object;
}

/**
 * @see rex_i18n::translate()
 *
 * @deprecated 5.0
 */
function rex_translate($text, $I18N_Catalogue = null, $use_htmlspecialchars = true)
{
  return rex_i18n::translate($text, $use_htmlspecialchars);
}

/**
 * @see rex_i18n::translateArray()
 *
 * @deprecated 5.0
 */
function rex_translate_array($array, $I18N_Catalogue = null, $use_htmlspecialchars = true)
{
  return rex_i18n::translateArray($array, $use_htmlspecialchars);
}
