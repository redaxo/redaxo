<?php

class rex_metainfo_category_handler extends rex_metainfo_handler
{
  const PREFIX = 'cat_';

  public function renderToggleButton(array $params)
  {
    $restrictionsCondition = $this->buildFilterCondition($params);

    $fields = parent::getSqlFields(self::PREFIX, $restrictionsCondition);
    if ($fields->getRows() >= 1) {
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

    //--></script><a id="rex-i-meta-category" class="rex-i-generic-open" href="javascript:rex_metainfo_toggle();">' . rex_i18n::msg('minfo_edit_metadata') . '</a></p>';

       return $params['subject'] . $return;
    }

    return $params['subject'];
  }

  public function handleSave(array $params, rex_sql $sqlFields)
  {
    if (rex_request_method() != 'post') return $params;

    $article = rex_sql::factory();
    // $article->debugsql = true;
    $article->setTable(rex::getTablePrefix() . 'article');
    $article->setWhere('id=:id AND clang=:clang', array('id' => $params['id'], 'clang' => $params['clang']));

    parent::fetchRequestValues($params, $article, $sqlFields);

    // do the save only when metafields are defined
    if ($article->hasValues())
      $article->update();

    // Artikel nochmal mit den zusätzlichen Werten neu generieren
    rex_article_cache::generateMeta($params['id'], $params['clang']);

    return $params;
  }

  protected function buildFilterCondition(array $params)
  {
    $s = '';

    if (!empty($params['id'])) {
      $OOCat = rex_category::getCategoryById($params['id'], $params['clang']);

      // Alle Metafelder des Pfades sind erlaubt
      foreach ($OOCat->getPathAsArray() as $pathElement) {
        if ($pathElement != '') {
          $s .= ' OR `p`.`restrictions` LIKE "%|' . $pathElement . '|%"';
        }
      }

      // Auch die Kategorie selbst kann Metafelder haben
      $s .= ' OR `p`.`restrictions` LIKE "%|' . $params['id'] . '|%"';
    }

    $restrictionsCondition = 'AND (`p`.`restrictions` = "" OR `p`.`restrictions` IS NULL ' . $s . ')';

    return $restrictionsCondition;
  }

  public function renderFormItem($field, $tag, $tag_attr, $id, $label, $labelIt, $typeLabel)
  {
    $add_td = '';
    $class_td = '';
    $class_tr = '';
    if (rex::getUser()->hasPerm('advancedMode[]'))
      $add_td = '<td></td>';

    $element = $field;
    if ($labelIt) {
      $element = '
         <' . $tag . $tag_attr . '>
           <label for="' . $id . '">' . $label . '</label>
           ' . $field . '
         </' . $tag . '>';
    }

    if ($typeLabel == 'legend') {
      $element = '<p class="rex-form-legend">' . $label . '</p>';
      $class_td = ' class="rex-colored"';
      $class_tr .= ' rex-metainfo-cat-b';
    }

    $s = '
    <tr class="rex-table-row-activ rex-metainfo-cat' . $class_tr . '" style="display:none;">
      <td></td>
      ' . $add_td . '
      <td colspan="5"' . $class_td . '>
         <div class="rex-form-row">
          ' . $element . '
        </div>
      </td>
    </tr>';

    return $s;
  }

  public function extendForm(array $params)
  {
    if (isset($params['category'])) {
      $params['activeItem'] = $params['category'];

      // Hier die category_id setzen, damit beim klick auf den REX_LINK_BUTTON der Medienpool in der aktuellen Kategorie startet
      $params['activeItem']->setValue('category_id', $params['id']);
    }

    $result = parent::renderFormAndSave(self::PREFIX, $params);

    // Bei CAT_ADDED und CAT_UPDATED nur speichern und kein Formular zur�ckgeben
    if ($params['extension_point'] == 'CAT_UPDATED' || $params['extension_point'] == 'CAT_ADDED')
      return $params['subject'];
    else
      return $params['subject'] . $result;
  }
}

$catHandler = new rex_metainfo_category_handler();

rex_extension::register('CAT_FORM_ADD', array($catHandler, 'extendForm'));
rex_extension::register('CAT_FORM_EDIT', array($catHandler, 'extendForm'));

rex_extension::register('CAT_ADDED', array($catHandler, 'extendForm'));
rex_extension::register('CAT_UPDATED', array($catHandler, 'extendForm'));

rex_extension::register('CAT_FORM_BUTTONS', array($catHandler, 'renderToggleButton'));
