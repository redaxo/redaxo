<?php

/**
 * REX_LINK_BUTTON,
 * REX_LINK,
 * REX_LINK_ID,
 * REX_LINKLIST_BUTTON,
 * REX_LINKLIST
 * 
 * Attribute:
 *   - category  => Kategorie in die beim oeffnen der Linkmapw gesprungen werden soll
 *
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_var_link extends rex_var
{
  // --------------------------------- Actions

  /*public*/ function getACRequestValues($REX_ACTION)
  {
    $values     = rex_request('LINK', 'array');
    $listvalues = rex_request('LINKLIST', 'array');
    for ($i = 1; $i < 11; $i++)
    {
      $link     = isset($values[$i]) ? stripslashes($values[$i]) : '';
      $linklist = isset($listvalues[$i]) ? stripslashes($listvalues[$i]) : '';

      $REX_ACTION['LINK'][$i] = $link;
      $REX_ACTION['LINKLIST'][$i] = $linklist;
    }
    return $REX_ACTION;
  }

  /*public*/ function getACDatabaseValues($REX_ACTION, & $sql)
  {
    for ($i = 1; $i < 11; $i++)
    {
      $REX_ACTION['LINK'][$i] = $this->getValue($sql, 'link'. $i);
      $REX_ACTION['LINKLIST'][$i] = $this->getValue($sql, 'linklist'. $i);
    }

    return $REX_ACTION;
  }

  /*public*/ function setACValues(& $sql, $REX_ACTION, $escape = false)
  {
    global $REX;

    for ($i = 1; $i < 11; $i++)
    {
      $this->setValue($sql, 'link'. $i, $REX_ACTION['LINK'][$i], $escape);
      $this->setValue($sql, 'linklist'. $i, $REX_ACTION['LINKLIST'][$i], $escape);
    }
  }

  // --------------------------------- Output

  /*public*/ function getBEOutput(& $sql, $content)
  {
    return $this->getOutput($sql, $content);
  }

  /*public*/ function getBEInput(& $sql, $content)
  {
    $content = $this->getOutput($sql, $content);
    $content = $this->matchLinkButton($sql, $content);
    $content = $this->matchLinkListButton($sql, $content);

    return $content;
  }

  /*private*/ function getOutput(& $sql, $content)
  {
    $content = $this->matchLinkList($sql, $content);
    $content = $this->matchLink($sql, $content);
    $content = $this->matchLinkId($sql, $content);

    return $content;
  }

  /*protected*/ function handleDefaultParam($varname, $args, $name, $value)
  {
    switch($name)
    {
      case '0' : 
        $args['id'] = (int) $value; 
        break;
      case '1' :
      case 'category' :
        $args['category'] = (int) $value;
        break;
    }
    return parent::handleDefaultParam($varname, $args, $name, $value);
  }

  /**
   * Button für die Eingabe
   */
  /*private*/ function matchLinkButton(& $sql, $content)
  {
  	global $REX;

  	$def_category = '';
  	$article_id = rex_request('article_id', 'int');
  	if($article_id != 0)
  	{
  		$art = OOArticle::getArticleById($article_id);
  		$def_category = $art->getCategoryId();
  	}

    $var = 'REX_LINK_BUTTON';
    $matches = $this->getVarParams($content, $var);
    foreach ($matches as $match)
    {
      list ($param_str, $args) = $match;
      list ($id, $args) = $this->extractArg('id', $args, 0);
      
      if ($id < 11 && $id > 0)
      {
        // Wenn vom Programmierer keine Kategorie vorgegeben wurde,
        // die Linkmap mit der aktuellen Kategorie öffnen
      	list ($category, $args) = $this->extractArg('category', $args, $def_category);

        $replace = $this->getLinkButton($id, $this->getValue($sql, 'link' . $id), $category, $args);
        $replace = $this->handleGlobalWidgetParams($var, $args, $replace);
        $content = str_replace($var . '[' . $param_str . ']', $replace, $content);
      }
    }

    return $content;
  }

  /**
   * Button für die Eingabe
   */
  /*private*/ function matchLinkListButton(& $sql, $content)
  {
    $var = 'REX_LINKLIST_BUTTON';
    $matches = $this->getVarParams($content, $var);
    foreach ($matches as $match)
    {
      list ($param_str, $args) = $match;
      list ($id, $args) = $this->extractArg('id', $args, 0);
      
      if ($id < 11 && $id > 0)
      {
        list ($category, $args) = $this->extractArg('category', $args, 0);

        $replace = $this->getLinklistButton($id, $this->getValue($sql, 'linklist' . $id), $category);
        $replace = $this->handleGlobalWidgetParams($var, $args, $replace);
        $content = str_replace($var . '[' . $param_str . ']', $replace, $content);
      }
    }

    return $content;
  }

  /**
   * Wert für die Ausgabe
   */
  /*private*/ function matchLink(& $sql, $content)
  {
    $var = 'REX_LINK';
    $matches = $this->getVarParams($content, $var);
    foreach ($matches as $match)
    {
      list ($param_str, $args) = $match;
      list ($id, $args) = $this->extractArg('id', $args, 0);
      
      if ($id > 0 && $id < 11)
      {
      	$replace = '';
      	if ($this->getValue($sql, 'link' . $id) != "")
      		$replace = rex_getUrl($this->getValue($sql, 'link' . $id));

        $replace = $this->handleGlobalVarParams($var, $args, $replace);
        $content = str_replace($var . '[' . $param_str . ']', $replace, $content);
      }
    }

    return $content;
  }

  /**
   * Wert für die Ausgabe
   */
  /*private*/ function matchLinkId(& $sql, $content)
  {
    $var = 'REX_LINK_ID';
    $matches = $this->getVarParams($content, $var);
    foreach ($matches as $match)
    {
      list ($param_str, $args) = $match;
      list ($id, $args) = $this->extractArg('id', $args, 0);
      
      if ($id > 0 && $id < 11)
      {
        $replace = $this->getValue($sql, 'link' . $id);
        $replace = $this->handleGlobalVarParams($var, $args, $replace);
        $content = str_replace($var . '[' . $param_str . ']', $replace, $content);
      }
    }

    return $content;
  }

  /**
   * Wert für die Ausgabe
   */
  /*private*/ function matchLinkList(& $sql, $content)
  {
    $var = 'REX_LINKLIST';
    $matches = $this->getVarParams($content, $var);
    foreach ($matches as $match)
    {
      list ($param_str, $args) = $match;
      list ($id, $args) = $this->extractArg('id', $args, 0);
      
      if ($id > 0 && $id < 11)
      {
        $replace = $this->getValue($sql, 'linklist' . $id);
        $replace = $this->handleGlobalVarParams($var, $args, $replace);
        $content = str_replace($var . '[' . $param_str . ']', $replace, $content);
      }
    }

    return $content;
  }
  
  // TODO: passenden namen finden
  /*public static*/ function _getLinkButton($name, $linkId, $article_id, $category = '')
  {
    $field = rex_var_link::getLinkButton($linkId, $article_id, $category);
    return str_replace('LINK['. $linkId .']', $name, $field);
  }

  /**
   * Gibt das Button Template zurück
   */
  /*public static*/ function getLinkButton($id, $article_id, $category = '')
  {
    global $REX, $I18N;

    $art_name = '';
    $clang = '';
    $art = OOArticle :: getArticleById($article_id);

    // Falls ein Artikel vorausgewählt ist, dessen Namen anzeigen und beim öffnen der Linkmap dessen Kategorie anzeigen
    if (OOArticle :: isValid($art))
    {
      $art_name = $art->getName();
			$category = $art->getCategoryId();
    }

    $open_params = '&clang=' . $REX['CUR_CLANG'];
    if ($category != '')
      $open_params .= '&category_id=' . $category;

    $open_class   = 'rex-icon-file-open rex-icon-file-open-inactive';
    $delete_class = 'rex-icon-file-delete rex-icon-file-delete-inactive';
    $open_func    = '';
    $delete_func  = '';
    if ($REX['USER']->hasStructurePerm())
    {
      $open_class   = 'rex-icon-file-open';
      $delete_class = 'rex-icon-file-delete';
      $open_func    = 'openLinkMap(\'LINK_' . $id . '\', \'' . $open_params . '\');';
      $delete_func  = 'deleteREXLink(' . $id . ');';
    }

    $media = '
	<div class="rex-widget">
		<div class="rex-widget-link">
      <p class="rex-widget-field">
  			<input type="hidden" name="LINK[' . $id . ']" id="LINK_' . $id . '" value="'. $article_id .'" />
  			<input type="text" size="30" name="LINK_NAME[' . $id . ']" value="' . htmlspecialchars($art_name) . '" id="LINK_' . $id . '_NAME" readonly="readonly" />
		  </p>
       <p class="rex-widget-icons rex-widget-1col">
       	<span class="rex-widget-column rex-widget-column-first">
  	     	<a href="#" class="'. $open_class .'" onclick="'. $open_func .'return false;" title="'. $I18N->msg('var_link_open') .'"'. rex_tabindex() .'></a>
 	  			<a href="#" class="'. $delete_class .'" onclick="'. $delete_func .'return false;" title="'. $I18N->msg('var_link_delete') .'"'. rex_tabindex() .'></a>
	 	  	</span>
 		  </p>
 		</div>
 	</div>
 	<div class="rex-clearer"></div>';

    return $media;
  }

  /**
   * Gibt das ListButton Template zurück
   */
  /*public static*/ function getLinklistButton($id, $value, $category = '')
  {
    global $REX, $I18N;

    $open_params = '&clang=' . $REX['CUR_CLANG'];
    if ($category != '')
      $open_params .= '&category_id=' . $category;

    $options = '';
    $linklistarray = explode(',', $value);
    if (is_array($linklistarray))
    {
      foreach ($linklistarray as $link)
      {
        if ($link != '')
        {
		  		if($article = OOArticle::getArticleById($link))
          	$options .= '<option value="' . $link . '">' . htmlspecialchars($article->getName()) . '</option>';
        }
      }
    }

    $open_class   = 'rex-icon-file-open rex-icon-file-open-inactive';
    $delete_class = 'rex-icon-file-delete rex-icon-file-delete-inactive';
    $open_func    = '';
    $delete_func  = '';
    if ($REX['USER']->hasStructurePerm())
    {
      $open_class   = 'rex-icon-file-open';
      $delete_class = 'rex-icon-file-delete';
      $open_func    = 'openREXLinklist(' . $id . ', \'' . $open_params . '\');';
      $delete_func  = 'deleteREXLinklist(' . $id . ');';
    }

    $link = '
  <div class="rex-widget">
    <div class="rex-widget-linklist">
      <input type="hidden" name="LINKLIST['. $id .']" id="REX_LINKLIST_'. $id .'" value="'. $value .'" />
      <p class="rex-widget-field">
        <select name="LINKLIST_SELECT[' . $id . ']" id="REX_LINKLIST_SELECT_' . $id . '" size="8"'. rex_tabindex() .'>
          ' . $options . '
        </select>
      </p>
       <p class="rex-widget-icons rex-widget-2col">
       	<span class="rex-widget-column rex-widget-column-first">
					<a href="#" class="rex-icon-file-top" onclick="moveREXLinklist(' . $id . ',\'top\');return false;" title="'. $I18N->msg('var_linklist_move_top') .'"'. rex_tabindex() .'></a>
					<a href="#" class="rex-icon-file-up" onclick="moveREXLinklist(' . $id . ',\'up\');return false;" title="'. $I18N->msg('var_linklist_move_up') .'"'. rex_tabindex() .'></a>
					<a href="#" class="rex-icon-file-down" onclick="moveREXLinklist(' . $id . ',\'down\');return false;" title="'. $I18N->msg('var_linklist_move_down') .'"'. rex_tabindex() .'></a>
	        <a href="#" class="rex-icon-file-bottom" onclick="moveREXLinklist(' . $id . ',\'bottom\');return false;" title="'. $I18N->msg('var_linklist_move_bottom') .'"'. rex_tabindex() .'></a>
       	</span>
       	<span class="rex-widget-column">
					<a href="#" class="'. $open_class .'" onclick="'. $open_func .'return false;" title="'. $I18N->msg('var_link_open') .'"'. rex_tabindex() .'></a>
					<a href="#" class="'. $delete_class .'" onclick="'. $delete_func .'return false;" title="'. $I18N->msg('var_link_delete') .'"'. rex_tabindex() .'></a>
        </span>
 	    </p>
    </div>
  </div>
 	<div class="rex-clearer"></div>
    ';

    return $link;
  }
}