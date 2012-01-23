<?php

$out = '';

$columns = isset($this->columns) ? (int)$this->columns : 1;
$columns_flag = $columns >= 2 ? true : false;
$columns_c = 0;


foreach($this->elements as $element)
{
  if ($columns_flag)
  {
    $columns_c++;
    
    $column_class = '';
    if ($columns_c == 1)
    {
      $out .= '<div class="rex-grid'.$columns.'col">';
      $column_class = ' rex-first';
      $columns_grid_open = true;
    }
    if ($columns_c == $columns)
    {
      $column_class = ' rex-last';
    }
      
    $out .= '<div class="rex-column'.$column_class.'">';
  }
  
  $id     = isset($element['id']) ? ' id="'.$element['id'].'"' : '';
  $label  = isset($element['label']) ? $element['label'] : '';
  $field  = isset($element['field']) ? $element['field'] : '';
  $before = isset($element['before']) ? $element['before'] : '';
  $after  = isset($element['after']) ? $element['after'] : '';
  
  
  if (isset($element['reverse']) && $element['reverse'])
  {
    $out .= '<div class="rex-form-data rex-form-reverse"'.$id.'>';
    $out .= $before.$field.$label.$after;
    $out .= '</div>';
  }
  else
  {
    $out .= '<div class="rex-form-data"'.$id.'>';
    $out .= $before.$label.$field.$after;  
    $out .= '</div>';
  }
  
  
  
  if ($columns_flag)
  {
    $out .= '</div>';
    
    if ($columns_c == $columns)
    {
      $out .= '</div>';
      $columns_c = 0;
      $columns_grid_open = false;
    }
  }
  
}

if ($columns_flag && $columns_grid_open)
  $out .= '</div>';

echo $out;
?>