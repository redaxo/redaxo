<?php

/**
 *
 * @package redaxo5
 */

$PREPOST[0] = 'PRE';
$PREPOST[1] = 'POST';
$ASTATUS[0] = 'ADD';
$ASTATUS[1] = 'EDIT';
$ASTATUS[2] = 'DELETE';

class rex_event_select extends rex_select
{
  function rex_event_select($options)
  {
    parent::__construct();

    $this->setMultiple(1);

    foreach($options as $key => $value)
      $this->addOption($value, $key);

    $this->setSize(count($options));
  }
}

$OUT = TRUE;

$action_id = rex_request('action_id', 'int');
$function  = rex_request('function', 'string');
$save      = rex_request('save', 'int');
$goon      = rex_request('goon', 'string');

$info = '';
$warning = '';
$warning_blck = '';

if ($function == 'delete')
{
  $del = rex_sql::factory();
//  $del->debugsql = true;
  $qry = 'SELECT
            *
          FROM
            '. rex::getTablePrefix() .'action a,
            '. rex::getTablePrefix() .'module_action ma
          LEFT JOIN
           '. rex::getTablePrefix() .'module m
          ON
            ma.module_id = m.id
          WHERE
            ma.action_id = a.id AND
            ma.action_id='. $action_id;
  $del->setQuery($qry); // module mit dieser aktion vorhanden ?
  if ($del->getRows() > 0)
  {
    $action_in_use_msg = '';
    $action_name = htmlspecialchars($del->getValue('a.name'));
    for ($i = 0; $i < $del->getRows(); $i++)
    {
      $action_in_use_msg .= '<li><a href="index.php?page=modules&amp;function=edit&amp;modul_id=' . $del->getValue('ma.module_id') . '">'. htmlspecialchars($del->getValue('m.name')) . ' ['. $del->getValue('ma.module_id') . ']</a></li>';
      $del->next();
    }

    if ($action_in_use_msg != '')
    {
      $warning_blck = '<ul>' . $action_in_use_msg . '</ul>';
    }

    $warning = rex_i18n::msg("action_cannot_be_deleted", $action_name);
  }
  else
  {
    $del->setQuery("DELETE FROM " . rex::getTablePrefix() . "action WHERE id='$action_id' LIMIT 1");
    $info = rex_i18n::msg("action_deleted");
  }
}

if ($function == "add" || $function == "edit")
{
  $name           = rex_post('name','string');
  $previewaction  = rex_post('previewaction','string');
  $presaveaction  = rex_post('presaveaction','string');
  $postsaveaction = rex_post('postsaveaction','string');

  $previewstatus  = 255;
  $presavestatus  = 255;
  $postsavestatus = 255;

  if ($save == "1")
  {
    $faction = rex_sql::factory();

    $previewstatus  = rex_post('previewstatus', 'array');
    $presavestatus  = rex_post('presavestatus', 'array');
    $postsavestatus = rex_post('postsavestatus', 'array');

    $previewmode = 0;
    foreach ($previewstatus as $status)
      $previewmode |= $status;

    $presavemode = 0;
    foreach ($presavestatus as $status)
      $presavemode |= $status;

    $postsavemode = 0;
    foreach ($postsavestatus as $status)
      $postsavemode |= $status;

    $faction->setTable(rex::getTablePrefix() . 'action');
    $faction->setValue('name', $name);
    $faction->setValue('preview', $previewaction);
    $faction->setValue('presave', $presaveaction);
    $faction->setValue('postsave', $postsaveaction);
    $faction->setValue('previewmode', $previewmode);
    $faction->setValue('presavemode', $presavemode);
    $faction->setValue('postsavemode', $postsavemode);

    try {
      if ($function == 'add')
      {
        $faction->addGlobalCreateFields();

        $faction->insert();
        $info = rex_i18n::msg('action_added');
      }
      else
      {
        $faction->addGlobalUpdateFields();
        $faction->setWhere(array('id' => $action_id));

        $faction->update();
        $info = rex_i18n::msg('action_updated');
      }
    } catch (rex_sql_exception $e) {
      $warning = $e->getMessage();
    }

    if (isset ($goon) and $goon != '')
    {
      $save = 'nein';
    }
    else
    {
      $function = '';
    }
  }

  if ($save != '1')
  {
    if ($function == 'edit')
    {
      $legend = rex_i18n::msg('action_edit') . ' [ID=' . $action_id . ']';

      $action = rex_sql::factory();
      $action->setQuery('SELECT * FROM '.rex::getTablePrefix().'action WHERE id='.$action_id);

      $name           = $action->getValue('name');
      $previewaction  = $action->getValue('preview');
      $presaveaction  = $action->getValue('presave');
      $postsaveaction = $action->getValue('postsave');
      $previewstatus  = $action->getValue('previewmode');
      $presavestatus  = $action->getValue('presavemode');
      $postsavestatus = $action->getValue('postsavemode');
    }
    else
    {
      $legend = rex_i18n::msg('action_create');
    }

    // PreView action macht nur bei add und edit Sinn da,
    // - beim Delete kommt keine View
    $options = array(
      1 => $ASTATUS[0] .' - '.rex_i18n::msg('action_event_add'),
      2 => $ASTATUS[1] .' - '.rex_i18n::msg('action_event_edit')
    );

    $sel_preview_status = new rex_event_select($options, false);
    $sel_preview_status->setName('previewstatus[]');
    $sel_preview_status->setId('previewstatus');

    $options = array(
      1 => $ASTATUS[0] .' - '.rex_i18n::msg('action_event_add'),
      2 => $ASTATUS[1] .' - '.rex_i18n::msg('action_event_edit'),
      4 => $ASTATUS[2] .' - '.rex_i18n::msg('action_event_delete')
    );

    $sel_presave_status = new rex_event_select($options);
    $sel_presave_status->setName('presavestatus[]');
    $sel_presave_status->setId('presavestatus');

    $sel_postsave_status = new rex_event_select($options);
    $sel_postsave_status->setName('postsavestatus[]');
    $sel_postsave_status->setId('postsavestatus');

    $allPreviewChecked = $previewstatus == 3 ? ' checked="checked"' : '';
    foreach (array (1,2,4) as $var)
    {
      if (($previewstatus & $var) == $var)
        $sel_preview_status->setSelected($var);
    }

    $allPresaveChecked = $presavestatus == 7 ? ' checked="checked"' : '';
    foreach (array (1,2,4) as $var)
    {
      if (($presavestatus & $var) == $var)
        $sel_presave_status->setSelected($var);
    }

    $allPostsaveChecked = $postsavestatus == 7 ? ' checked="checked"' : '';
    foreach (array (1,2,4) as $var)
    {
      if (($postsavestatus & $var) == $var)
        $sel_postsave_status->setSelected($var);
    }

    $btn_update = '';
    if ($function != 'add')
      $btn_update = '<input type="submit" name="goon" value="' . rex_i18n::msg('save_action_and_continue') . '"'. rex::getAccesskey(rex_i18n::msg('save_action_and_continue'), 'apply') .' />';

    if ($info != '')
      echo rex_view::info($info);

    if ($warning != '')
      echo rex_view::warning($warning);

    echo '
      <div class="rex-form" id="rex-form-action">
        <form action="index.php" method="post">
          <fieldset>
            <h2>' . $legend . ' </h2>

              <input type="hidden" name="page" value="modules" />
              <input type="hidden" name="subpage" value="actions" />
              <input type="hidden" name="function" value="' . $function . '" />
              <input type="hidden" name="save" value="1" />
              <input type="hidden" name="action_id" value="' . $action_id . '" />';


          $formElements = array();

            $n = array();
            $n['label'] = '<label for="name">' . rex_i18n::msg('action_name') . '</label>';
            $n['field'] = '<input type="text" id="name" name="name" value="' . htmlspecialchars($name) . '" />';
            $formElements[] = $n;

          $fragment = new rex_fragment();
          $fragment->setVar('elements', $formElements, false);
          echo $fragment->parse('form.tpl');

    echo '
          </fieldset>

          <fieldset>
            <h2>Preview-Action ['. rex_i18n::msg('action_mode_preview') .']</h2>';


          $formElements = array();

            $n = array();
            $n['label'] = '<label for="previewaction">' . rex_i18n::msg('input') . '</label>';
            $n['field'] = '<textarea cols="50" rows="6" name="previewaction" id="previewaction">' . htmlspecialchars($previewaction) . '</textarea>';
            $n['after'] = '<span class="rex-form-notice">' . rex_i18n::msg('action_hint') . '</span>';
            $formElements[] = $n;

            $n = array();
            $n['reverse'] = true;
            $n['label'] = '<label for="preview_allevents">'.rex_i18n::msg("action_event_all").'</label>';
            $n['field'] = '<input id="preview_allevents" type="checkbox" name="preview_allevents" '. $allPreviewChecked .' />';
            $formElements[] = $n;

            $n = array();
            $n['id'] = 'preview_events';
            $n['label'] = '<label for="previestatus">' . rex_i18n::msg('action_event') . '</label>';
            $n['field'] = $sel_preview_status->get();
            $n['after'] = '<span class="rex-form-notice">' . rex_i18n::msg('ctrl') . '</span>';
            $formElements[] = $n;

          $fragment = new rex_fragment();
          $fragment->setVar('elements', $formElements, false);
          echo $fragment->parse('form.tpl');

    echo '
          </fieldset>

          <fieldset>
            <h2>Presave-Action ['. rex_i18n::msg('action_mode_presave') .']</h2>';


          $formElements = array();

            $n = array();
            $n['label'] = '<label for="presaveaction">' . rex_i18n::msg('input') . '</label>';
            $n['field'] = '<textarea cols="50" rows="6" name="presaveaction" id="presaveaction">' . htmlspecialchars($presaveaction) . '</textarea>';
            $n['after'] = '<span class="rex-form-notice">' . rex_i18n::msg('action_hint') . '</span>';
            $formElements[] = $n;

            $n = array();
            $n['reverse'] = true;
            $n['label'] = '<label for="presave_allevents">'.rex_i18n::msg("action_event_all").'</label>';
            $n['field'] = '<input id="presave_allevents" type="checkbox" name="presave_allevents" '. $allPresaveChecked .' />';
            $formElements[] = $n;

            $n = array();
            $n['id'] = 'presave_events';
            $n['label'] = '<label for="presavestatus">' . rex_i18n::msg('action_event') . '</label>';
            $n['field'] = $sel_presave_status->get();
            $n['after'] = '<span class="rex-form-notice">' . rex_i18n::msg('ctrl') . '</span>';
            $formElements[] = $n;

          $fragment = new rex_fragment();
          $fragment->setVar('elements', $formElements, false);
          echo $fragment->parse('form.tpl');

    echo '
          </fieldset>


          <fieldset>
            <h2>Postsave-Action ['. rex_i18n::msg('action_mode_postsave') .']</h2>';


          $formElements = array();

            $n = array();
            $n['label'] = '<label for="postsaveaction">' . rex_i18n::msg('input') . '</label>';
            $n['field'] = '<textarea cols="50" rows="6" name="postsaveaction" id="postsaveaction">' . htmlspecialchars($postsaveaction) . '</textarea>';
            $n['after'] = '<span class="rex-form-notice">' . rex_i18n::msg('action_hint') . '</span>';
            $formElements[] = $n;

            $n = array();
            $n['reverse'] = true;
            $n['label'] = '<label for="postsave_allevents">'.rex_i18n::msg("action_event_all").'</label>';
            $n['field'] = '<input id="postsave_allevents" type="checkbox" name="postsave_allevents" '. $allPostsaveChecked .' />';
            $formElements[] = $n;

            $n = array();
            $n['id'] = 'postsave_events';
            $n['label'] = '<label for="postsavestatus">' . rex_i18n::msg('action_event') . '</label>';
            $n['field'] = $sel_postsave_status->get();
            $n['after'] = '<span class="rex-form-notice">' . rex_i18n::msg('ctrl') . '</span>';
            $formElements[] = $n;

          $fragment = new rex_fragment();
          $fragment->setVar('elements', $formElements, false);
          echo $fragment->parse('form.tpl');

    echo '
          </fieldset>

          <fieldset class="rex-form-action">';


          $formElements = array();

          $fragment = new rex_fragment();

            $n = array();
            $n['field'] = '<input type="submit" value="' . rex_i18n::msg('save_action_and_quit') . '"'. rex::getAccesskey(rex_i18n::msg('save_action_and_quit'), 'save') .' />';
            $formElements[] = $n;

            if ($btn_update != '')
            {
              $n = array();
              $n['field'] = $btn_update;
              $formElements[] = $n;

              $fragment->setVar('columns', 2, false);
            }

          $fragment->setVar('elements', $formElements, false);
          echo $fragment->parse('form.tpl');

    echo '
          </fieldset>
        </form>
      </div>

      <script type="text/javascript">
      <!--

      jQuery(function($) {
        var eventTypes = "#preview #presave #postsave";

        $(eventTypes.split(" ")).each(function() {
          var eventType = this;
          $(eventType+ "_allevents").click(function() {
            $(eventType+"_events").slideToggle("slow");
          });

          if($(eventType+"_allevents").is(":checked")) {
            $(eventType+"_events").hide();
          }
        });
      });

      -->
      </script>
      ';

    $OUT = false;
  }
}

if ($OUT)
{
  if ($info != '')
    echo rex_view::info($info);

  if ($warning != '')
    echo rex_view::warning($warning);

  if ($warning_blck != '')
    echo rex_view::warningBlock($warning_blck);

  // ausgabe actionsliste !
  echo '
    <table class="rex-table" id="rex-table-action" summary="' . rex_i18n::msg('action_summary') . '">
      <caption>' . rex_i18n::msg('action_caption') . '</caption>
      <thead>
        <tr>
          <th class="rex-icon"><a class="rex-ic-action rex-ic-add" href="index.php?page=modules&amp;subpage=actions&amp;function=add"'. rex::getAccesskey(rex_i18n::msg('action_create'), 'add') .'>' . rex_i18n::msg('action_create') . '</a></th>
          <th class="rex-small">ID</th>
          <th class="name">' . rex_i18n::msg('action_name') . '</th>
          <th class="preview">Preview-Event(s)</th>
          <th class="presave">Presave-Event(s)</th>
          <th class="postsave">Postsave-Event(s)</th>
          <th class="function">' . rex_i18n::msg('action_functions') . '</th>
        </tr>
      </thead>
    ';

  $sql = rex_sql::factory();
  $sql->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'action ORDER BY name');
  $rows = $sql->getRows();

  if($rows > 0)
  {
    echo '<tbody>'."\n";

    for ($i = 0; $i < $rows; $i++)
    {
      $previewmode = array ();
      $presavemode = array ();
      $postsavemode = array ();

      foreach (array (1 => 'ADD',2 => 'EDIT',4 => 'DELETE') as $var => $value)
        if (($sql->getValue('previewmode') & $var) == $var)
          $previewmode[] = $value;

      foreach (array (1 => 'ADD',2 => 'EDIT',4 => 'DELETE') as $var => $value)
        if (($sql->getValue('presavemode') & $var) == $var)
          $presavemode[] = $value;

      foreach (array (1 => 'ADD',2 => 'EDIT',4 => 'DELETE') as $var => $value)
        if (($sql->getValue('postsavemode') & $var) == $var)
          $postsavemode[] = $value;

      echo '
            <tr>
              <td class="rex-icon"><a class="rex-ic-action" href="index.php?page=modules&amp;subpage=actions&amp;action_id=' . $sql->getValue("id") . '&amp;function=edit" title="' . htmlspecialchars($sql->getValue("name")) . '">' . htmlspecialchars($sql->getValue("name")) . '</a></td>
              <td class="rex-small">' . $sql->getValue("id") . '</td>
              <td class="name"><a href="index.php?page=modules&amp;subpage=actions&amp;action_id=' . $sql->getValue("id") . '&amp;function=edit">' . htmlspecialchars($sql->getValue("name")) . '</a></td>
              <td class="preview">' . implode('/', $previewmode) . '</td>
              <td class="presave">' . implode('/', $presavemode) . '</td>
              <td class="postsave">' . implode('/', $postsavemode) . '</td>
              <td class="delete"><a href="index.php?page=modules&amp;subpage=actions&amp;action_id=' . $sql->getValue("id") . '&amp;function=delete" data-confirm="' . rex_i18n::msg('action_delete') . ' ?">' . rex_i18n::msg("action_delete") . '</a></td>
            </tr>
          ';

      $sql->next();
    }

    echo '</tbody>'."\n";
  }

  echo '
    </table>';
}
