<?php

/**
 * Menupunkt nur einbinden, falls ein Plugin sich angemeldet hat 
 * via BE_STYLE_PAGE_CONTENT inhalt auszugeben
 *  
 * @param $params Extension-Point Parameter
 */
function rex_be_add_page($params)
{
  if(rex_extension_is_registered('BE_STYLE_PAGE_CONTENT'))
  {
    global $REX;
    
    $mypage = 'be_style';
    $REX['ADDON']['name'][$mypage] = 'Backend Style';
  }
}