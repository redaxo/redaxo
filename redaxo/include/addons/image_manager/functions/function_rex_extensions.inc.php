<?php

function rex_imanager_handle_form_control_fields($params)
{
  $controlFields = $params['subject'];
  $form = $params['form'];
  $sql  = $form->getSql();
  
  // remove delete button on internal types (status == 1)
  if($sql->getRows() > 0 && $sql->hasValue('status') && $sql->getValue('status') == 1)
  {
    $controlFields['delete'] = '';
  }
  return $controlFields;
}