<?php

/**
 * Backend Search Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 * @version svn:$Id$
 */

function rex_be_search_mpool($params)
{
  global $REX;

  if(!$REX['USER']->hasPerm('be_search[mediapool]'))
  {
    return $params['subject'];
  }

  if(rex_request('subpage', 'string') != '') return $params['subject'];
  $media_name = rex_request('be_media_name', 'string');

  $subject = $params['subject'];

  $search_form = '
    <p class="rex-form-col-a rex-form-text" id="be_search-media-search">
      <label for="be_search-media-name">'. rex_i18n::msg('be_search_mpool_media') .'</label>
      <input class="rex-form-text" type="text" name="be_search_media_name" id="be_search-media-name" value="'. $media_name .'" />
      <input class="rex-form-submit" type="submit" value="'. rex_i18n::msg('be_search_mpool_start') .'" />
    </p>
  ';

  $subject = str_replace('<div class="rex-form-row">', '<div class="rex-form-row">' . $search_form, $subject);
  $subject = str_replace('<fieldset class="rex-form-col-1">', '<fieldset class="rex-form-col-2">', $subject);
  $subject = str_replace('<p class="rex-form-select">', '<p class="rex-form-col-b rex-form-select">', $subject);

  return $subject;
}

function rex_be_search_mpool_query($params)
{
  global $REX;

  if(!$REX['USER']->hasPerm('be_search[mediapool]'))
  {
    return $params['subject'];
  }

  $media_name = rex_request('be_search_media_name', 'string');
  if($media_name == '') return $params['subject'];

  $qry = $params['subject'];
  $category_id = $params['category_id'];

  // replace LIKE wildcards
  $media_name = str_replace(array('_', '%'), array('\_', '\%'), $media_name);
  $where = " f.category_id = c.id AND (f.filename LIKE '%". $media_name ."%' OR f.title LIKE '%". $media_name ."%')";
  switch(rex_ooAddon::getProperty('be_search', 'searchmode', 'local'))
  {
    case 'local':
    {
      // Suche auf aktuellen Kontext eingrenzen
      if($category_id != 0)
        $where .=" AND (c.path LIKE '%|". $params['category_id'] ."|%' OR c.id=". $params['category_id'] .") ";
      else
        $qry = str_replace('f.category_id=0', '1=1', $qry);
    }
  }

  $qry = str_replace('FROM ', 'FROM '. $REX['TABLE_PREFIX'] .'media_category c,', $qry);
  $qry = str_replace('WHERE ', 'WHERE '. $where .' AND ', $qry);

  return $qry;
}