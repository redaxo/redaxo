<?php

class rex_linkmap_categoryTree extends rex_linkmap_treeRenderer {
  private $context;
  
  public function __construct(rex_context $context) {
    $this->context = $context;
  }
    
  protected function treeItem(rex_ooCategory $cat, $liClasses, $linkClasses, $subHtml) {
    
    $label = self::formatLabel($cat);

    $li = '';
    $li .= '      <li'.$liClasses.'>';
    $li .= '<a'.$linkClasses.' href="'. $this->context->getUrl(array('category_id' => $cat->getId())).'">'.htmlspecialchars($label).'</a>';
    $li .= $subHtml;
    $li .= '</li>'. "\n";
    
    return $li;
  }
}

class rex_linkmap_articleList extends rex_linkmap_articleListRenderer {
  private $context;

  public function __construct(rex_context $context) {
    $this->context = $context;
  }
    
  protected function listItem(rex_ooArticle $article, $category_id)
  {
    $liClass = $article->isStartpage() ? ' class="rex-linkmap-startpage"' : '';
    $url     = 'javascript:insertLink(\'redaxo://'.$article->getId().'\',\''.addslashes(htmlspecialchars($article->getName())).'\');';
    return rex_linkmap_treeRenderer::formatLi($article, $category_id, $this->context, $liClass, ' href="'. $url .'"'). '</li>'. "\n";
  }
}