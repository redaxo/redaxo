<?php

class rex_sitemap_category_tree extends rex_linkmap_tree_renderer
{
  private $context;

  public function __construct(rex_context $context)
  {
    $this->context = $context;
  }

  public function getTree($category_id)
  {
    // if not, let the structure as is, by providing a remembered id
    if ($category_id <= 0) {
      $category_id = rex_request::session('tree_category_id', 'int');
    } else {
      rex_request::setSession('tree_category_id', $category_id);
    }
    return parent::getTree($category_id);
  }

  protected function treeItem(rex_ooCategory $cat, $liClasses, $linkClasses, $subHtml)
  {
    $linkClasses .= '';

    if ($liClasses != '')
      $liClasses = ' class="' . rtrim($liClasses) . '"';

    if ($linkClasses != '')
      $linkClasses = ' class="' . rtrim($linkClasses) . '"';

    $label = self::formatLabel($cat);

    $li = '';
    $li .= '<li' . $liClasses . ' cat-id="' . $cat->getId() . '" parent-id="' . $cat->getParentId() . '" prior="' . $cat->getPriority() . '">';
    $li .= '<a' . $linkClasses . ' href="' . $this->context->getUrl(array('rex-api-call' => 'sitemap_tree', 'toggle_category_id' => $cat->getId())) . '">&nbsp;</a>';
    $li .= '<a href="' . $this->context->getUrl(array('category_id' => $cat->getId())) . '">' . htmlspecialchars($label) . '</a>';
    $li .= $subHtml;
    $li .= '</li>';

    return $li;
  }
}
