<?php

/**
 * Erweiterung eines Artikels um slicemanagement.
 *
 * @package redaxo\structure\content
 */
class rex_article_content_editor extends rex_article_content
{
    private $MODULESELECT;

    public function __construct($article_id = null, $clang = null)
    {
        parent::__construct($article_id, $clang);
    }

    /**
     * {@inheritdoc}
     */
    protected function outputSlice(rex_sql $artDataSql, $moduleIdToAdd)
    {
        global $REX;

        if ($this->mode != 'edit') {
            // ----- wenn mode nicht edit
            $slice_content = parent::outputSlice(
                $artDataSql,
                $moduleIdToAdd
            );
        } else {
            $sliceId      = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.id');
            $sliceCtype   = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.ctype');

            $moduleInput  = $artDataSql->getValue(rex::getTablePrefix() . 'module.input');
            $moduleOutput = $artDataSql->getValue(rex::getTablePrefix() . 'module.output');
            $moduleId     = $artDataSql->getValue(rex::getTablePrefix() . 'module.id');
            $moduleName   = htmlspecialchars($artDataSql->getValue(rex::getTablePrefix() . 'module.name'));

            // ----- add select box einbauen
            if ($this->function == 'add' && $this->slice_id == $sliceId) {
                $slice_content = $this->addSlice($sliceId, $moduleIdToAdd);
            } else {
                // ----- BLOCKAUSWAHL - SELECT
                $slice_content = $this->getModuleSelect($sliceId);
            }

            // ----- Display message at current slice
            //if(rex::getUser()->getComplexPerm('modules')->hasPerm($moduleId))
            {
                if ($this->function != 'add' && $this->slice_id == $sliceId) {
                    $msg = '';
                    if ($this->warning != '') {
                        $msg .= rex_view::warning($this->warning);
                    }
                    if ($this->info != '') {
                        $msg .= rex_view::success($this->info);
                    }
                    $slice_content .= $msg;
                }
            }

            // ----- Slicemenue
            $containerClass = '';
            if ($this->function == 'edit' && $this->slice_id == $sliceId) {
                $containerClass = ' rex-slice-edit';
            }

            $slice_content .= '<section class="rex-slice' . $containerClass . '">';
            $slice_content .= '
                    <header class="rex-slice-header">
                        ' . $this->getSliceMenu($artDataSql) . '
                    </header>';

            // ----- EDIT/DELETE BLOCK - Wenn Rechte vorhanden
            if (rex::getUser()->getComplexPerm('modules')->hasPerm($moduleId)) {
                if ($this->function == 'edit' && $this->slice_id == $sliceId) {
                    // **************** Aktueller Slice

                    // ----- PRE VIEW ACTION [EDIT]
                    $action = new rex_article_action($moduleId, 'edit', $artDataSql);
                    if (rex_request_method() == 'post' && rex_request('function', 'string') == 'edit') {
                        $action->setRequestValues();
                    }
                    $action->exec(rex_article_action::PREVIEW);
                    // ----- / PRE VIEW ACTION

                    $moduleInput = $this->replaceVars($artDataSql, $moduleInput);
                    $slice_content .= $this->editSlice($sliceId, $moduleInput, $sliceCtype, $moduleId);
                } else {
                    // Modulinhalt ausgeben
                    $moduleOutput = $this->replaceVars($artDataSql, $moduleOutput);
                    $slice_content .= $this->getWrappedModuleOutput($moduleId, $moduleOutput);
                }
            } else {
                // ----- hat keine rechte an diesem modul, einfach ausgeben
                $moduleOutput = $this->replaceVars($artDataSql, $moduleOutput);
                $slice_content .= $this->getWrappedModuleOutput($moduleId, $moduleOutput);
            }
            $slice_content .= '</section>';
        }

        return $slice_content;
    }

    /**
     * Returns the slice menu
     *
     * @param rex_sql $artDataSql rex_sql istance containing all the slice and module information
     * @return string
     */
    private function getSliceMenu(rex_sql $artDataSql)
    {
        $sliceId      = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.id');
        $sliceCtype   = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.ctype');

        $moduleId     = $artDataSql->getValue(rex::getTablePrefix() . 'module.id');
        $moduleName   = rex_i18n::translate($artDataSql->getValue(rex::getTablePrefix() . 'module.name'));


        $context = new rex_context([
            'page' => rex_be_controller::getCurrentPage(),
            'article_id' => $this->article_id,
            'slice_id' => $sliceId,
            'clang' => $this->clang,
            'ctype' => $this->ctype
        ]);
        $fragment = '#slice' . $sliceId;
        $listElements = [];

        if (rex::getUser()->getComplexPerm('modules')->hasPerm($moduleId)
            && rex_template::hasModule($this->template_attributes, $this->ctype, $moduleId)
        ) {
            // edit
            $n = [];
            $n['title'] = '<span class="rex-visuallyhidden">' . rex_i18n::msg('module') . ' ' . $moduleName . ' </span>' . rex_i18n::msg('edit');
            $n['href'] = $context->getUrl(['function' => 'edit']) . $fragment;
            $n['itemClasses'] = ['rex-slice-edit'];
            $n['linkClasses'] = ['rex-slice-edit'];
            $listElements[] = $n;

            // delete
            $n = [];
            $n['title'] = '<span class="rex-visuallyhidden">' . rex_i18n::msg('module') . ' ' . $moduleName . ' </span>' . rex_i18n::msg('delete');
            $n['href'] = $context->getUrl(['function' => 'delete', 'save' => 1]) . $fragment;
            $n['itemClasses'] = ['rex-slice-delete'];
            $n['linkClasses'] = ['rex-slice-delete'];
            $n['linkAttr'] = ['data-confirm' => rex_i18n::msg('delete') . ' ?'];
            $listElements[] = $n;


            if (rex::getUser()->hasPerm('moveSlice[]')) {

                // moveup
                $n = [];
                $n['title'] = '<span class="rex-visuallyhidden">' . rex_i18n::msg('module') . ' ' . $moduleName . ' </span>' . rex_i18n::msg('move_slice_up');
                $n['href'] = $context->getUrl(['upd' => time(), 'rex-api-call' => 'content_move_slice', 'direction' => 'moveup']) . $fragment;
                $n['itemClasses'] = ['rex-slice-move-up'];
                $n['linkClasses'] = ['rex-slice-move-up'];
                $listElements[] = $n;

                // movedown
                $n = [];
                $n['title'] = '<span class="rex-visuallyhidden">' . rex_i18n::msg('module') . ' ' . $moduleName . ' </span>' . rex_i18n::msg('move_slice_down');
                $n['href'] = $context->getUrl(['upd' => time(), 'rex-api-call' => 'content_move_slice', 'direction' => 'movedown']) . $fragment;
                $n['itemClasses'] = ['rex-slice-move-down'];
                $n['linkClasses'] = ['rex-slice-move-down'];
                $listElements[] = $n;
            }

        } else {
            $n = [];
            $n['title'] = rex_i18n::msg('no_editing_rights') . ' ' . $moduleName;
            $n['itemClasses'] = ['rex-error'];
            $listElements[] = $n;
        }

        // ----- EXTENSION POINT
        $listElements = rex_extension::registerPoint(new rex_extension_point(
            'ART_SLICE_MENU',
            $listElements,
            [
                'article_id' => $this->article_id,
                'clang' => $this->clang,
                'ctype' => $sliceCtype,
                'module_id' => $moduleId,
                'slice_id' => $sliceId,
                'perm' => rex::getUser()->getComplexPerm('modules')->hasPerm($moduleId)
            ]
        ));

        $blocks = [];
        $blocks[] = [
            'headline' => ['title' => $moduleName],
            'navigation' => $listElements
        ];

        $fragment = new rex_fragment();
        $fragment->setVar('type', 'slice');
        $fragment->setVar('blocks', $blocks, false);
        return $fragment->parse('navigation.tpl');

    }

    /**
     * Wraps the output of a module
     *
     * @param integer $moduleId     The id of the module
     * @param string  $moduleOutput The output of the module
     * @return string
     */
    private function getWrappedModuleOutput($moduleId, $moduleOutput)
    {
        return '
                        <section class="rex-slice-content">
                                ' . $this->getStreamOutput('module/' . $moduleId . '/output', $moduleOutput) . '
                        </section>
                        ';
    }

    private function getModuleSelect($sliceId)
    {
        // ----- BLOCKAUSWAHL - SELECT
        $this->MODULESELECT[$this->ctype]->setId('module_id' . $sliceId);

        return '
                    <section class="rex-slice-select">
                    <div class="rex-form">
                    <form action="' . rex_url::backendController() . '" method="get" id="slice' . $sliceId . '">
                        <fieldset>
                            <legend><span>' . rex_i18n::msg('add_block') . '</span></legend>
                            <input type="hidden" name="page" value="content" />
                            <input type="hidden" name="article_id" value="' . $this->article_id . '" />
                            <input type="hidden" name="clang" value="' . $this->clang . '" />
                            <input type="hidden" name="ctype" value="' . $this->ctype . '" />
                            <input type="hidden" name="slice_id" value="' . $sliceId . '" />
                            <input type="hidden" name="function" value="add" />

                            <div class="rex-form-data">
                                 ' . $this->MODULESELECT[$this->ctype]->get() . '
                                 <noscript><input class="rex-form-submit" type="submit" name="btn_add" value="' . rex_i18n::msg('add_block') . '" /></noscript>
                            </div>
                        </fieldset>
                    </form>
                    </div>
                    </section>';

    }

    /**
     * {@inheritdoc}
     */
    protected function preArticle($articleContent, $module_id)
    {
        // ---------- moduleselect: nur module nehmen auf die der user rechte hat
        if ($this->mode == 'edit') {
            $MODULE = rex_sql::factory();
            $modules = $MODULE->getArray('select * from ' . rex::getTablePrefix() . 'module order by name');

            $template_ctypes = isset($this->template_attributes['ctype']) ? $this->template_attributes['ctype'] : [];
            // wenn keine ctyes definiert sind, gibt es immer den CTYPE=1
            if (count($template_ctypes) == 0) {
                $template_ctypes = [1 => 'default'];
            }

            $this->MODULESELECT = [];
            foreach ($template_ctypes as $ct_id => $ct_name) {
                $this->MODULESELECT[$ct_id] = new rex_select;
                $this->MODULESELECT[$ct_id]->setName('module_id');
                $this->MODULESELECT[$ct_id]->setSize('1');
                $this->MODULESELECT[$ct_id]->setStyle('class="rex-form-select"');
                $this->MODULESELECT[$ct_id]->setAttribute('onchange', 'this.form.submit();');
                $this->MODULESELECT[$ct_id]->addOption('----------------------------  ' . rex_i18n::msg('add_block'), '');
                foreach ($modules as $m) {
                    if (rex::getUser()->getComplexPerm('modules')->hasPerm($m['id'])) {
                        if (rex_template::hasModule($this->template_attributes, $ct_id, $m['id'])) {
                            $this->MODULESELECT[$ct_id]->addOption(rex_i18n::translate($m['name'], false), $m['id']);
                        }
                    }
                }
            }
        }

        return parent::preArticle($articleContent, $module_id);
    }

    /**
     * {@inheritdoc}
     */
    protected function postArticle($articleContent, $moduleIdToAdd)
    {
        // special identifier for the slot behind the last slice
        $LCTSL_ID = -1;

        // ----- add module im edit mode
        if ($this->mode == 'edit') {
            if ($this->function == 'add' && $this->slice_id == $LCTSL_ID) {
                $slice_content = $this->addSlice($LCTSL_ID, $moduleIdToAdd);
            } else {
                // ----- BLOCKAUSWAHL - SELECT
                $slice_content = $this->getModuleSelect($LCTSL_ID);
            }
            $articleContent .= $slice_content;
        }

        return $articleContent;
    }


    // ----- ADD Slice
    protected function addSlice($sliceId, $moduleIdToAdd)
    {
        $MOD = rex_sql::factory();
        $MOD->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'module WHERE id="' . $moduleIdToAdd . '"');

        if ($MOD->getRows() != 1) {
            $slice_content = rex_view::warning(rex_i18n::msg('module_doesnt_exist'));
        } else {
            $initDataSql = rex_sql::factory();

            // ----- PRE VIEW ACTION [ADD]
            $action = new rex_article_action($moduleIdToAdd, 'add', $initDataSql);
            $action->setRequestValues();
            $action->exec(rex_article_action::PREVIEW);
            // ----- / PRE VIEW ACTION

            $moduleInput = $this->replaceVars($initDataSql, $MOD->getValue('input'));

            $moduleInput = $this->getStreamOutput('module/' . $moduleIdToAdd . '/input', $moduleInput);

            $msg = '';
            if ($this->warning != '') {
                $msg .= rex_view::warning($this->warning);
            }
            if ($this->info != '') {
                $msg .= rex_view::success($this->info);
            }


            $blocks = [];
            $blocks[] = [
                'headline' => ['title' => rex_i18n::msg('module') . ': ' . rex_i18n::translate($MOD->getValue('name'))],
                'navigation' => []
            ];

            $fragment = new rex_fragment();
            $fragment->setVar('type', 'slice');
            $fragment->setVar('blocks', $blocks, false);
            $slice_header = $fragment->parse('navigation.tpl');


            $listElements = [];

            $n = [];
            $n['title'] = '<input class="rex-form-submit" type="submit" name="btn_save" value="' . rex_i18n::msg('add_block') . '"' . rex::getAccesskey(rex_i18n::msg('add_block'), 'save') . ' />';
            $n['itemClasses'] = ['rex-slice-save'];
            $listElements[] = $n;


            $blocks = [];
            $blocks[] = [
                'navigation' => $listElements
            ];

            $fragment = new rex_fragment();
            $fragment->setVar('type', 'action');
            $fragment->setVar('blocks', $blocks, false);
            $slice_footer = $fragment->parse('navigation.tpl');



            $slice_content = '
                ' . $msg . '
                <section class="rex-slice rex-slice-add">

                    <div class="rex-form">
                    <form action="' . rex_url::currentBackendPage(['article_id' => $this->article_id, 'slice_id' => $sliceId, 'clang' => $this->clang, 'ctype' => $this->ctype]) . '#slice' . $sliceId . '" method="post" id="REX_FORM" enctype="multipart/form-data">

                    <header class="rex-slice-header">
                        ' . $slice_header . '
                    </header>

                    <section class="rex-slice-content">

                        <fieldset class="rex-form-col-1">
                            <legend><span>' . rex_i18n::msg('add_block') . '</span></legend>
                            <input type="hidden" name="function" value="add" />
                            <input type="hidden" name="module_id" value="' . $moduleIdToAdd . '" />
                            <input type="hidden" name="save" value="1" />

                            <div class="rex-form-datas">
                                ' . $moduleInput . '
                            </div>
                        </fieldset>
                    </section>

                    <footer class="rex-slice-footer">
                        ' . $slice_footer . '
                    </footer>

                    </form>
                    </div>
                </section>
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

        $listElements = [];

        $n = [];
        $n['title'] = '<input class="rex-form-submit" type="submit" value="' . rex_i18n::msg('save_block') . '" name="btn_save" ' . rex::getAccesskey(rex_i18n::msg('save_block'), 'save') . ' />';
        $n['itemClasses'] = ['rex-slice-save'];
        $listElements[] = $n;

        $n = [];
        $n['title'] = '<input class="rex-form-submit rex-form-submit-2" type="submit" value="' . rex_i18n::msg('update_block') . '" name="btn_update" ' . rex::getAccesskey(rex_i18n::msg('update_block'), 'apply') . ' />';
        $n['itemClasses'] = ['rex-slice-save'];
        $listElements[] = $n;

        $blocks = [];
        $blocks[] = [
            'navigation' => $listElements
        ];

        $fragment = new rex_fragment();
        $fragment->setVar('type', 'action');
        $fragment->setVar('blocks', $blocks, false);
        $slice_footer = $fragment->parse('navigation.tpl');

        $slice_content = '
            <div class="rex-form">
            <form enctype="multipart/form-data" action="' . rex_url::currentBackendPage(['article_id' => $this->article_id, 'slice_id' => $RE_CONTS, 'ctype' => $RE_CTYPE, 'clang' => $this->clang]) . '#slice' . $RE_CONTS . '" method="post" id="REX_FORM">

                <section class="rex-slice-content">
                    <fieldset class="rex-form-col-1">
                        <legend><span>' . rex_i18n::msg('edit_block') . '</span></legend>
                        <input type="hidden" name="module_id" value="' . $RE_MODUL_ID . '" />
                        <input type="hidden" name="function" value="edit" />
                        <input type="hidden" name="save" value="1" />
                        <input type="hidden" name="update" value="0" />

                        <div class="rex-form-datas">
                            ' . $this->getStreamOutput('module/' . $RE_MODUL_ID . '/input', $RE_MODUL_IN) . '
                        </div>
                    </fieldset>
                </section>

                <footer class="rex-slice-footer">
                    ' . $slice_footer . '
                </footer>

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
