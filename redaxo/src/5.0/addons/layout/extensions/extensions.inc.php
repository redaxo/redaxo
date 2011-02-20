<?php

/**
 * Layout 
 *
 * @author jan[dot]kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 *
 * @author thomas[dot]blum[at]redaxo[dot]de Thomas Blum
 * @author <a href="http://www.blumbeet.com">www.blumbeet.com</a>
 *
 */
 

/**
 * Menupunkt nur einbinden, falls ein Plugin sich angemeldet hat 
 * via LAYOUT_PAGE_CONTENT inhalt auszugeben
 *  
 * @param $params Extension-Point Parameter
 */
function rex_layout_addPage($params)
{
  if(rex_extension_is_registered('LAYOUT_PAGE_CONTENT'))
  {
    global $REX;
    
    $mypage = 'layout';
    $REX['ADDON']['name'][$mypage] = 'Layout';
  }
}