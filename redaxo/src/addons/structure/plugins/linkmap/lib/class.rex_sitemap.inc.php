<?php

class rex_sitemap_categoryTree extends rex_linkmap_treeRenderer {
  private $context;
  
  public function __construct(rex_context $context) {
    $this->context = $context;
  }
  
  protected function treeItem(rex_ooCategory $cat, $liClasses, $linkClasses, $subHtml) {
    $label = self::formatLabel($cat);
    
    $li = '';
    $li .= '<li'.$liClasses.'>';
    $li .= '<a'.$linkClasses.' style="float: left" href="'. $this->context->getUrl(array('toggle_category_id' => $cat->getId())).'">&nbsp;</a>';
    $li .= '<a href="'. $this->context->getUrl(array('category_id' => $cat->getId())).'">'.htmlspecialchars($label).'</a>';
    $li .= $subHtml;
    $li .= '</li>'. "\n";
    
    return $li;
  }
}

class rex_sitemap_articleList extends rex_linkmap_articleListRenderer {
  private $context;

  public function __construct(rex_context $context) {
    $this->context = $context;
  }
    
  protected function listItem(rex_ooArticle $article, $category_id)
  {
    return 'TOODDOO';
  }
}