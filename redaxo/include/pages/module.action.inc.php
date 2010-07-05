<?php

/**
 *
 * @package redaxo4
 * @version svn:$Id$
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
    global $I18N;

    parent::rex_select();

    $this->setMultiple(1);

    foreach($options as $key => $value)
      $this->addOption($value, $key);

    $this->setSize(count($options));
  }
}

$OUT = TRUE;

$action_id = rex_request('action_id', 'rex-action-id');
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
            '. $REX['TABLE_PREFIX'] .'action a,
            '. $REX['TABLE_PREFIX'] .'module_action ma
          LEFT JOIN
           '. $REX['TABLE_PREFIX'] .'module m
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
      $action_in_use_msg .= '<li><a href="index.php?page=module&amp;function=edit&amp;modul_id=' . $del->getValue('ma.module_id') . '">'. htmlspecialchars($del->getValue('m.name')) . ' ['. $del->getValue('ma.module_id') . ']</a></li>';
      $del->next();
    }

    if ($action_in_use_msg != '')
    {
      $warning_blck = '<ul>' . $action_in_use_msg . '</ul>';
    }

    $warning = $I18N->msg("action_cannot_be_deleted", $action_name);
  }
  else
  {
    $del->setQuery("DELETE FROM " . $REX['TABLE_PREFIX'] . "action WHERE id='$action_id' LIMIT 1");
    $info = $I18N->msg("action_deleted");
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

    $faction->setTable($REX['TABLE_PREFIX'] . 'action');
    $faction->setValue('name', $name);
    $faction->setValue('preview', $previewaction);
    $faction->setValue('presave', $presaveaction);
    $faction->setValue('postsave', $postsaveaction);
    $faction->setValue('previewmode', $previewmode);
    $faction->setValue('presavemode', $presavemode);
    $faction->setValue('postsavemode', $postsavemode);

    if ($function == 'add')
    {
      $faction->addGlobalCreateFields();

      if($faction->insert())
        $info = $I18N->msg('action_added');
      else
        $warning = $faction->getError();
    }
    else
    {
      $faction->addGlobalUpdateFields();
      $faction->setWhere('id=' . $action_id);

      if($faction->update())
        $info = $I18N->msg('action_updated');
      else
        $warning = $faction->getError();
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
      $legend = $I18N->msg('action_edit') . ' [ID=' . $action_id . ']';

      $action = rex_sql::factory();
      $action->setQuery('SELECT * FROM '.$REX['TABLE_PREFIX'].'action WHERE id='.$action_id);

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
      $legend = $I18N->msg('action_create');
    }

    // PreView action macht nur bei add und edit Sinn da,
    // - beim Delete kommt keine View
    $options = array(
      1 => $ASTATUS[0] .' - '.$I18N->msg('action_event_add'),
      2 => $ASTATUS[1] .' - '.$I18N->msg('action_event_edit')
    );

    $sel_preview_status = new rex_event_select($options, false);
    $sel_preview_status->setName('previewstatus[]');
    $sel_preview_status->setId('previewstatus');
		$sel_preview_status->setStyle('class="rex-form-select"');

    $options = array(
      1 => $ASTATUS[0] .' - '.$I18N->msg('action_event_add'),
      2 => $ASTATUS[1] .' - '.$I18N->msg('action_event_edit'),
      4 => $ASTATUS[2] .' - '.$I18N->msg('action_event_delete')
    );

    $sel_presave_status = new rex_event_select($options);
    $sel_presave_status->setName('presavestatus[]');
    $sel_presave_status->setId('presavestatus');
		$sel_presave_status->setStyle('class="rex-form-select"');

    $sel_postsave_status = new rex_event_select($options);
    $sel_postsave_status->setName('postsavestatus[]');
    $sel_postsave_status->setId('postsavestatus');
		$sel_postsave_status->setStyle('class="rex-form-select"');
		
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
      $btn_update = '<input type="submit" class="rex-form-submit rex-form-submit-2" name="goon" value="' . $I18N->msg('save_action_and_continue') . '"'. rex_accesskey($I18N->msg('save_action_and_continue'), $REX['ACKEY']['APPLY']) .' />';

    if ($info != '')
      echo rex_info($info);

    if ($warning != '')
      echo rex_warning($warning);

    echo '
      <div class="rex-form rex-action-editmode">
        <form action="index.php" method="post">
          <fieldset class="rex-form-col-1">
            <legend>' . $legend . ' </legend>

           	<div class="rex-form-wrapper">
	          	<input type="hidden" name="page" value="module" />
  	        	<input type="hidden" name="subpage" value="actions" />
          		<input type="hidden" name="function" value="' . $function . '" />
		          <input type="hidden" name="save" value="1" />
    		      <input type="hidden" name="action_id" value="' . $action_id . '" />
    		      
    		      <div class="rex-form-row">
			          <p class="rex-form-col-a rex-form-text">
      			    	<label for="name">' . $I18N->msg('action_name') . '</label>
			            <input class="rex-form-text" type="text" size="10" id="name" name="name" value="' . htmlspecialchars($name) . '" />
      			    </p>
      			  </div>
      			  
              <div class="rex-clearer"></div>
      			</div>
          </fieldset>

          <fieldset class="rex-form-col-1">
            <legend>Preview-Action ['. $I18N->msg('action_mode_preview') .']</legend>
           	<div class="rex-form-wrapper">
    		      <div class="rex-form-row">
			          <p class="rex-form-col-a rex-form-textarea">
			          	<label for="previewaction">' . $I18N->msg('input') . '</label>
			          	<textarea class="rex-txtr-cd" cols="50" rows="6" name="previewaction" id="previewaction">' . htmlspecialchars($previewaction) . '</textarea>
			          	<span class="rex-form-notice">' . $I18N->msg('action_hint') . '</span>
			          </p>
			        </div>
			         
			        <div class="rex-form-row">
                <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
                  <input class="rex-form-checkbox" id="preview_allevents" type="checkbox" name="preview_allevents" '. $allPreviewChecked .' />
                  <label for="preview_allevents">'.$I18N->msg("action_event_all").'</label> 
                </p>
                <div id="preview_events">
                  <p class="rex-form-col-a rex-form-select">
  			         		<label for="previestatus">' . $I18N->msg('action_event') . '</label>
  			         		' . $sel_preview_status->get() . '
  			         		<span class="rex-form-notice">' . $I18N->msg('ctrl') . '</span>
  			         	</p>
			         	</div>
			        </div>
			        
              <div class="rex-clearer"></div>
			      </div>
	        </fieldset>
	        
          <fieldset class="rex-form-col-1">
            <legend>Presave-Action ['. $I18N->msg('action_mode_presave') .']</legend>
           	<div class="rex-form-wrapper">
    		      <div class="rex-form-row">
			          <p class="rex-form-col-a rex-form-textarea">
			          	<label for="presaveaction">' . $I18N->msg('input') . '</label>
			          	<textarea class="rex-form-textarea" cols="50" rows="6" name="presaveaction" id="presaveaction">' . htmlspecialchars($presaveaction) . '</textarea>
			          	<span class="rex-form-notice">' . $I18N->msg('action_hint') . '</span>
			          </p>
			        </div>
			         
			        <div class="rex-form-row">
                <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
                  <input class="rex-form-checkbox" id="presave_allevents" type="checkbox" name="presave_allevents" '. $allPresaveChecked .' />
                  <label for="presave_allevents">'.$I18N->msg("action_event_all").'</label> 
                </p>
                <div id="presave_events">
			            <p class="rex-form-col-a rex-form-select">
  			            <label for="presavestatus">' . $I18N->msg('action_event') . '</label>
        			      ' . $sel_presave_status->get() . '
        			      <span class="rex-form-notice">' . $I18N->msg('ctrl') . '</span>
      			      </p>
      			    </div>
      			  </div>
      			  
              <div class="rex-clearer"></div>
      			</div>
          </fieldset>
          
	        
          <fieldset class="rex-form-col-1">
            <legend class="rex-lgnd">Postsave-Action ['. $I18N->msg('action_mode_postsave') .']</legend>
           	<div class="rex-form-wrapper">
    		      <div class="rex-form-row">
			          <p class="rex-form-col-a rex-form-textarea">
			          	<label for="postsaveaction">' . $I18N->msg('input') . '</label>
			          	<textarea class="rex-form-textarea" cols="50" rows="6" name="postsaveaction" id="postsaveaction">' . htmlspecialchars($postsaveaction) . '</textarea>
			          	<span class="rex-form-notice">' . $I18N->msg('action_hint') . '</span>
			          </p>
			        </div>
			         
			        <div class="rex-form-row">
                <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
                  <input class="rex-form-checkbox" id="postsave_allevents" type="checkbox" name="postsave_allevents" '. $allPostsaveChecked .' />
                  <label for="postsave_allevents">'.$I18N->msg("action_event_all").'</label> 
                </p>
                <div id="postsave_events">
			            <p class="rex-form-col-a rex-form-select">
			         		  <label for="postsavestatus">' . $I18N->msg('action_event') . '</label>
  			         		' . $sel_postsave_status->get() . '
	  		         		<span class="rex-form-notice">' . $I18N->msg('ctrl') . '</span>
		  	         	</p>
		  	        </div>
			        </div>
			        
              <div class="rex-clearer"></div>
			      </div>
			    </fieldset>
			    
          <fieldset class="rex-form-col-1">
           	<div class="rex-form-wrapper">
    		      <div class="rex-form-row">
			    			<p class="rex-form-col-a rex-form-submit">
			    				<input class="rex-form-submit" type="submit" value="' . $I18N->msg('save_action_and_quit') . '"'. rex_accesskey($I18N->msg('save_action_and_quit'), $REX['ACKEY']['SAVE']) .' />
		    				' . $btn_update . '
			    			</p>
			    		</div>
			    	</div>
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
    echo rex_info($info);

  if ($warning != '')
    echo rex_warning($warning);

  if ($warning_blck != '')
    echo rex_warning_block($warning_blck);

  // ausgabe actionsliste !
  echo '
    <table class="rex-table" summary="' . $I18N->msg('action_summary') . '">
      <caption>' . $I18N->msg('action_caption') . '</caption>
      <colgroup>
        <col width="40" />
        <col width="40" />
        <col width="*" />
        <col width="120" />
        <col width="120" />
        <col width="120" />
        <col width="153" />
      </colgroup>
      <thead>
        <tr>
          <th class="rex-icon"><a class="rex-i-element rex-i-action-add" href="index.php?page=module&amp;subpage=actions&amp;function=add"'. rex_accesskey($I18N->msg('action_create'), $REX['ACKEY']['ADD']) .'><span class="rex-i-element-text">' . $I18N->msg('action_create') . '</span></a></th>
          <th class="rex-small">ID</th>
          <th>' . $I18N->msg('action_name') . '</th>
          <th>Preview-Event(s)</th>
          <th>Presave-Event(s)</th>
          <th>Postsave-Event(s)</th>
          <th>' . $I18N->msg('action_functions') . '</th>
        </tr>
      </thead>
    ';

  $sql = rex_sql::factory();
  $sql->setQuery('SELECT * FROM ' . $REX['TABLE_PREFIX'] . 'action ORDER BY name');
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
              <td class="rex-icon"><a class="rex-i-element rex-i-action" href="index.php?page=module&amp;subpage=actions&amp;action_id=' . $sql->getValue("id") . '&amp;function=edit" title="' . htmlspecialchars($sql->getValue("name")) . '"><span class="rex-i-element-text">' . htmlspecialchars($sql->getValue("name")) . '</span></a></td>
              <td class="rex-small">' . $sql->getValue("id") . '</td>
              <td><a href="index.php?page=module&amp;subpage=actions&amp;action_id=' . $sql->getValue("id") . '&amp;function=edit">' . htmlspecialchars($sql->getValue("name")) . '</a></td>
              <td>' . implode('/', $previewmode) . '</td>
              <td>' . implode('/', $presavemode) . '</td>
              <td>' . implode('/', $postsavemode) . '</td>
              <td><a href="index.php?page=module&amp;subpage=actions&amp;action_id=' . $sql->getValue("id") . '&amp;function=delete" onclick="return confirm(\'' . $I18N->msg('action_delete') . ' ?\')">' . $I18N->msg("action_delete") . '</a></td>
            </tr>
          ';

      $sql->next();
    }

    echo '</tbody>'."\n";
  }

  echo '
    </table>';
}