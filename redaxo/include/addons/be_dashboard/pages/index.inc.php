<?php

/**
 * Backenddashboard Addon
 * 
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

include $REX["INCLUDE_PATH"]."/layout/top.php";

rex_title($I18N->msg('dashboard'), '');

$contentFound = false;

$component = new rex_notification_component();
$content   =& $component->get();
if($content != '')
{
  $contentFound = true;
  
	echo '<div class="rex-dashboard-section rex-dashboard-1col rex-dashboard-notifications">
	        <div class="rex-dashboard-column rex-dashboard-column-first">
            '.$content.'
          </div>
        </div>';
}

// ----- EXTENSION POINT
$dashboard_components = array();
$dashboard_components = rex_register_extension_point('DASHBOARD_COMPONENT', $dashboard_components);

// ------------ sort components by block and format
$components = array();
foreach($dashboard_components as $index => $component)
{
  if(rex_dashboard_component::isValid($component) && $component->checkPermission())
  {
    $block  = $component->getBlock();
    $format = $component->getFormat();
    
    if($block == '' && $format == 'half')
    {
      $block = $I18N->msg('dashboard_component_block_misc');
    }

    if(!isset($components[$format]))
    {
      $components[$format] = array();
    }
    if(!isset($components[$format][$block]))
    {
      $components[$format][$block] = array();
    }
    
    $components[$format][$block][] = $component;
  }
  unset($dashboard_components[$index]);
}

// ------------ show components
foreach($components as $format => $componentBlocks)
{
  foreach($componentBlocks as $block => $componentBlock)
  {
    if($format == 'full')
    {
      echo '<div class="rex-dashboard-section rex-dashboard-1col rex-dashboard-components">
              <div class="rex-dashboard-column rex-dashboard-column-first">';
      
      if($block != '')
      {
          echo '<h2 class="rex-dashboard-hl1">'. $block .'</h2>';
      }
      
      foreach($componentBlock as $component)
      {
        $cnt =& $component->get();
        if($cnt != '')
        {
          echo $cnt;
          $contentFound = true;
        }
      }
      
      echo '  </div>
            </div>';
    }
    else if ($format == 'half')
    {
      $numComponents = count($componentBlock);
      $componentsPerCol = ceil($numComponents / 2);
      
      echo '<div class="rex-dashboard-section rex-dashboard-2col rex-dashboard-components">';
      
      if($block != '')
      {
          echo '<h2 class="rex-dashboard-hl1">'. $block .'</h2>';
      }
      
      // show first column
      $i = 0;
      echo '  <div class="rex-dashboard-column rex-dashboard-column-first">';
      foreach($componentBlock as $index => $component)
      {
        $cnt =& $component->get();
        if($cnt != '')
        {
          echo $cnt;
          $contentFound = true;
        }
        unset($componentBlock[$index]);
        
        $i++;
        if($i == $componentsPerCol) break;
      }
      echo '</div>';
      // /show first column
      
      // show second column
      echo '<div class="rex-dashboard-column">';
      foreach($componentBlock as $index => $component)
      {
        $cnt =& $component->get();
        if($cnt != '')
        {
          echo $cnt;
          $contentFound = true;
        }
        unset($componentBlock[$index]);
      }
      echo '</div>';
      // /show second column
      
      echo '</div>';
    }
  }
  unset($components[$format][$block]);
}

if(!$contentFound)
{
  echo rex_warning($I18N->msg('dashboard_no_content'));
}

include $REX["INCLUDE_PATH"]."/layout/bottom.php";