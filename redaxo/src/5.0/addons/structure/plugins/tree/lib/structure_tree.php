<?php

// klick auf + -> ordner aufklappen
// klick auf namen -> artikel werden angezeigt




class rex_structure_tree {

  public
    $clang,
    $tree_categories_open = array(),
    $tree_category_show = "",
    $context;

  public function __construct() {
  
    $this->tree_categories_open = rex_request::session("tree_categories_open","array");
    $category_toggle_id = rex_request("rex_tree_category_toggle","int",-1);
    if($category_toggle_id > 0) 
    {
      if(array_key_exists($category_toggle_id,$this->tree_categories_open)) 
      {
        unset($this->tree_categories_open[$category_toggle_id]);
      }else
      {
        $this->tree_categories_open[$category_toggle_id] = $category_toggle_id;
      }
      rex_request::setSession("tree_categories_open",$this->tree_categories_open);
    }
      
    $this->tree_category_show = rex_request::session("tree_category_show","int");
    $category_show_id = rex_request("rex_tree_category_show","int",-1);
    if($category_show_id > 0) {
      $this->tree_category_show = $category_show_id;
      rex_request::setSession("tree_category_show",$this->tree_category_show);
      $this->tree_categories_open[$this->tree_category_show] = $this->tree_category_show;
      rex_request::setSession("tree_categories_open",$this->tree_categories_open);
    }
  
  }

  public function setContext($context) {
    $this->context = $context;
  }

  public function setClang($clang) {
    $this->clang = $clang;
  }

  public function getConfig($type)
  {
    switch($type) {
      case("categories"):
        return $this->tree_categories_open;
      case("category"):
        return $this->tree_category_show;
    }
  }

  public function getTree() 
  {
    $roots = rex_ooCategory::getRootCategories();
  
    $mountpoints = rex::getUser()->getComplexPerm('structure')->getMountpoints();
    if(count($mountpoints)>0)
    {
      $roots = array();
      foreach($mountpoints as $mp)
      {
        if(rex_ooCategory::getCategoryById($mp))
          $roots[] = rex_ooCategory::getCategoryById($mp);
      }
    }

    $categories = $this->getChildren($roots);
    $articles = $this->getArticles($this->getConfig("category"));

    return '<div id="rex-tree">'.$categories.$articles.'</div>';
  }

  public function getChildren($children)
  {
    $ul = '';
    if(is_array($children))
    {
      $li = '';
      
      foreach($children as $cat){
      
        $cat_children = $cat->getChildren();
        $cat_id = $cat->getId();
        $liclasses = '';
        $linkclasses = '';
        $sub_li = '';
        $t = "[&nbsp;&nbsp;]";
        if (count($cat_children)>0) {
          $liclasses .= 'rex-children ';
          $linkclasses .= 'rex-tree-is-not-empty ';
          $t = "[+]";
        }
  
        if (next($children)== null ) $liclasses .= 'rex-children-last ';
        $linkclasses .= $cat->isOnline() ? 'rex-online ' : 'rex-offline ';
        
        if (in_array($cat_id,$this->getConfig("categories")))
        {
          if (count($cat_children)>0)
            $t = "[-]";
          $sub_li = $this->getChildren($cat_children);
          $liclasses .= 'rex-active ';
          $linkclasses .= 'rex-active ';
        }
  
        if($liclasses != '')
          $liclasses = ' class="'. rtrim($liclasses) .'"';
  
        if($linkclasses != '')
          $linkclasses = ' class="'. rtrim($linkclasses) .'"';
  
        $label = $this->formatLabel($cat);
      
        // $link_open
  
        $li .= '<li'.$liclasses.'>';
        $li .= '<a href="'.$this->context->getUrl(
            array(
              'rex_tree_category_toggle' => $cat_id, 
              // 'rex-api-call' => 'category_status', 
             )
           ).'" >&nbsp;'.$t.'&nbsp;&nbsp;</a>'; // class="rex-api-get"
        
        $li .= '<a'.$linkclasses.' href="'. $this->context->getUrl(
            array(
              'rex_tree_category_show' => $cat_id,
              'category_id' => $cat_id, 
             )
           ).'">'.htmlspecialchars($label).'</a>';
        $li .= $sub_li;
        $li .= '</li>'. "\n";
      }
  
      if ($li != '') 
        $ul = '<ul>'.$li.'</ul>';
    }
    return $ul;
  }



  private function getArticles($category_id = NULL) 
  {
    if(!$category_id)
      return FALSE;
    if(!($category = rex_ooCategory::getCategoryById($category_id)))
      return FALSE;
    
    $li = '';
    $articles = $category->getArticles();
    if ($articles)
    {
      foreach($articles as $article)
      {
        $liClass = $article->isStartpage() ? ' class="rex-tree-startpage"' : '';
        $liAttr = $article->getId() == $this->getConfig("category_id") ? ' id="rex-tree-active"' : '';
        $linkAttr = ' class="'. ($article->isOnline() ? 'rex-online' : 'rex-offine'). '"';
        $linkAttr .= ' href="'. $this->context->getUrl(
            array(
              'category_id' => $article->getCategoryId(),
              'article_id' => $article->getId(),
              'mode' => 'edit'
             )
           ) .'"';
        $label = $this->formatLabel($article);
        $li .= '<li'. $liAttr .'><a'. $linkAttr .'>'. htmlspecialchars($label) . '</a></li>';
      }
    }
    
    $ul = "";
    if ($li != '') 
      $ul = '<ul>'."\n".$li.'</ul>'. "\n";
    
    return $ul;
  }

  private function formatLabel($OOobject)
  {
    $label = $OOobject->getName();
  
    if(trim($label) == '')
      $label = '&nbsp;';
  
    if (rex::getUser()->hasPerm('advancedMode[]'))
      $label .= ' ['. $OOobject->getId() .']';

    return $label;
  }


}

?>