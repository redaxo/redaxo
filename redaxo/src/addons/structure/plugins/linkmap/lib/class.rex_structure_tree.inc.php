<?php

class rex_structure_tree
{
  private $context;
  
  public function __construct(rex_context $context) {
    $this->context = $context;
  }
  
  
  /**
   * Returns the markup of a tree structure, with $children as root categories and respecing $activeTreeIds as the active path.
   * 
   * @param array $children A array of rex_ooCategory objects representing the top level objects
   * @param array $tree A array of ids representing the active path
   * @return string the rendered markup
   */
  public function renderTree(array $children, array $activeTreeIds)
  {
    $ul = '';
    if(is_array($children))
    {
      $li = '';
      $ulclasses = '';
      if (count($children)==1) $ulclasses .= 'rex-children-one ';
      foreach($children as $cat){
        $cat_children = $cat->getChildren();
        $cat_id = $cat->getId();
        $liclasses = '';
        $linkclasses = '';
        $sub_li = '';
        if (count($cat_children)>0) {
          $liclasses .= 'rex-children ';
          $linkclasses .= 'rex-linkmap-is-not-empty ';
        }
  
        if (next($children)== null ) $liclasses .= 'rex-children-last ';
        $linkclasses .= $cat->isOnline() ? 'rex-online ' : 'rex-offline ';
        if (is_array($activeTreeIds) && in_array($cat_id,$activeTreeIds))
        {
          $sub_li = $this->renderTree($cat_children, $activeTreeIds);
          $liclasses .= 'rex-active ';
          $linkclasses .= 'rex-active ';
        }
  
        if($liclasses != '')
          $liclasses = ' class="'. rtrim($liclasses) .'"';
  
        if($linkclasses != '')
          $linkclasses = ' class="'. rtrim($linkclasses) .'"';
  
        $label = self::formatLabel($cat);
  
        $li .= '      <li'.$liclasses.'>';
        $li .= '<a'.$linkclasses.' href="'. $this->context->getUrl(array('category_id' => $cat_id)).'">'.htmlspecialchars($label).'</a>';
        $li .= $sub_li;
        $li .= '</li>'. "\n";
      }
  
      if($ulclasses != '')
        $ulclasses = ' class="'. rtrim($ulclasses) .'"';
  
      if ($li!='') $ul = '<ul'.$ulclasses.'>'."\n".$li.'</ul>'. "\n";
    }
    return $ul;
  }
  
  static public function formatLabel(rex_ooRedaxo $OOobject)
  {
    $label = $OOobject->getName();
  
    if(trim($label) == '')
    $label = '&nbsp;';
  
    if (rex::getUser()->hasPerm('advancedMode[]'))
    $label .= ' ['. $OOobject->getId() .']';
  
    if(rex_ooArticle::isValid($OOobject) && !$OOobject->hasTemplate())
    $label .= ' ['.rex_i18n::msg('lmap_has_no_template').']';
  
    return $label;
  }
  
  static public function formatLi(rex_ooRedaxo $OOobject, $current_category_id, rex_context $context, $liAttr = '', $linkAttr = '')
  {
    $liAttr .= $OOobject->getId() == $current_category_id ? ' id="rex-linkmap-active"' : '';
    $linkAttr .= ' class="'. ($OOobject->isOnline() ? 'rex-online' : 'rex-offine'). '"';
  
    if(strpos($linkAttr, ' href=') === false)
    $linkAttr .= ' href="'. $context->getUrl(array('category_id' => $OOobject->getId())) .'"';
  
    $label = self::formatLabel($OOobject);
  
    return '<li'. $liAttr .'><a'. $linkAttr .'>'. htmlspecialchars($label) . '</a>';
  }
}