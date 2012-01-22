<?php

/**
 * Führt alle pre-view Aktionen eines Moduls aus
 *
 * @param int    $module_id  Id des Moduls
 * @param string $function   Funktion/Modus der Aktion
 * @param array  $REX_ACTION Array zum modifizieren der initialwerte
 *
 * @return array Das gefüllte REX_ACTION-Array
 */
function rex_execPreViewAction($module_id, $function, $REX_ACTION)
{
  $modebit = rex_getActionModeBit($function);

  $ga = rex_sql::factory();
  $ga->setQuery('SELECT a.id, preview FROM '.rex::getTablePrefix().'module_action ma,'. rex::getTablePrefix(). 'action a WHERE preview != "" AND ma.action_id=a.id AND module_id='. $module_id .' AND ((a.previewmode & '. $modebit .') = '. $modebit .')');

  foreach ($ga as $row)
  {
    $iaction = $row->getValue('preview');

    // ****************** VARIABLEN ERSETZEN
    foreach(rex_var::getVars() as $obj)
    {
      $iaction = $obj->getACOutput($REX_ACTION, $iaction);
    }

    require rex_stream::factory('action/'. $row->getValue('id') .'/preview', $iaction);
  }

  return $REX_ACTION;
}

/**
 * Führt alle pre-save Aktionen eines Moduls aus
 *
 * @param int    $module_id  Id des Moduls
 * @param string $function   Funktion/Modus der Aktion
 * @param array  $REX_ACTION Array zum speichern des Status
 *
 * @return array Ein Array welches eine Meldung sowie das gefüllte REX_ACTION-Array beinhaltet
 */
function rex_execPreSaveAction($module_id, $function, $REX_ACTION)
{
  $modebit = rex_getActionModeBit($function);
  $messages = array();

  $ga = rex_sql::factory();
  $ga->setQuery('SELECT a.id, presave FROM ' . rex::getTablePrefix() . 'module_action ma,' . rex::getTablePrefix() . 'action a WHERE presave != "" AND ma.action_id=a.id AND module_id=' . $module_id . ' AND ((a.presavemode & ' . $modebit . ') = ' . $modebit . ')');

  foreach($ga as $row)
  {
    $REX_ACTION['MSG'] = '';
    $iaction = $row->getValue('presave');

    // *********************** WERTE ERSETZEN
    foreach (rex_var::getVars() as $obj)
    {
      $iaction = $obj->getACOutput($REX_ACTION, $iaction);
    }

    require rex_stream::factory('action/'. $row->getValue('id') .'/presave', $iaction);

    if ($REX_ACTION['MSG'] != '')
    $messages[] = $REX_ACTION['MSG'];
  }
  return array(implode(' | ', $messages), $REX_ACTION);
}

/**
 * Führt alle post-save Aktionen eines Moduls aus
 *
 * @param int    $module_id  Id des Moduls
 * @param string $function   Funktion/Modus der Aktion
 * @param array  $REX_ACTION Array zum speichern des Status
 *
 * @return string Eine Meldung
 */
function rex_execPostSaveAction($module_id, $function, $REX_ACTION)
{
  $modebit = rex_getActionModeBit($function);
  $messages = array();

  $ga = rex_sql::factory();
  $ga->setQuery('SELECT a.id, postsave FROM ' . rex::getTablePrefix() . 'module_action ma,' . rex::getTablePrefix() . 'action a WHERE postsave != "" AND ma.action_id=a.id AND module_id=' . $module_id . ' AND ((a.postsavemode & ' . $modebit . ') = ' . $modebit . ')');

  foreach ($ga as $row)
  {
    $REX_ACTION['MSG'] = '';
    $iaction = $row->getValue('postsave');

    // ***************** WERTE ERSETZEN UND POSTACTION AUSFÜHREN
    foreach (rex_var::getVars() as $obj)
    {
      $iaction = $obj->getACOutput($REX_ACTION, $iaction);
    }

    require rex_stream::factory('action/'. $row->getValue('id') .'/postsave', $iaction);

    if ($REX_ACTION['MSG'] != '')
    $messages[] = $REX_ACTION['MSG'];
  }
  return implode(' | ', $messages);
}

/**
 * Übersetzt den Modus in das dazugehörige Bitwort
 *
 * @param string $function   Funktion/Modus der Aktion
 *
 * @return int Ein Bitwort
 */
function rex_getActionModeBit($function)
{
  if ($function == 'edit')
  $modebit = '2'; // pre-action and edit
  elseif ($function == 'delete')
  $modebit = '4'; // pre-action and delete
  else
  $modebit = '1'; // pre-action and add

  return $modebit;
}