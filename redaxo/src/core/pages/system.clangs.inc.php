<?php

/**
 * Verwaltung der Content Sprachen
 * @package redaxo5
 */

$content = '';

// -------------- Defaults
$clang_id   = rex_request('clang_id', 'int');
$clang_name = rex_request('clang_name', 'string');
$func       = rex_request('func', 'string');

// -------------- Form Submits
$add_clang_save  = rex_post('add_clang_save', 'boolean');
$edit_clang_save = rex_post('edit_clang_save', 'boolean');

$warning = '';
$info = '';

// ----- delete clang
if ($func == 'deleteclang' && $clang_id != "")
{
  if(rex_clang::exists($clang_id))
  {
    rex_clang_service::deleteCLang($clang_id);
    $info = rex_i18n::msg('clang_deleted');
    $func = '';
    unset ($clang_id);
  }
}

// ----- add clang
if ($add_clang_save)
{
  if ($clang_name != '' && $clang_id > 0)
  {
    if (!rex_clang::exists($clang_id))
    {
      $info = rex_i18n::msg('clang_created');
      rex_clang_service::addCLang($clang_id, $clang_name);
      unset ($clang_id);
       $func = '';
    }
    else
    {
      $warning = rex_i18n::msg('id_exists');
      $func = 'addclang';
    }
  }
  else
  {
    $warning = rex_i18n::msg('enter_name');
    $func = 'addclang';
  }

}
elseif ($edit_clang_save)
{
  if (rex_clang::exists($clang_id))
  {
    rex_clang_service::editCLang($clang_id, $clang_name);
    $info = rex_i18n::msg('clang_edited');
    $func = '';
    unset ($clang_id);
  }
}

// seltype
$sel = new rex_select;
$sel->setName('clang_id');
$sel->setStyle('class="rex-form-select"');
$sel->setId('rex-form-clang-id');
$sel->setSize(1);
$remaingClangs = array_diff(range(0, rex::getProperty('maxlogins')-1), rex_clang::getAllIds());
foreach ($remaingClangs as $clang)
{
  $sel->addOption($clang, $clang);
}

// no remaing clang-ids
if(empty($remaingClangs))
{
  $warning = rex_i18n::msg('clang_no_left');
}

if ($info != '')
  $content .= rex_view::info($info);

if ($warning != '')
  $content .= rex_view::warning($warning);


$content .= '
      <div class="rex-form" id="rex-form-system-language">
      <form action="index.php#clang" method="post">
    ';

if ($func == 'addclang' || $func == 'editclang')
{
  $legend = $func == 'addclang' ? rex_i18n::msg('clang_add') : rex_i18n::msg('clang_edit');
  $content .= '
        <fieldset>
          <legend>'.$legend.'</legend>
          <input type="hidden" name="page" value="system" />
          <input type="hidden" name="subpage" value="lang" />
          <input type="hidden" name="clang_id" value="'.$clang_id.'" />
      ';
}


$content .= '
    <table class="rex-table" summary="'.rex_i18n::msg('clang_summary').'">
      <caption>'.rex_i18n::msg('clang_caption').'</caption>
      <colgroup>
        <col width="40" />
        <col width="40" />
        <col width="*" />
        <col width="153" />
      </colgroup>
      <thead>
        <tr>
          <th class="rex-small"><a class="rex-i-element rex-i-clang-add" href="index.php?page=system&amp;subpage=lang&amp;func=addclang#clang"'. rex::getAccesskey(rex_i18n::msg('clang_add'), 'add') .'><span class="rex-i-element-text">'.rex_i18n::msg('clang_add').'</span></a></th>
          <th class="rex-small">ID</th>
          <th>'.rex_i18n::msg('clang_name').'</th>
          <th>'.rex_i18n::msg('clang_function').'</th>
        </tr>
      </thead>
      <tbody>
  ';

// Add form
if ($func == 'addclang')
{
  //ggf wiederanzeige des add forms, falls ungueltige id uebermittelt
  $content .= '
        <tr class="rex-table-row-activ">
          <td class="rex-small"><span class="rex-i-element rex-i-clang"><span class="rex-i-element-text">'.htmlspecialchars($clang_name).'</span></span></td>
          <td class="rex-small">'.$sel->get().'</td>
          <td><input class="rex-form-text" type="text" id="rex-form-clang-name" name="clang_name" value="'.htmlspecialchars($clang_name).'" /></td>
          <td><input class="rex-form-submit" type="submit" name="add_clang_save" value="'.rex_i18n::msg('clang_add').'"'. rex::getAccesskey(rex_i18n::msg('clang_add'), 'save') .' /></td>
        </tr>
      ';
}
foreach (rex_clang::getAll() as $lang_id => $lang)
{

  $add_td = '';
  $add_td = '<td class="rex-small">'.$lang_id.'</td>';

  $delLink = rex_i18n::msg('clang_delete');
  if($lang_id == 0)
   $delLink = '<span class="rex-strike">'. $delLink .'</span>';
  else
    $delLink = '<a href="index.php?page=system&amp;subpage=lang&amp;func=deleteclang&amp;clang_id='.$lang_id.'" data-confirm="'.rex_i18n::msg('delete').' ?">'. $delLink .'</a>';

  // Edit form
  if ($func == "editclang" && $clang_id == $lang_id)
  {
    $content .= '
          <tr class="rex-trow-actv">
            <td class="rex-small"><span class="rex-i-element rex-i-clang"><span class="rex-i-element-text">'.htmlspecialchars($clang_name).'</span></span></td>
            '.$add_td.'
            <td><input class="rex-form-text" type="text" id="rex-form-clang-name" name="clang_name" value="'.htmlspecialchars($lang).'" /></td>
            <td><input class="rex-form-submit" type="submit" name="edit_clang_save" value="'.rex_i18n::msg('clang_update').'"'. rex::getAccesskey(rex_i18n::msg('clang_update'), 'save') .' /></td>
          </tr>';

  }
  else
  {
    $editLink = 'index.php?page=system&amp;subpage=lang&amp;func=editclang&amp;clang_id='.$lang_id.'#clang';

    $content .= '
          <tr>
            <td class="rex-small"><a class="rex-i-element rex-i-clang" href="'. $editLink .'"><span class="rex-i-element-text">'.htmlspecialchars($clang_name).'</span></a></td>
            '.$add_td.'
            <td><a href="'. $editLink .'">'.htmlspecialchars($lang).'</a></td>
            <td>'. $delLink .'</td>
          </tr>';
  }
}

$content .= '
    </tbody>
  </table>';

if ($func == 'addclang' || $func == 'editclang')
{
  $content .= '
          <script type="text/javascript">
            <!--
            jQuery(function($){
              $("#rex-form-clang-name").focus();
            });
            //-->
          </script>
        </fieldset>';
}

$content .= '
      </form>
      </div>';

echo rex_view::contentBlock($content,'','block');
