<?php

/**
 * Tree Structure PlugIn
 * @package redaxo5
 */

if (rex::isBackend() && rex::getUser())
{
  
  rex_perm::register('structure_tree[off]');
  if(!rex::getUser()->hasPerm("structure_tree[off]"))
  {
    rex_extension::register('PAGE_STRUCTURE_HEADER_PRE', function($params){
        $tree = new rex_structure_tree($params["context"]);
        $params['subject'] = $tree->getTree();
        $params['subject'] .= "\n  ".
          '<script type="text/javascript" src="'. rex_path::pluginAssets('structure', 'tree', 'tree.js') .'"></script>';
        
        return $params['subject'];
      }
    );
  }

}