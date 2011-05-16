<?php

class rex_metainfoHandler
{
  /**
   * Erstellt den nötigen HTML Code um ein Formular zu erweitern
   *
   * @param $sqlFields rex_sql-objekt, dass die zu verarbeitenden Felder enthält
   * @param $formatCallback callback, dem die infos als Array übergeben werden und den formatierten HTML Text zurückgibt
   * @param $epParams array Array of all EP parameters
   */
  public function rex_a62_metaFields($sqlFields, $formatCallback, $epParams)
  {
    $s = '';

    // Startwert für MEDIABUTTON, MEDIALIST, LINKLIST zähler
    $media_id = 1;
    $mlist_id = 1;
    $link_id  = 1;
    $llist_id = 1;

    $activeItem = isset($epParams['activeItem']) ? $epParams['activeItem'] : null;

    $sqlFields->reset();
    for($i = 0; $i < $sqlFields->getRows(); $i++, $sqlFields->next())
    {
      // Umschliessendes Tag von Label und Formularelement
      $tag      = 'p';
      $tag_attr = '';

      $name          = $sqlFields->getValue('name');
      $title         = $sqlFields->getValue('title');
      $params        = $sqlFields->getValue('params');
      $typeLabel     = $sqlFields->getValue('label');
      $attr          = $sqlFields->getValue('attributes');
      $dblength      = $sqlFields->getValue('dblength');
      $restrictions  = $sqlFields->getValue('restrictions');

      $attrArray = rex_split_string($attr);
      if(isset($attrArray['perm']))
      {
        if(!rex::getUser()->hasPerm($attrArray['perm']))
        {
          continue;
        }
        unset($attrArray['perm']);
      }

      $dbvalues = array($sqlFields->getValue('default'));
      if($activeItem)
      {
        $itemValue = $activeItem->getValue($name);

        if(strpos($itemValue, '|+|') !== false)
        {
          // Alte notation mit |+| als Trenner
          $dbvalues = explode('|+|', $activeItem->getValue($name));
        }
        else
        {
          // Neue Notation mit | als Trenner
          $dbvalues = explode('|', $activeItem->getValue($name));
        }
      }

      if($title != '')
        $label = rex_i18n::translate($title);
      else
        $label = htmlspecialchars($name);

      $id = preg_replace('/[^a-zA-Z\-0-9_]/', '_', $label);
      $labelIt = true;

      $field = '';

      switch($typeLabel)
      {
        case 'text':
        {
          $tag_attr = ' class="rex-form-text"';

          $rexInput = rex_input::factory($typeLabel);
          $rexInput->addAttributes($attrArray);
          $rexInput->setAttribute('id', $id);
          $rexInput->setAttribute('name', $name);
          if($dblength > 0)
            $rexInput->setAttribute('maxlength', $dblength);
          if($activeItem)
            $rexInput->setValue($activeItem->getValue($name));
          $field = $rexInput->getHtml();
          break;
        }
        case 'checkbox':
          $name .= '[]';
        case 'radio':
        {
          $values = array();
          if(rex_sql::getQueryType($params) == 'SELECT')
          {
            $sql = rex_sql::factory();
            $value_groups = $sql->getDBArray($params, PDO::FETCH_NUM);
            foreach($value_groups as $value_group)
            {
              if(isset($value_group[1]))
                $values[$value_group[1]] = $value_group[0];
              else
                $values[$value_group[0]] = $value_group[0];
            }
          }
          else
          {
            $value_groups = explode('|', $params);
            foreach($value_groups as $value_group)
            {
              // check ob key:value paar
              // und der wert beginnt nicht mit "translate:"
              if(strpos($value_group, ':') !== false &&
                 strpos($value_group, 'translate:') !== 0)
              {
                $temp = explode(':', $value_group, 2);
                $values[$temp[0]] = rex_i18n::translate($temp[1]);
              }
              else
              {
                $values[$value_group] = rex_i18n::translate($value_group);
              }
            }
          }

          $class_s = $typeLabel;
          $class_p = $typeLabel == 'radio' ? 'radios' : 'checkboxes';
          $oneValue = (count($values) == 1);

          if(!$oneValue)
          {
            $labelIt = false;
            $tag = 'div';
            $tag_attr = ' class="rex-form-col-a rex-form-'.$class_p.'"';
            $field .= '<p class="rex-form-label">'. $label .'</p><div class="rex-form-'.$class_p.'-wrapper">';
          }

          foreach($values as $key => $value)
          {
            $id = preg_replace('/[^a-zA-Z\-0-9_]/', '_', $id . $key);

            // wenn man keine Werte angibt (Boolean Chkbox/Radio)
            // Dummy Wert annehmen, damit an/aus unterscheidung funktioniert
            if($oneValue && $key == '')
              $key = 'true';

            $selected = '';
            if(in_array($key, $dbvalues))
              $selected = ' checked="checked"';

            if($oneValue)
            {
              $tag_attr = ' class="rex-form-col-a rex-form-'. $class_s .'"';
              $field .= '<input class="rex-form-'.$class_s.'" type="'. $typeLabel .'" name="'. $name .'" value="'. htmlspecialchars($key) .'" id="'. $id .'" '. $attr . $selected .' />'."\n";
            }
            else
            {
              $field .= '<p class="rex-form-'. $class_s .' rex-form-label-right">'."\n";
              $field .= '<input class="rex-form-'. $class_s .'" type="'. $typeLabel .'" name="'. $name .'" value="'. htmlspecialchars($key) .'" id="'. $id .'" '. $attr . $selected .' />'."\n";
              $field .= '<label for="'. $id .'">'. htmlspecialchars($value) .'</label>';
              $field .= '</p>'."\n";
            }

          }
          if(!$oneValue)
          {
          	$field .= '</div>';
          }

          break;
        }
        case 'select':
        {
          $tag_attr = ' class="rex-form-select"';

          $select = new rex_select();
  				$select->setStyle('class="rex-form-select"');
          $select->setName($name);
          $select->setId($id);
          // hier mit den "raw"-values arbeiten, da die rex_select klasse selbst escaped
          $select->setSelected($dbvalues);

  				$multiple = FALSE;
          foreach($attrArray as $attr_name => $attr_value)
          {
            if(empty($attr_name)) continue;

            $select->setAttribute($attr_name, $attr_value);

            if($attr_name == 'multiple')
            {
            	$multiple = TRUE;
              $select->setName($name.'[]');
            }
          }

          if(!$multiple)
          	$select->setSize(1);

          if(rex_sql::getQueryType($params) == 'SELECT')
          {
            // Werte via SQL Laden
            $select->addDBSqlOptions($params);
          }
          else
          {
            // Optionen mit | separiert
            // eine einzelne Option kann mit key:value separiert werden
            $values = array();
            $value_groups = explode('|', $params);
            foreach($value_groups as $value_group)
            {
              // check ob key:value paar
              // und der wert beginnt nicht mit "translate:"
              if(strpos($value_group, ':') !== false &&
                 strpos($value_group, 'translate:') !== 0)
              {
                $temp = explode(':', $value_group, 2);
                $values[$temp[0]] = rex_i18n::translate($temp[1]);
              }
              else
              {
                $values[$value_group] = rex_i18n::translate($value_group);
              }
            }
            $select->addOptions($values);
          }

          $field .= $select->get();
          break;
        }
        case 'date':
        case 'time':
        case 'datetime':
        {
          $tag_attr = ' class="rex-form-select-date"';

          $active = $dbvalues[0] != 0;
          if($dbvalues[0] == '')
            $dbvalues[0] = time();

          $inputValue = array();
          $inputValue['year'] = date('Y', $dbvalues[0]);
          $inputValue['month'] = date('m', $dbvalues[0]);
          $inputValue['day'] = date('j', $dbvalues[0]);
          $inputValue['hour'] = date('G', $dbvalues[0]);
          $inputValue['minute'] = date('i', $dbvalues[0]);

          $rexInput = rex_input::factory($typeLabel);
          $rexInput->addAttributes($attrArray);
          $rexInput->setAttribute('id', $id);
          $rexInput->setAttribute('name', $name);
          $rexInput->setValue($inputValue);
          $field = $rexInput->getHtml();

          $checked = $active ? ' checked="checked"' : '';
          $field .= '<input class="rex-form-select-checkbox rex-metainfo-checkbox" type="checkbox" name="'. $name .'[active]" value="1"'. $checked .' />';
          break;
        }
        case 'textarea':
        {
          $tag_attr = ' class="rex-form-textarea"';

          $rexInput = rex_input::factory($typeLabel);
          $rexInput->addAttributes($attrArray);
          $rexInput->setAttribute('id', $id);
          $rexInput->setAttribute('name', $name);
          if($activeItem)
            $rexInput->setValue($activeItem->getValue($name));
          $field = $rexInput->getHtml();

          break;
        }
        case 'legend':
        {
          $tag = '';
          $tag_attr = '';
          $labelIt = false;

          // tabindex entfernen, macht bei einer legend wenig sinn
          $attr = preg_replace('@tabindex="[^"]*"@', '', $attr);

          $field = '</div></fieldset><fieldset class="rex-form-col-1"><legend id="'. $id .'"'. $attr .'">'. $label .'</legend><div class="rex-form-wrapper">';
          break;
        }
        case 'REX_MEDIA_BUTTON':
        {
          $tag = 'div';
          $tag_attr = ' class="rex-form-widget"';

          $paramArray = rex_split_string($params);

          $rexInput = rex_input::factory('mediabutton');
          $rexInput->addAttributes($attrArray);
          $rexInput->setButtonId($media_id);
          $rexInput->setAttribute('name', $name);
          $rexInput->setValue($dbvalues[0]);

          if(isset($paramArray['category']))
            $rexInput->setCategoryId($paramArray['category']);
          if(isset($paramArray['types']))
            $rexInput->setTypes($paramArray['types']);
          if(isset($paramArray['preview']))
            $rexInput->setPreview($paramArray['preview']);

          $id = $rexInput->getAttribute('id');
          $field = $rexInput->getHtml();

          $media_id++;
          break;
        }
        case 'REX_MEDIALIST_BUTTON':
        {
          $tag = 'div';
          $tag_attr = ' class="rex-form-widget"';

          $paramArray = rex_split_string($params);

          $name .= '[]';
          $rexInput = rex_input::factory('medialistbutton');
          $rexInput->addAttributes($attrArray);
          $rexInput->setButtonId($mlist_id);
          $rexInput->setAttribute('name', $name);
          $rexInput->setValue($dbvalues[0]);

          if(isset($paramArray['category']))
            $rexInput->setCategoryId($paramArray['category']);
          if(isset($paramArray['types']))
            $rexInput->setTypes($paramArray['types']);
          if(isset($paramArray['preview']))
            $rexInput->setPreview($paramArray['preview']);

          $id = $rexInput->getAttribute('id');
          $field = $rexInput->getHtml();

          $mlist_id++;
          break;
        }
        case 'REX_LINK_BUTTON':
        {
          $tag = 'div';
          $tag_attr = ' class="rex-form-widget"';

          $paramArray = rex_split_string($params);
          $category = '';
          if(isset($paramArray['category']))
            $category = $paramArray['category'];
          else if($activeItem)
            $category = $activeItem->getValue('category_id');

          $rexInput = rex_input::factory('linkbutton');
          $rexInput->addAttributes($attrArray);
          $rexInput->setButtonId($link_id);
          $rexInput->setCategoryId($category);
          $rexInput->setAttribute('name', $name);
          $rexInput->setValue($dbvalues[0]);
          $id = $rexInput->getAttribute('id');
          $field = $rexInput->getHtml();

          $link_id++;
          break;
        }
        case 'REX_LINKLIST_BUTTON':
        {
          $tag = 'div';
          $tag_attr = ' class="rex-form-widget"';

          $paramArray = rex_split_string($params);
          $category = '';
          if(isset($paramArray['category']))
            $category = $paramArray['category'];
          else if($activeItem)
            $category = $activeItem->getValue('category_id');

          $name .= '[]';
          $rexInput = rex_input::factory('linklistbutton');
          $rexInput->addAttributes($attrArray);
          $rexInput->setButtonId($llist_id);
          $rexInput->setCategoryId($category);
          $rexInput->setAttribute('name', $name);
          $rexInput->setValue(implode(',',$dbvalues));
          $id = $rexInput->getAttribute('id');
          $field = $rexInput->getHtml();

          $llist_id++;
          break;
        }
        default :
        {
          // ----- EXTENSION POINT
          list($field, $tag, $tag_attr, $id, $label, $labelIt) =
            rex_extension::registerPoint( 'A62_CUSTOM_FIELD',
              array(
                $field,
                $tag,
                $tag_attr,
                $id,
                $label,
                $labelIt,
                'values' => $dbvalues,
                'rawvalues' => $dbvalues,
                'type' => $typeLabel,
                'sql' => $sqlFields)
              );
        }
      }

      $s .= rex_call_func($formatCallback, array($field, $tag, $tag_attr, $id, $label, $labelIt, $typeLabel), false);
    }

    return $s;
  }

  /**
   * Übernimmt die gePOSTeten werte in ein rex_sql-Objekt
   *
   * @param $sqlSave rex_sql-objekt, in das die aktuellen Werte gespeichert werden sollen
   * @param $sqlFields rex_sql-objekt, dass die zu verarbeitenden Felder enthält
   */
  public function _rex_a62_metainfo_handleSave(&$params, &$sqlSave, $sqlFields)
  {
    if(rex_request_method() != 'post') return;

    for($i = 0;$i < $sqlFields->getRows(); $i++, $sqlFields->next())
    {
      $fieldName = $sqlFields->getValue('name');
      $fieldType = $sqlFields->getValue('type');
      $fieldAttributes = $sqlFields->getValue('attributes');

      // dont save restricted fields
      $attrArray = rex_split_string($fieldAttributes);
      if(isset($attrArray['perm']))
      {
        if(!rex::getUser()->hasPerm($attrArray['perm']))
        {
          continue;
        }
        unset($attrArray['perm']);
      }

      // Wert in SQL zum speichern
      $saveValue = self::_rex_a62_metainfo_saveValue($fieldName, $fieldType, $fieldAttributes);
      $sqlSave->setValue($fieldName, $saveValue);

      // Werte im aktuellen Objekt speichern, dass zur Anzeige verwendet wird
      if(isset($params['activeItem']))
        $params['activeItem']->setValue($fieldName, $saveValue);
    }
  }

  /**
   * Retrieves the posted value for the given field and converts it into a saveable format.
   *
   * @param string $fieldName The name of the field
   * @param int $fieldType One of the REX_A62_FIELD_* constants
   * @param string $fieldAttributes The attributes of the field
   */
  public function _rex_a62_metainfo_saveValue($fieldName, $fieldType, $fieldAttributes)
  {
    if(rex_request_method() != 'post') return null;

    $postValue = rex_post($fieldName, 'array');

    // handle date types with timestamps
    if(isset($postValue['year']) && isset($postValue['month']) && isset($postValue['day']) && isset($postValue['hour']) && isset($postValue['minute']))
    {
      if(isset($postValue['active']))
        $saveValue = mktime((int)$postValue['hour'],(int)$postValue['minute'],0,(int)$postValue['month'],(int)$postValue['day'],(int)$postValue['year']);
      else
        $saveValue = 0;
    }
    // handle date types without timestamps
    elseif(isset($postValue['year']) && isset($postValue['month']) && isset($postValue['day']))
    {
      if(isset($postValue['active']))
        $saveValue = mktime(0,0,0,(int)$postValue['month'],(int)$postValue['day'],(int)$postValue['year']);
      else
        $saveValue = 0;
    }
    // handle time types
    elseif(isset($postValue['hour']) && isset($postValue['minute']))
    {
      if(isset($postValue['active']))
        $saveValue = mktime((int)$postValue['hour'],(int)$postValue['minute'],0,0,0,0);
      else
        $saveValue = 0;
    }
    else
    {
      if(count($postValue) > 1)
      {
        // Mehrwertige Felder
        $saveValue = '|'. implode('|', $postValue) .'|';
      }
      else
      {
        $postValue = isset($postValue[0]) ? $postValue[0] : '';
        if($fieldType == REX_A62_FIELD_SELECT && strpos($fieldAttributes, 'multiple') !== false ||
           $fieldType == REX_A62_FIELD_CHECKBOX)
        {
          // Mehrwertiges Feld, aber nur ein Wert ausgewählt
          $saveValue = '|'. $postValue .'|';
        }
        else
        {
          // Einwertige Felder
          $saveValue = $postValue;
        }
      }
    }

    return $saveValue;
  }

  /**
   * Ermittelt die metainfo felder mit dem Prefix $prefix limitiert auf die Kategorien $restrictions
   *
   * @param string $prefix Feldprefix
   * @param string $restrictionsCondition SQL Where-Bedingung zum einschränken der Metafelder
   * @return rex_sql Metainfofelder
   */
  public function _rex_a62_metainfo_sqlfields($prefix, $restrictionsCondition)
  {
    // replace LIKE wildcards
    $prefix = str_replace(array('_', '%'), array('\_', '\%'), $prefix);

    $qry = 'SELECT
              *
            FROM
              '. rex::getTablePrefix() .'62_params p,
              '. rex::getTablePrefix() .'62_type t
            WHERE
              `p`.`type` = `t`.`id` AND
              `p`.`name` LIKE "'. $prefix .'%"
              '. $restrictionsCondition .'
              ORDER BY
              prior';

    $sqlFields = rex_sql::factory();
    //$sqlFields->debugsql = true;
    $sqlFields->setQuery($qry);

    return $sqlFields;
  }

  /**
   * Erweitert das Meta-Formular um die neuen Meta-Felder
   *
   * @param string $prefix Feldprefix
   * @param string $params EP Params
   * @param callback $saveCallback Callback, dass die Daten speichert
   */
  public function _rex_a62_metainfo_form($prefix, $params, $saveCallback)
  {
    // Beim ADD gibts noch kein activeItem
    $activeItem = null;
    if(isset($params['activeItem']))
      $activeItem = $params['activeItem'];

    $restrictionsCondition = '';
    if($prefix == 'art_')
    {
      if($params['id'] != '')
      {
        $s = '';
        $OOArt = rex_ooArticle::getArticleById($params['id'], $params['clang']);

        // Alle Metafelder des Pfades sind erlaubt
        foreach(explode('|', $OOArt->getPath()) as $pathElement)
        {
          if($pathElement != '')
          {
            $s .= ' OR `p`.`restrictions` LIKE "%|'. $pathElement .'|%"';
          }
        }

        $restrictionsCondition = 'AND (`p`.`restrictions` = "" OR `p`.`restrictions` IS NULL '. $s .')';
      }
    }
    else if($prefix == 'cat_')
    {
      $s = '';

      if($params['id'] != '')
      {
        $OOCat = rex_ooCategory::getCategoryById($params['id'], $params['clang']);

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
    }
    else if($prefix == 'med_')
    {
      $catId = rex_session('media[rex_file_category]', 'int');
      if($activeItem)
      {
        $catId = $activeItem->getValue('category_id');
      }

      if($catId !== '')
      {
        $s = '';
        if($catId != 0)
        {
          $OOCat = rex_ooMediaCategory::getCategoryById($catId);

          // Alle Metafelder des Pfades sind erlaubt
          foreach(explode('|', $OOCat->getPath()) as $pathElement)
          {
            if($pathElement != '')
            {
              $s .= ' OR `p`.`restrictions` LIKE "%|'. $pathElement .'|%"';
            }
          }
        }

        // Auch die Kategorie selbst kann Metafelder haben
        $s .= ' OR `p`.`restrictions` LIKE "%|'. $catId .'|%"';

        $restrictionsCondition = 'AND (`p`.`restrictions` = "" OR `p`.`restrictions` IS NULL '. $s .')';
      }
    }

    $sqlFields = self::_rex_a62_metainfo_sqlfields($prefix, $restrictionsCondition);
    $params = rex_call_func($saveCallback, array($params, $sqlFields), false);

    // trigger callback of sql fields
    if(rex_request_method() == 'post')
    {
      foreach($sqlFields as $row)
      {
        if($row->getValue('callback') != '')
        {
          // use a small sandbox, so the callback cannot affect our local variables
          $sandboxFunc = function($field)
          {
            // TODO add var to ref the actual table (rex_article,...)
            $fieldName = $field->getValue('name');
            $fieldType = $field->getValue('type');
            $fieldAttributes = $field->getValue('attributes');
            $fieldValue = _rex_a62_metainfo_saveValue($fieldName, $fieldType, $fieldAttributes);

            require rex_stream::factory('metainfo/'. $field->getValue('field_id') .'/callback', $field->getValue('callback'));
          };
          // pass a clone to the custom handler, so the callback will not change our var
          $sandboxFunc(clone $row);
        }
      }
    }

    return self::rex_a62_metaFields($sqlFields, array($this, 'rex_a62_metainfo_form_item'), $params);
  }
}