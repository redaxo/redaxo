<?php

class rex_categoryTree {
  private $context;
  
  public function __construct(rex_context $context) {
    $this->context = $context;
  }
  
  public function renderTree($category_id) {
    $tree = array();
    $category = rex_ooCategory::getCategoryById($category_id);
  
    if ($category)
    {
      foreach($category->getParentTree() as $cat)
      {
        $tree[] = $cat->getId();
      }
    }
  
    $mountpoints = rex::getUser()->getComplexPerm('structure')->getMountpoints();
    if(count($mountpoints)>0)
    {
      $roots = array();
      foreach($mountpoints as $mp)
      {
        if(rex_ooCategory::getCategoryById($mp))
        {
          $roots[] = rex_ooCategory::getCategoryById($mp);
        }
      }
    }
    else
    {
      $roots = rex_ooCategory::getRootCategories();
    }
  
    $structureTree = new rex_treeRenderer($this->context);
    return $structureTree->renderTree($roots, $tree);
  }
}

class rex_articleList {
  private $context;
  
  public function __construct(rex_context $context) {
    $this->context = $context;
  }
  
  public function renderList($category_id) {
    $isRoot = $category_id === 0;
    $mountpoints = rex::getUser()->getComplexPerm('structure')->getMountpoints();
  
    if($isRoot && count($mountpoints)==0)
    {
      $articles = rex_ooArticle::getRootArticles();
    }
    else
    {
      $articles = rex_ooArticle::getArticlesOfCategory($category_id);
    }
  
    $list = null;
    if ($articles)
    {
      foreach($articles as $article)
      {
        $liClass = $article->isStartpage() ? ' class="rex-linkmap-startpage"' : '';

        $url = self::articleUrl($article);
        $list .= rex_treeRenderer::formatLi($article, $category_id, $this->context, $liClass, ' href="'. $url .'"');
        $list .= '</li>'. "\n";
      }
      
      if($list != '')
      {
        $list = '<ul>'. $list .'</ul>';
      }
    }
    return $list;
  }
  
  static protected function articleUrl(rex_ooArticle $article)
  {
    return 'javascript:insertLink(\'redaxo://'.$article->getId().'\',\''.addslashes(htmlspecialchars($article->getName())).'\');';
  }
}