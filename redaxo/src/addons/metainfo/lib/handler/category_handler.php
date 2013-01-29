<?php

class rex_metainfo_category_handler extends rex_metainfo_handler
{
  const PREFIX = 'cat_';

  public function renderToggleButton(array $params)
  {
    $restrictionsCondition = $this->buildFilterCondition($params);

    $fields = parent::getSqlFields(self::PREFIX, $restrictionsCondition);
    if ($fields->getRows() >= 1) {


      // Beispiel Fields
      $items = array();

      $f = array();
      $f['label'] = '<label>REX_MEDIA</label>';
      $f['field'] = rex_var_media::getWidget('id', 'name', 'value');

      $fragment = new rex_fragment();
      $fragment->setVar('elements', array($f), false);
      $item = array();
      $item['html']  = $fragment->parse('core/form/form.tpl');
      $items[] = $item;

      $f = array();
      $f['label'] = '<label>REX_MEDIALIST</label>';
      $f['field'] = rex_var_medialist::getWidget('id', 'name', 'value');

      $fragment = new rex_fragment();
      $fragment->setVar('elements', array($f), false);
      $item = array();
      $item['html']  = $fragment->parse('core/form/form.tpl');
      $items[] = $item;

      $f = array();
      $f['label'] = '<label>REX_LINK</label>';
      $f['field'] = rex_var_link::getWidget('id', 'name', 'value');

      $fragment = new rex_fragment();
      $fragment->setVar('elements', array($f), false);
      $item = array();
      $item['html']  = $fragment->parse('core/form/form.tpl');
      $items[] = $item;

      $f = array();
      $f['label'] = '<label>REX_LINKLIST</label>';
      $f['field'] = rex_var_linklist::getWidget('id', 'name', 'value');

      $fragment = new rex_fragment();
      $fragment->setVar('elements', array($f), false);
      $item = array();
      $item['html']  = $fragment->parse('core/form/form.tpl');
      $items[] = $item;




      $formElements = array();

      $n = array();
      $n['field'] = '<a class="rex-back" href="' . rex_url::currentBackendPage() . '"><span class="rex-icon rex-icon-back"></span>' . rex_i18n::msg('form_abort') . '</a>';
      $formElements[] = $n;

      $n = array();
      $n['field'] = '<button class="rex-button" type="submit">Kategorie speichern</button>';
      $formElements[] = $n;

      $n = array();
      $n['field'] = '<button class="rex-button" type="submit">Kategorie übernehmen</button>';
      $formElements[] = $n;

      $fragment = new rex_fragment();
      $fragment->setVar('elements', $formElements, false);
      $footer = $fragment->parse('core/form/submit.tpl');

      $fragment = new rex_fragment();
      $fragment->setVar('header', rex_i18n::msg('minfo_edit_metadata'));
      $fragment->setVar('items', $items, false);
      $fragment->setVar('class', 'rex-large');
      $fragment->setVar('footer', $footer, false);
      $return = $fragment->parse('core/navigations/context.tpl');


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
