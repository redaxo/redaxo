<?php

class rex_categoryMetainfoHandler extends rex_metainfoHandler
{
  /**
   * Artikel & Kategorien:
   *
   * Übernimmt die gePOSTeten werte in ein rex_sql-Objekt und speichert diese
   */
  public function _rex_a62_metainfo_cat_handleSave($params, $sqlFields)
  {
    if(rex_request_method() != 'post') return $params;

    $article = rex_sql::factory();
    // $article->debugsql = true;
    $article->setTable(rex::getTablePrefix(). 'article');
    $article->setWhere('id=:id AND clang=:clang', array('id'=> $params['id'], 'clang' => $params['clang']));

    parent::_rex_a62_metainfo_handleSave($params, $article, $sqlFields);

    // do the save only when metafields are defined
    if($article->hasValues())
      $article->update();

    // Artikel nochmal mit den zusätzlichen Werten neu generieren
    rex_article_cache::generateMeta($params['id'], $params['clang']);

    return $params;
  }

  public function rex_a62_metainfo_button($params)
  {
  	$s = '';
  	$restrictionsCondition = '';
  	if(isset($params['id']) && $params['id'] != '')
  	{
      $OOCat = rex_ooCategory::getCategoryById($params['id']);

      // Alle Metafelder des Pfades sind erlaubt
      foreach(explode('|', $OOCat->getPath()) as $pathElement)
      {
        if($pathElement != '')
        {
          $s .= ' OR `p`.`restrictions` LIKE "%|'. $pathElement .'|%"';
        }
      }

      // Auch die Kategorie selbst kann Metafelder haben
      $s .= ' OR `p`.`restrictions` LIKE "%|'. $params['id'] .'|%"';
  	}
    $restrictionsCondition = 'AND (`p`.`restrictions` = "" OR `p`.`restrictions` IS NULL '. $s .')';


  	$fields = parent::_rex_a62_metainfo_sqlfields('cat_', $restrictionsCondition);
  	if ($fields->getRows() >= 1)
    {
    	$return = '<p class="rex-button-add"><script type="text/javascript"><!--

    function rex_metainfo_toggle()
    {
    	jQuery("#rex-form-structure-category .rex-metainfo-cat").toggle();
  		metacat = jQuery("#rex-i-meta-category");
  		if(metacat.hasClass("rex-i-generic-open"))
  		{
  			metacat.removeClass("rex-i-generic-open");
  			metacat.addClass("rex-i-generic-close");
  		}
  		else
  		{
  			metacat.removeClass("rex-i-generic-close");
  			metacat.addClass("rex-i-generic-open");
  		}
    }

    //--></script><a id="rex-i-meta-category" class="rex-i-generic-open" href="javascript:rex_metainfo_toggle();">'. rex_i18n::msg('minfo_edit_metadata') .'</a></p>';

  	   return $params['subject'] . $return;
    }

    return $params['subject'];
  }

  /**
   * Callback, dass ein Formular item formatiert
   */
  public function rex_a62_metainfo_form_item($field, $tag, $tag_attr, $id, $label, $labelIt, $typeLabel)
  {
    $add_td = '';
    $class_td = '';
    $class_tr = '';
    if (rex::getUser()->hasPerm('advancedMode[]'))
      $add_td = '<td></td>';

    $element = $field;
    if ($labelIt)
    {
      $element = '
    	   <'.$tag.$tag_attr.'>
    	     <label for="'. $id .'">'. $label .'</label>
    	     '.$field.'
    	   </'.$tag.'>';
    }

    if ($typeLabel == 'legend')
    {
    	$element = '<p class="rex-form-legend">'. $label .'</p>';
      $class_td = ' class="rex-colored"';
      $class_tr .= ' rex-metainfo-cat-b';
    }

    $s = '
    <tr class="rex-table-row-activ rex-metainfo-cat'. $class_tr .'" style="display:none;">
    	<td></td>
    	'.$add_td.'
    	<td colspan="5"'.$class_td.'>
     	  <div class="rex-form-row">
    	    '.$element.'
    	  </div>
      </td>
    </tr>';

    return $s;
  }

  /**
   * Erweitert das Meta-Formular um die neuen Meta-Felder
   */
  public function rex_a62_metainfo_form($params)
  {
    if(isset($params['category']))
    {
      $params['activeItem'] = $params['category'];

      // Hier die category_id setzen, damit beim klick auf den REX_LINK_BUTTON der Medienpool in der aktuellen Kategorie startet
      $params['activeItem']->setValue('category_id', $params['id']);
    }

    $result = parent::_rex_a62_metainfo_form('cat_', $params, array($this, '_rex_a62_metainfo_cat_handleSave'));

    // Bei CAT_ADDED und CAT_UPDATED nur speichern und kein Formular zur�ckgeben
    if($params['extension_point'] == 'CAT_UPDATED' || $params['extension_point'] == 'CAT_ADDED')
      return $params['subject'];
    else
      return $params['subject'] . $result;
  }
}

$catHandler = new rex_categoryMetainfoHandler();

rex_extension::register('CAT_FORM_ADD', array($catHandler, 'rex_a62_metainfo_form'));
rex_extension::register('CAT_FORM_EDIT', array($catHandler, 'rex_a62_metainfo_form'));

rex_extension::register('CAT_ADDED', array($catHandler, 'rex_a62_metainfo_form'));
rex_extension::register('CAT_UPDATED', array($catHandler, 'rex_a62_metainfo_form'));

rex_extension::register('CAT_FORM_BUTTONS', array($catHandler, 'rex_a62_metainfo_button'));
