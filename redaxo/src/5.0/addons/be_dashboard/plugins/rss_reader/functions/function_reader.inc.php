<?php

/**
 * RSS Reader Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 *
 * @package redaxo4
 * @version svn:$Id$
 */

function rex_a656_rss_teaser($feedUrl, $numItems = 5)
{
  $feed = new rex_rssReader($feedUrl);
  $encoding = $feed->get_encoding();
  $title = rex_a656_convert($feed->get_title(), $encoding);

  $s = '';
  $s .= '<div class="rex-rss-feed">
           <h3>'. htmlspecialchars($title) .'</h3>
           <ul>';

  foreach ($feed->get_items(0, $numItems) as $item) {
    $s .= '
        <li>
            <a href="'. $item->get_permalink() .'" onclick="window.open(this.href); return false;">
              <span>'. rex_a656_convert($item->get_date('d.m.Y H:i'), $encoding) .'</span>
              '. rex_a656_convert($item->get_title(), $encoding) .
            '</a>
        </li>';
  }

  $s .= '</ul>
  </div>';

  unset($feed);

  return $s;
}

function rex_a656_convert($string, $sourceEncoding)
{
  static $transTables = array();

  if(!isset($transTables[$sourceEncoding]))
  {
    // trans-table damit unabhaengig von feed/backend encoding sonderzeichen richtig dargestellt werden
    $allEntities = get_html_translation_table(HTML_ENTITIES, ENT_NOQUOTES);
    $specialEntities = get_html_translation_table(HTML_SPECIALCHARS, ENT_NOQUOTES);
    $noTags = array_diff($allEntities, $specialEntities);

    if($sourceEncoding == 'UTF-8')
    {
      //konvertiere trans-table nach utf8
      foreach($noTags as $charkey => $char)
      {
        // jedes zeichen nach utf8 kodieren
        $noTags[utf8_encode($charkey)]= utf8_encode($char);
        // uebrig gebliebenes iso zeichen entfernen
        unset($noTags[$charkey]);
      }
    }

    $transTables[$sourceEncoding] = $noTags;
  }

  return strtr($string, $transTables[$sourceEncoding]);

}