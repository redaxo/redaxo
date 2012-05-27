<?php

/**
 * REX_MODULE_ID,
 * REX_SLICE_ID,
 * REX_CTYPE_ID
 *
 * @package redaxo5
 */

class rex_var_globals extends rex_var
{
  // --------------------------------- Actions

  public function getACRequestValues(array $REX_ACTION)
  {
    // determine event-type...
    $REX_ACTION = $this->getEventData($REX_ACTION);

    // Variablen hier einfuegen, damit sie in einer
    // Aktion abgefragt werden können
    $REX_ACTION['ARTICLE_ID'] = rex_request('article_id', 'int');
    $REX_ACTION['CLANG_ID']   = rex_request('clang', 'int');
    $REX_ACTION['CTYPE_ID']   = rex_request('ctype', 'int');
    $REX_ACTION['MODULE_ID']  = rex_request('module_id', 'int');

    return $REX_ACTION;
  }

  public function getACDatabaseValues(array $REX_ACTION, rex_sql $sql)
  {
    // determine event-type...
    $REX_ACTION = $this->getEventData($REX_ACTION);

    // Variablen hier einfuegen, damit sie in einer
    // Aktion abgefragt werden können
    $REX_ACTION['ARTICLE_ID'] = $this->getValue($sql, 'article_id');
    $REX_ACTION['CLANG_ID']   = $this->getValue($sql, 'clang');
    $REX_ACTION['CTYPE_ID']   = $this->getValue($sql, 'ctype');
    $REX_ACTION['MODULE_ID']  = $this->getValue($sql, 'modultyp_id');
    $REX_ACTION['SLICE_ID']   = $this->getValue($sql, 'id');

    return $REX_ACTION;
  }

  public function setACValues(rex_sql $sql, array $REX_ACTION)
  {
    $this->setValue($sql, 'id', $REX_ACTION['SLICE_ID']);
    $this->setValue($sql, 'ctype', $REX_ACTION['CTYPE_ID']);
    $this->setValue($sql, 'modultyp_id', $REX_ACTION['MODULE_ID']);
  }

  private function getEventData($REX_ACTION)
  {
    // SLICE ID im Update Mode setzen
    if ($this->isEditEvent()) {
      $REX_ACTION['EVENT'] = 'EDIT';
      $REX_ACTION['SLICE_ID'] = rex_request('slice_id', 'int');
    }
    // SLICE ID im Delete Mode setzen
    elseif ($this->isDeleteEvent()) {
      $REX_ACTION['EVENT'] = 'DELETE';
      $REX_ACTION['SLICE_ID'] = rex_request('slice_id', 'int');
    }
    // Im Add Mode 0 setze wg auto-increment
    else {
      $REX_ACTION['EVENT'] = 'ADD';
      $REX_ACTION['SLICE_ID'] = 0;
    }
    return $REX_ACTION;
  }

  // --------------------------------- Output

  public function getBEOutput(rex_sql $sql, $content)
  {
    // Modulabhängige Globale Variablen ersetzen
    $content = str_replace('REX_MODULE_ID', (int) $this->getValue($sql, 'modultyp_id'), $content);
    $content = str_replace('REX_SLICE_ID', (int) $this->getValue($sql, 'id'), $content);
    $content = str_replace('REX_CTYPE_ID', (int) $this->getValue($sql, 'ctype'), $content);

    return $content;
  }
}
