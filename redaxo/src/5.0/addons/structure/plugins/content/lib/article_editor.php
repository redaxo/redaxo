<?php

/**
 * Erweiterung eines Artikels um slicemanagement.
 *
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_article_editor extends rex_article
{
  private $MODULESELECT;

  public function __construct($article_id = null, $clang = null)
  {
    parent::__construct($article_id, $clang);
  }

  protected function outputSlice(rex_sql $artDataSql, $moduleIdToAdd)
  {
    global $REX;
    
    if($this->mode != 'edit')
    {
      // ----- wenn mode nicht edit
      $slice_content = parent::outputSlice(
        $artDataSql,
        $moduleIdToAdd
      );
    }
    else
    {
      $sliceId      = $artDataSql->getValue($REX['TABLE_PREFIX'].'article_slice.id');
      $sliceCtype   = $artDataSql->getValue($REX['TABLE_PREFIX'].'article_slice.ctype');
      
      $moduleInput  = $artDataSql->getValue($REX['TABLE_PREFIX'].'module.input');
      $moduleOutput = $artDataSql->getValue($REX['TABLE_PREFIX'].'module.output');
      $moduleId     = $artDataSql->getValue($REX['TABLE_PREFIX'].'module.id');
      $moduleName   = htmlspecialchars($artDataSql->getValue($REX['TABLE_PREFIX'].'module.name'));

      // ----- add select box einbauen
      if($this->function=="add" && $this->slice_id == $sliceId)
      {
        $slice_content = $this->addSlice($sliceId, $moduleIdToAdd);
      }
      else
      {
        // ----- BLOCKAUSWAHL - SELECT
        $this->MODULESELECT[$this->ctype]->setId("module_id". $sliceId);

        $slice_content = '
              <div class="rex-form rex-form-content-editmode">
              <form action="index.php" method="get" id="slice'. $sliceId .'">
                <fieldset class="rex-form-col-1">
                  <legend><span>'. $REX['I18N']->msg("add_block") .'</span></legend>
                  <input type="hidden" name="article_id" value="'. $this->article_id .'" />
                  <input type="hidden" name="page" value="content" />
                  <input type="hidden" name="mode" value="'. $this->mode .'" />
                  <input type="hidden" name="slice_id" value="'. $sliceId .'" />
                  <input type="hidden" name="function" value="add" />
                  <input type="hidden" name="clang" value="'.$this->clang.'" />
                  <input type="hidden" name="ctype" value="'.$this->ctype.'" />

                  <div class="rex-form-wrapper">
                    <div class="rex-form-row">
                      <p class="rex-form-col-a rex-form-select">
                        '. $this->MODULESELECT[$this->ctype]->get() .'
                        <noscript><input class="rex-form-submit" type="submit" name="btn_add" value="'. $REX['I18N']->msg("add_block") .'" /></noscript>
                      </p>
                    </div>
                  </div>
                </fieldset>
              </form>
              </div>';
      }

      // ----- Display message at current slice
      //if($REX['USER']->isAdmin() || $REX['USER']->hasPerm("module[".$moduleId."]"))
      {
        if($this->function != 'add' && $this->slice_id == $sliceId)
        {
          $msg = '';
          if($this->warning != '')
          {
            $msg .= rex_warning($this->warning);
          }
          if($this->info != '')
          {
            $msg .= rex_info($this->info);
          }
          $slice_content .= $msg;
        }
      }

      // ----- Slicemenue
      $containerClass = '';
      if($this->function=="edit" && $this->slice_id == $sliceId)
      {
        $containerClass = 'rex-form-content-editmode-edit-slice';
      }

      $slice_content .= '
      		<div class="rex-content-editmode-module-name '. $containerClass .'">
            <h3 class="rex-hl4">'. $moduleName .'</h3>
            <div class="rex-navi-slice">'. $this->getSliceMenu($artDataSql) .'</div>
          </div>';
      
      // ----- EDIT/DELETE BLOCK - Wenn Rechte vorhanden
      if($REX['USER']->isAdmin() || $REX['USER']->hasPerm("module[".$moduleId."]"))
      {
        if($this->function=="edit" && $this->slice_id == $sliceId)
        {
          // **************** Aktueller Slice

          $REX_ACTION = array ();

          // nach klick auf den übernehmen button,
          // die POST werte übernehmen
          if(rex_request_method() == 'post' && rex_var::isEditEvent())
          {
            foreach ($REX['VARIABLES'] as $obj)
            {
              $REX_ACTION = $obj->getACRequestValues($REX_ACTION);
            }
          }
          // Sonst die Werte aus der DB holen
          // (1. Aufruf via Editieren Link)
          else
          {
            foreach ($REX['VARIABLES'] as $obj)
            {
              $REX_ACTION = $obj->getACDatabaseValues($REX_ACTION, $artDataSql);
            }
          }

          // ----- PRE VIEW ACTION [EDIT]
          $REX_ACTION = rex_execPreViewAction($moduleId, 'edit', $REX_ACTION);
          // ----- / PRE VIEW ACTION

          // ****************** Action Werte in SQL-Objekt uebernehmen
          foreach($REX['VARIABLES'] as $obj)
          {
            $obj->setACValues($artDataSql, $REX_ACTION);
          }

          $slice_content .= $this->editSlice($sliceId,$moduleInput,$sliceCtype, $moduleId);
          $slice_content = $this->replaceVars($artDataSql, $slice_content);
        }
        else
        {
          // Modulinhalt ausgeben
          $slice_content .= $this->getWrappedModuleOutput($moduleId, $moduleOutput);
          $slice_content = $this->replaceVars($artDataSql, $slice_content);
        }
      }
      else
      {
        // ----- hat keine rechte an diesem modul, einfach ausgeben
        $slice_content .= $this->getWrappedModuleOutput($moduleId, $moduleOutput);
        $slice_content = $this->replaceVars($artDataSql, $slice_content);
      }
      
    }
    
    return $slice_content;
  }
  
  private function getSliceMenu(rex_sql $artDataSql)
  {
    global $REX;
    
    $sliceId      = $artDataSql->getValue($REX['TABLE_PREFIX'].'article_slice.id');
    $sliceCtype   = $artDataSql->getValue($REX['TABLE_PREFIX'].'article_slice.ctype');
    
    $moduleId     = $artDataSql->getValue($REX['TABLE_PREFIX'].'module.id');
    $moduleName   = htmlspecialchars($artDataSql->getValue($REX['TABLE_PREFIX'].'module.name'));
  

    $sliceUrl = 'index.php?page=content&amp;article_id='. $this->article_id .'&amp;mode=edit&amp;slice_id='. $sliceId .'&amp;clang='. $this->clang .'&amp;ctype='. $this->ctype .'%s#slice'. $sliceId;
    $listElements = array();

    if(($REX['USER']->isAdmin() || $REX['USER']->hasPerm("module[".$moduleId."]"))
      && rex_template::hasModule($this->template_attributes, $this->ctype, $moduleId))
    {
      $listElements[] = '<a href="'. sprintf($sliceUrl, '&amp;function=edit') .'" class="rex-tx3">'. $REX['I18N']->msg('edit') .' <span>'. $moduleName .'</span></a>';
    	$listElements[] = '<a href="'. sprintf($sliceUrl, '&amp;function=delete&amp;save=1') .'" class="rex-tx2" onclick="return confirm(\''.$REX['I18N']->msg('delete').' ?\')">'. $REX['I18N']->msg('delete') .' <span>'. $moduleName .'</span></a>';

      if ($REX['USER']->hasPerm('moveSlice[]'))
      {
        $moveUp = $REX['I18N']->msg('move_slice_up');
        $moveDown = $REX['I18N']->msg('move_slice_down');
        // upd stamp uebergeben, da sonst ein block nicht mehrfach hintereindander verschoben werden kann
        // (Links waeren sonst gleich und der Browser laesst das klicken auf den gleichen Link nicht zu)
        $listElements[] = '<a href="'. sprintf($sliceUrl, '&amp;upd='. time() .'&amp;function=moveup') .'" title="'. $moveUp .'" class="rex-slice-move-up"><span>'. $moduleName .'</span></a>';
        $listElements[] = '<a href="'. sprintf($sliceUrl, '&amp;upd='. time() .'&amp;function=movedown') .'" title="'. $moveDown .'" class="rex-slice-move-down"><span>'. $moduleName .'</span></a>';
      }

    }
    else
    {
      $listElements[] = '<b class="rex-tx2">'. $REX['I18N']->msg('no_editing_rights') .' <span>'. $moduleName .'</span></b>';
    }

    // ----- EXTENSION POINT
    $listElements = rex_register_extension_point(
      'ART_SLICE_MENU',
      $listElements,
      array(
        'article_id' => $this->article_id,
        'clang' => $this->clang,
        'ctype' => $sliceCtype,
        'module_id' => $moduleId,
        'slice_id' => $sliceId,
        'perm' => ($REX['USER']->isAdmin() || $REX['USER']->hasPerm("module[".$moduleId."]"))
      )
    );
    
    // ----- render the list
    $mne = '';
    $listElementFlag = true;
    foreach($listElements as $listElement)
    {
      $class = '';
      if ($listElementFlag)
      {
        $class = ' class="rex-navi-first"';
        if(count($listElements) == 1)
        {
          $class = ' class="rex-navi-first rex-navi-onlyone"';
        }
        $listElementFlag = false;
      }
      $mne  .= '<li'.$class.'>'. $listElement .'</li>';
    }
    
    return '<ul>'. $mne .'</ul>';
  }
  
  /**
   * Wraps the output of a module
   * 
   * @param integer $moduleId The id of the module
   * @param string $moduleOutput The output of the module
   */
  private function getWrappedModuleOutput($moduleId, $moduleOutput)
  {
    return '
            <!-- *** OUTPUT OF MODULE-OUTPUT - START *** -->
            <div class="rex-content-editmode-slice-output">
              <div class="rex-content-editmode-slice-output-2">
                '. $this->getVariableStreamOutput('module/'. $moduleId .'/output', $moduleOutput) .'
              </div>
            </div>
            <!-- *** OUTPUT OF MODULE-OUTPUT - END *** -->
            ';
  }


  protected function preArticle()
  {
    global $REX;

    // ---------- moduleselect: nur module nehmen auf die der user rechte hat
    if($this->mode=='edit')
    {
      $MODULE = rex_sql::factory();
      $modules = $MODULE->getArray('select * from '.$REX['TABLE_PREFIX'].'module order by name');

      $template_ctypes = rex_getAttributes('ctype', $this->template_attributes, array ());
      // wenn keine ctyes definiert sind, gibt es immer den CTYPE=1
      if(count($template_ctypes) == 0)
      {
        $template_ctypes = array(1 => 'default');
      }

      $this->MODULESELECT = array();
      foreach($template_ctypes as $ct_id => $ct_name)
      {
        $this->MODULESELECT[$ct_id] = new rex_select;
        $this->MODULESELECT[$ct_id]->setName('module_id');
        $this->MODULESELECT[$ct_id]->setSize('1');
        $this->MODULESELECT[$ct_id]->setStyle('class="rex-form-select"');
        $this->MODULESELECT[$ct_id]->setAttribute('onchange', 'this.form.submit();');
        $this->MODULESELECT[$ct_id]->addOption('----------------------------  '.$REX['I18N']->msg('add_block'),'');
        foreach($modules as $m)
        {
          if ($REX['USER']->isAdmin() || $REX['USER']->hasPerm('module['.$m['id'].']'))
          {
            if(rex_template::hasModule($this->template_attributes,$ct_id,$m['id']))
            {
              $this->MODULESELECT[$ct_id]->addOption(rex_translate($m['name'],NULL,FALSE),$m['id']);
            }
          }
        }
      }
    }
  }

  protected function postArticle($articleContent, $moduleIdToAdd)
  {
    global $REX;
    
    // special identifier for the slot behind the last slice
    $LCTSL_ID = -1;

    // ----- add module im edit mode
    if ($this->mode == "edit")
    {
      if($this->function=="add" && $this->slice_id == $LCTSL_ID)
      {
        $slice_content = $this->addSlice($LCTSL_ID,$moduleIdToAdd);
      }else
      {
        // ----- BLOCKAUSWAHL - SELECT
        $this->MODULESELECT[$this->ctype]->setId("module_id". $LCTSL_ID);

        $slice_content = '
            <div class="rex-form rex-form-content-editmode">
            <form action="index.php" method="get">
              <fieldset class="rex-form-col-1">
                <legend><span>'. $REX['I18N']->msg("add_block") .'</span></legend>
                <input type="hidden" name="article_id" value="'. $this->article_id .'" />
                <input type="hidden" name="page" value="content" />
                <input type="hidden" name="mode" value="'. $this->mode .'" />
                <input type="hidden" name="slice_id" value="'. $LCTSL_ID .'" />
                <input type="hidden" name="function" value="add" />
                <input type="hidden" name="clang" value="'.$this->clang.'" />
                <input type="hidden" name="ctype" value="'.$this->ctype.'" />


                  <div class="rex-form-wrapper">
                    <div class="rex-form-row">
                      <p class="rex-form-col-a rex-form-select">
                        '. $this->MODULESELECT[$this->ctype]->get() .'
                        <noscript><input class="rex-form-submit" type="submit" name="btn_add" value="'. $REX['I18N']->msg("add_block") .'" /></noscript>
                      </p>
                    </div>
                  </div>
              </fieldset>
            </form>
            </div>';
      }
      $articleContent .= $slice_content;
    }
    return $articleContent;
  }


  // ----- ADD Slice
  protected function addSlice($sliceId,$moduleIdToAdd)
  {
    global $REX;

    $MOD = rex_sql::factory();
    $MOD->setQuery("SELECT * FROM ".$REX['TABLE_PREFIX']."module WHERE id=$moduleIdToAdd");

    if ($MOD->getRows() != 1)
    {
      $slice_content = rex_warning($REX['I18N']->msg('module_doesnt_exist'));
    }else
    {
      $initDataSql = rex_sql::factory();

      $REX_ACTION = array();
      foreach ($REX['VARIABLES'] as $obj)
      {
        $REX_ACTION = $obj->getACRequestValues($REX_ACTION);
      }

      // ----- PRE VIEW ACTION [ADD]
      $REX_ACTION = rex_execPreViewAction($moduleIdToAdd, 'add', $REX_ACTION);
      // ----- / PRE VIEW ACTION

      // ****************** Action Werte in Sql-Objekt uebernehmen
      foreach($REX['VARIABLES'] as $obj)
      {
        $obj->setACValues($initDataSql, $REX_ACTION);
      }

      $moduleInput = $this->replaceVars($initDataSql, $MOD->getValue("input"));

      $moduleInput = $this->getVariableStreamOutput('module/'. $moduleIdToAdd .'/input', $moduleInput);

      $msg = '';
      if($this->warning != '')
      {
        $msg .= rex_warning($this->warning);
      }
      if($this->info != '')
      {
        $msg .= rex_info($this->info);
      }

      $slice_content = '
        <a name="addslice"></a>
        '. $msg .'
        <div class="rex-form rex-form-content-editmode-add-slice">
        <form action="index.php#slice'. $sliceId .'" method="post" id="REX_FORM" enctype="multipart/form-data">
          <fieldset class="rex-form-col-1">
            <legend><span>'. $REX['I18N']->msg('add_block').'</span></legend>
            <input type="hidden" name="article_id" value="'. $this->article_id .'" />
            <input type="hidden" name="page" value="content" />
            <input type="hidden" name="mode" value="'. $this->mode .'" />
            <input type="hidden" name="slice_id" value="'. $sliceId .'" />
            <input type="hidden" name="function" value="add" />
            <input type="hidden" name="module_id" value="'. $moduleIdToAdd .'" />
            <input type="hidden" name="save" value="1" />
            <input type="hidden" name="clang" value="'. $this->clang .'" />
            <input type="hidden" name="ctype" value="'.$this->ctype .'" />

            <div class="rex-content-editmode-module-name">
              <h3 class="rex-hl4">
                '. $REX['I18N']->msg("module") .': <span>'. htmlspecialchars($MOD->getValue("name")) .'</span>
              </h3>
            </div>

            <div class="rex-form-wrapper">

              <div class="rex-form-row">
                <div class="rex-content-editmode-slice-input">
                <div class="rex-content-editmode-slice-input-2">
                  '. $moduleInput .'
                </div>
                </div>
              </div>

            </div>
          </fieldset>

          <fieldset class="rex-form-col-1">
             <div class="rex-form-wrapper">
              <div class="rex-form-row">
                <p class="rex-form-col-a rex-form-submit">
                  <input class="rex-form-submit" type="submit" name="btn_save" value="'. $REX['I18N']->msg('add_block') .'"'. rex_accesskey($REX['I18N']->msg('add_block'), $REX['ACKEY']['SAVE']) .' />
                </p>
              </div>
            </div>
          </fieldset>
        </form>
        </div>
        <script type="text/javascript">
           <!--
          jQuery(function($) {
            $(":input:visible:enabled:not([readonly]):first", $("#REX_FORM")).focus();
          });
           //-->
        </script>';

    }

    return $slice_content;
  }

  // ----- EDIT Slice
  protected function editSlice($RE_CONTS, $RE_MODUL_IN, $RE_CTYPE, $RE_MODUL_ID)
  {
    global $REX;

    $slice_content = '
      <a name="editslice"></a>
      <div class="rex-form rex-form-content-editmode-edit-slice">
      <form enctype="multipart/form-data" action="index.php#slice'.$RE_CONTS.'" method="post" id="REX_FORM">
        <fieldset class="rex-form-col-1">
          <legend><span>'. $REX['I18N']->msg('edit_block') .'</span></legend>
          <input type="hidden" name="article_id" value="'.$this->article_id.'" />
          <input type="hidden" name="page" value="content" />
          <input type="hidden" name="mode" value="'.$this->mode.'" />
          <input type="hidden" name="slice_id" value="'.$RE_CONTS.'" />
          <input type="hidden" name="ctype" value="'.$RE_CTYPE.'" />
          <input type="hidden" name="module_id" value="'. $RE_MODUL_ID .'" />
          <input type="hidden" name="function" value="edit" />
          <input type="hidden" name="save" value="1" />
          <input type="hidden" name="update" value="0" />
          <input type="hidden" name="clang" value="'.$this->clang.'" />

          <div class="rex-form-wrapper">
            <div class="rex-form-row">
              <div class="rex-content-editmode-slice-input">
                <div class="rex-content-editmode-slice-input-2">
                '. $this->getVariableStreamOutput('module/'. $RE_MODUL_ID.'/input', $RE_MODUL_IN) .'
                </div>
              </div>
            </div>
          </div>
        </fieldset>

        <fieldset class="rex-form-col-2">
          <div class="rex-form-wrapper">
            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-submit">
                <input class="rex-form-submit" type="submit" value="'.$REX['I18N']->msg('save_block').'" name="btn_save" '. rex_accesskey($REX['I18N']->msg('save_block'), $REX['ACKEY']['SAVE']) .' />
                <input class="rex-form-submit rex-form-submit-2" type="submit" value="'.$REX['I18N']->msg('update_block').'" name="btn_update" '. rex_accesskey($REX['I18N']->msg('update_block'), $REX['ACKEY']['APPLY']) .' />
              </p>
            </div>
          </div>
        </fieldset>
      </form>
      </div>
      <script type="text/javascript">
         <!--
        jQuery(function($) {
          $(":input:visible:enabled:not([readonly]):first", $("#REX_FORM")).focus();
        });
         //-->
      </script>';

    return $slice_content;
  }
}