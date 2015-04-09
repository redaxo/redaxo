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
        if ($this->mode != 'edit') {
            // ----- wenn mode nicht edit
            $slice_content = parent::outputSlice(
                $artDataSql,
                $moduleIdToAdd
            );
        } else {
            $sliceId      = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.id');
            $sliceCtype   = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.ctype_id');

            $moduleInput  = $artDataSql->getValue(rex::getTablePrefix() . 'module.input');
            $moduleOutput = $artDataSql->getValue(rex::getTablePrefix() . 'module.output');
            $moduleId     = $artDataSql->getValue(rex::getTablePrefix() . 'module.id');
            
            $slice_content = '';
            // ----- add select box einbauen
            if ($this->function == 'add' && $this->slice_id == $sliceId) {
                $slice_content .= $this->addSlice($sliceId, $moduleIdToAdd);
            } else {
                // ----- BLOCKAUSWAHL - SELECT
                $slice_content .= $this->getModuleSelect($sliceId);
            }

            $panel = '';
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
                    $panel .= $msg;
                }
            }


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
                    $panel .= $this->editSlice($sliceId, $moduleInput, $sliceCtype, $moduleId);
                } else {
                    // Modulinhalt ausgeben
                    $moduleOutput = $this->replaceVars($artDataSql, $moduleOutput);
                    $panel .= $this->getWrappedModuleOutput($moduleId, $moduleOutput);
                }
            } else {
                // ----- hat keine rechte an diesem modul, einfach ausgeben
                $moduleOutput = $this->replaceVars($artDataSql, $moduleOutput);
                $panel .= $this->getWrappedModuleOutput($moduleId, $moduleOutput);
            }


            // ----- Slicemenue
            $containerClass = '';
            if ($this->function == 'edit' && $this->slice_id == $sliceId) {
                $containerClass = ' rex-slice-edit';
            }

            $fragment = new rex_fragment();
            $fragment->setVar('header', $this->getSliceMenu($artDataSql), false);
            $fragment->setVar('body', $panel, false);
            $slice_content .= '<li class="rex-slice' . $containerClass . '">' . $fragment->parse('core/page/section.php') . '</li>';


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
        $sliceCtype   = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.ctype_id');

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

        $header_right = '';


        $menu_items_action = [];
        $menu_items_move   = [];

        if (rex::getUser()->getComplexPerm('modules')->hasPerm($moduleId)) {

            $templateHasModule = rex_template::hasModule($this->template_attributes, $this->ctype, $moduleId);
            if ($templateHasModule) {
                // edit
                $item = [];
                $item['hidden_label']         = rex_i18n::msg('module') . ' ' . $moduleName . ' ' . rex_i18n::msg('edit');
                $item['url']                  = $context->getUrl(['function' => 'edit']) . $fragment;
                $item['attributes']['class'][] = 'btn-primary';
                $item['attributes']['title']  = rex_i18n::msg('edit');
                $item['icon']                 = 'edit';
                $menu_items_action[] = $item;
            }

            // delete
            $item = [];
            $item['hidden_label']          = rex_i18n::msg('module') . ' ' . $moduleName . ' ' . rex_i18n::msg('delete');
            $item['url']                   = $context->getUrl(['function' => 'delete', 'save' => 1]) . $fragment;
            $item['attributes']['class'][] = 'btn-danger';
            $item['attributes']['title']   = rex_i18n::msg('delete');
            $item['icon']                  = 'delete';
            $menu_items_action[] = $item;

            if ($templateHasModule && rex::getUser()->hasPerm('moveSlice[]')) {

                // moveup
                $item = [];
                $item['hidden_label']          = rex_i18n::msg('module') . ' ' . $moduleName . ' ' . rex_i18n::msg('move_slice_up');
                $item['url']                   = $context->getUrl(['upd' => time(), 'rex-api-call' => 'content_move_slice', 'direction' => 'moveup']) . $fragment;
                $item['attributes']['class'][] = 'btn-default';
                $item['attributes']['title']   = rex_i18n::msg('edit');
                $item['icon']                  = 'up';
                $menu_items_move[] = $item;


                // movedown
                $item = [];
                $item['hidden_label']          = rex_i18n::msg('module') . ' ' . $moduleName . ' ' . rex_i18n::msg('move_slice_down');
                $item['url']                   = $context->getUrl(['upd' => time(), 'rex-api-call' => 'content_move_slice', 'direction' => 'movedown']) . $fragment;
                $item['attributes']['class'][] = 'btn-default';
                $item['attributes']['title']   = rex_i18n::msg('delete');
                $item['icon']                  = 'down';
                $menu_items_move[] = $item;
            }

        } else {
            $header_right .= rex_i18n::msg('no_editing_rights') . ' ' . $moduleName;
        }

        // ----- EXTENSION POINT
        $menu_items_ep = [];
        $menu_items_ep = rex_extension::registerPoint(new rex_extension_point(
            'ART_SLICE_MENU',
                $menu_items_ep,
            [
                'article_id' => $this->article_id,
                'clang' => $this->clang,
                'ctype' => $sliceCtype,
                'module_id' => $moduleId,
                'slice_id' => $sliceId,
                'perm' => rex::getUser()->getComplexPerm('modules')->hasPerm($moduleId)
            ]
        ));

        if (count($menu_items_action) > 0) {

            $fragment = new rex_fragment();
            $fragment->setVar('items', $menu_items_action, false);
            $header_right .= $fragment->parse('slice_menu_action.php');
        }

        if (count($menu_items_ep) > 0) {
            $fragment = new rex_fragment();
            $fragment->setVar('items', $menu_items_ep, false);
            $header_right .= $fragment->parse('slice_menu_ep.php');
        }

        if (count($menu_items_move) > 0) {
            $fragment = new rex_fragment();
            $fragment->setVar('items', $menu_items_move, false);
            $header_right .= $fragment->parse('slice_menu_move.php');
        }

        $header_right = $header_right != '' ? '<div class="col-md-4 text-right">' . $header_right . '</div>' : '';

        return '<div class="row"><div class="col-md-8"><div class="panel-title">' . $moduleName . '</div></div>' . $header_right . '</div>';

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

        $panel = '<form id="slice' . $sliceId . '" action="' . rex_url::backendController() . '" method="get">
                        <fieldset>
                            <legend><span>' . rex_i18n::msg('add_block') . '</span></legend>
                            <input type="hidden" name="page" value="content/edit" />
                            <input type="hidden" name="article_id" value="' . $this->article_id . '" />
                            <input type="hidden" name="clang" value="' . $this->clang . '" />
                            <input type="hidden" name="ctype" value="' . $this->ctype . '" />
                            <input type="hidden" name="slice_id" value="' . $sliceId . '" />
                            <input type="hidden" name="function" value="add" />
                            ' . $this->MODULESELECT[$this->ctype]->get() . '
                            <noscript><button class="btn btn-primary" type="submit" name="btn_add" value="' . rex_i18n::msg('add_block') . '">' . rex_i18n::msg('add_block') . '</button></noscript>
                        </fieldset>
                    </form>';

        $fragment = new rex_fragment();
        $fragment->setVar('body', $panel, false);
        return '<li class="rex-slice-select">' . $fragment->parse('core/page/section.php') . '</li>';

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
                $this->MODULESELECT[$ct_id]->setStyle('class="form-control"');
                $this->MODULESELECT[$ct_id]->setAttribute('onchange', '$(this.form).submit();');
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

            $formElements = [];
            $n = [];
            $n['field'] = '<button class="btn btn-primary" type="submit" name="btn_save" value="1"' . rex::getAccesskey(rex_i18n::msg('add_block'), 'save') . '>' . rex_i18n::msg('add_block') . '</button>';
            $formElements[] = $n;

            $fragment = new rex_fragment();
            $fragment->setVar('elements', $formElements, false);
            $slice_footer = $fragment->parse('core/form/submit.php');



            $panel = '
                <fieldset>
                    <legend>' . rex_i18n::msg('add_block') . '</legend>
                    <input type="hidden" name="function" value="add" />
                    <input type="hidden" name="module_id" value="' . $moduleIdToAdd . '" />
                    <input type="hidden" name="save" value="1" />

                    <div class="rex-form-datas">
                        ' . $moduleInput . '
                    </div>
                </fieldset>
                        ';

            $fragment = new rex_fragment();
            $fragment->setVar('before', $msg, false);
            $fragment->setVar('header', rex_i18n::msg('module') . ': ' . rex_i18n::translate($MOD->getValue('name')), false);
            $fragment->setVar('body', $panel, false);
            $fragment->setVar('footer', $slice_footer, false);
            $slice_content = $fragment->parse('core/page/section.php');

            $slice_content = '
                <li class="rex-slice rex-slice-add">
                    <form action="' . rex_url::currentBackendPage(['article_id' => $this->article_id, 'slice_id' => $sliceId, 'clang' => $this->clang, 'ctype' => $this->ctype]) . '#slice' . $sliceId . '" method="post" id="REX_FORM" enctype="multipart/form-data">
                        ' . $slice_content . '
                    </form>
                    <script type="text/javascript">
                         <!--
                        jQuery(function($) {
                            $(":input:visible:enabled:not([readonly]):first", $("#REX_FORM")).focus();
                        });
                         //-->
                    </script>
                </li>
                ';

        }

        return $slice_content;
    }

    // ----- EDIT Slice
    protected function editSlice($RE_CONTS, $RE_MODUL_IN, $RE_CTYPE, $RE_MODUL_ID)
    {

        $formElements = [];

        $n = [];
        $n['field'] = '<button class="btn btn-primary" type="submit" name="btn_save" value="1"' . rex::getAccesskey(rex_i18n::msg('save_block'), 'save') . '>' . rex_i18n::msg('save_block') . '</button>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="btn btn-primary" type="submit" name="btn_update" value="1"' . rex::getAccesskey(rex_i18n::msg('update_block'), 'apply') . '>' . rex_i18n::msg('update_block') . '</button>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $slice_footer = $fragment->parse('core/form/submit.php');

        $panel = '            
                <fieldset>
                    <legend>' . rex_i18n::msg('edit_block') . '</legend>
                    <input type="hidden" name="module_id" value="' . $RE_MODUL_ID . '" />
                    <input type="hidden" name="function" value="edit" />
                    <input type="hidden" name="save" value="1" />
                    <input type="hidden" name="update" value="0" />

                    <div class="rex-form-datas">
                        ' . $this->getStreamOutput('module/' . $RE_MODUL_ID . '/input', $RE_MODUL_IN) . '
                    </div>
                </fieldset>

            </form>';

        $fragment = new rex_fragment();
        $fragment->setVar('header', rex_i18n::msg('edit_block'), false);
        $fragment->setVar('body', $panel, false);
        $fragment->setVar('footer', $slice_footer, false);
        $slice_content = $fragment->parse('core/page/section.php');

        $slice_content = '
            <li class="rex-slice rex-slice-edit">
                <form enctype="multipart/form-data" action="' . rex_url::currentBackendPage(['article_id' => $this->article_id, 'slice_id' => $RE_CONTS, 'ctype' => $RE_CTYPE, 'clang' => $this->clang]) . '#slice' . $RE_CONTS . '" method="post" id="REX_FORM">
                    ' . $slice_content . '
                </form>
                <script type="text/javascript">
                     <!--
                    jQuery(function($) {
                        $(":input:visible:enabled:not([readonly]):first", $("#REX_FORM")).focus();
                    });
                     //-->
                </script>
            </li>
            ';

        return $slice_content;
    }
}
