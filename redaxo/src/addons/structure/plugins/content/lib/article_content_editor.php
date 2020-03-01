<?php

/**
 * Erweiterung eines Artikels um slicemanagement.
 *
 * @package redaxo\structure\content
 */
class rex_article_content_editor extends rex_article_content
{
    private $MODULESELECT;
    private $sliceAddPosition = 0;

    public function __construct($article_id = null, $clang = null)
    {
        parent::__construct($article_id, $clang);
    }

    /**
     * {@inheritdoc}
     */
    protected function outputSlice(rex_sql $artDataSql, $moduleIdToAdd)
    {
        if ('edit' != $this->mode) {
            // ----- wenn mode nicht edit
            $slice_content = parent::outputSlice(
                $artDataSql,
                $moduleIdToAdd
            );
        } else {
            $sliceId = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.id');
            $sliceCtype = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.ctype_id');
            $sliceStatus = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.status');

            $moduleInput = $artDataSql->getValue(rex::getTablePrefix() . 'module.input');
            $moduleOutput = $artDataSql->getValue(rex::getTablePrefix() . 'module.output');
            $moduleId = $artDataSql->getValue(rex::getTablePrefix() . 'module.id');

            // ----- add select box einbauen
            $slice_content = $this->getModuleSelect($sliceId);

            if ('add' == $this->function && $this->slice_id == $sliceId) {
                $slice_content .= $this->addSlice($sliceId, $moduleIdToAdd);
            }

            $panel = '';
            // ----- Display message at current slice
            //if(rex::getUser()->getComplexPerm('modules')->hasPerm($moduleId)) {
            if ('add' != $this->function && $this->slice_id == $sliceId) {
                $msg = '';
                if ('' != $this->warning) {
                    $msg .= rex_view::warning($this->warning);
                }
                if ('' != $this->info) {
                    $msg .= rex_view::success($this->info);
                }
                $panel .= $msg;
            }
            //}

            // ----- EDIT/DELETE BLOCK - Wenn Rechte vorhanden
            if (rex::getUser()->getComplexPerm('modules')->hasPerm($moduleId)) {
                if ('edit' == $this->function && $this->slice_id == $sliceId) {
                    // **************** Aktueller Slice

                    // ----- PRE VIEW ACTION [EDIT]
                    $action = new rex_article_action($moduleId, 'edit', $artDataSql);
                    if ('post' == rex_request_method() && 'edit' == rex_request('function', 'string')) {
                        $action->setRequestValues();
                    }
                    $action->exec(rex_article_action::PREVIEW);
                    // ----- / PRE VIEW ACTION

                    $moduleInput = $this->replaceVars($artDataSql, $moduleInput);
                    return $slice_content . $this->editSlice($sliceId, $moduleInput, $sliceCtype, $moduleId, $artDataSql);
                }
                // Modulinhalt ausgeben
                $moduleOutput = $this->replaceVars($artDataSql, $moduleOutput);
                $panel .= $this->getWrappedModuleOutput($moduleId, $moduleOutput);
            } else {
                // ----- hat keine rechte an diesem modul, einfach ausgeben
                $moduleOutput = $this->replaceVars($artDataSql, $moduleOutput);
                $panel .= $this->getWrappedModuleOutput($moduleId, $moduleOutput);
            }

            $fragment = new rex_fragment();
            $fragment->setVar('title', $this->getSliceHeading($artDataSql), false);
            $fragment->setVar('options', $this->getSliceMenu($artDataSql), false);
            $fragment->setVar('body', $panel, false);
            $statusName = $sliceStatus ? 'online' : 'offline';
            $slice_content .= '<li class="rex-slice rex-slice-output rex-slice-'.$statusName.'" id="slice'.$sliceId.'">' . $fragment->parse('core/page/section.php') . '</li>';
        }

        return $slice_content;
    }

    /**
     * Returns the slice heading.
     *
     * @param rex_sql $artDataSql rex_sql istance containing all the slice and module information
     *
     * @return string
     */
    private function getSliceHeading(rex_sql $artDataSql)
    {
        $sliceId = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.id');
        $sliceCtype = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.ctype_id');

        $moduleId = $artDataSql->getValue(rex::getTablePrefix() . 'module.id');
        $moduleName = rex_i18n::translate($artDataSql->getValue(rex::getTablePrefix() . 'module.name'));

        return $moduleName;
    }

    /**
     * Returns the slice menu.
     *
     * @param rex_sql $artDataSql rex_sql istance containing all the slice and module information
     *
     * @return string
     */
    private function getSliceMenu(rex_sql $artDataSql)
    {
        $sliceId = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.id');
        $sliceCtype = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.ctype_id');
        $sliceStatus = $artDataSql->getValue(rex::getTablePrefix() . 'article_slice.status');

        $moduleId = $artDataSql->getValue(rex::getTablePrefix() . 'module.id');
        $moduleName = rex_i18n::translate($artDataSql->getValue(rex::getTablePrefix() . 'module.name'));

        $context = new rex_context([
            'page' => rex_be_controller::getCurrentPage(),
            'article_id' => $this->article_id,
            'slice_id' => $sliceId,
            'clang' => $this->clang,
            'ctype' => $this->ctype,
        ]);
        $fragment = '#slice' . $sliceId;

        $header_right = '';

        $menu_items_action = [];
        $menu_items_status = [];
        $menu_items_move = [];

        if (rex::getUser()->getComplexPerm('modules')->hasPerm($moduleId)) {
            $templateHasModule = rex_template::hasModule($this->template_attributes, $this->ctype, $moduleId);
            if ($templateHasModule) {
                // edit
                $item = [];
                $item['label'] = rex_i18n::msg('edit');
                $item['url'] = $context->getUrl(['function' => 'edit']) . $fragment;
                $item['attributes']['class'][] = 'btn-edit';
                $item['attributes']['title'] = rex_i18n::msg('edit');
                $menu_items_action[] = $item;
            }

            // delete
            $item = [];
            $item['label'] = rex_i18n::msg('delete');
            $item['url'] = $context->getUrl(['function' => 'delete', 'save' => 1]) . $fragment;
            $item['attributes']['class'][] = 'btn-delete';
            $item['attributes']['title'] = rex_i18n::msg('delete');
            $item['attributes']['data-confirm'] = rex_i18n::msg('confirm_delete_block');
            $menu_items_action[] = $item;

            // status
            $item = [];
            $statusName = $sliceStatus ? 'online' : 'offline';
            $item['label'] = rex_i18n::msg('status_'.$statusName);
            $item['url'] = $context->getUrl(['status' => $sliceStatus ? 0 : 1] + rex_api_content_slice_status::getUrlParams());
            $item['attributes']['class'][] = 'btn-default';
            $item['attributes']['class'][] = 'rex-'.$statusName;
            $menu_items_status[] = $item;

            if ($templateHasModule && rex::getUser()->hasPerm('moveSlice[]')) {
                // moveup
                $item = [];
                $item['hidden_label'] = rex_i18n::msg('module') . ' ' . $moduleName . ' ' . rex_i18n::msg('move_slice_up');
                $item['url'] = $context->getUrl(['upd' => time(), 'direction' => 'moveup'] + rex_api_content_move_slice::getUrlParams()) . $fragment;
                $item['attributes']['class'][] = 'btn-move';
                $item['attributes']['title'] = rex_i18n::msg('move_slice_up');
                $item['icon'] = 'up';
                $menu_items_move[] = $item;

                // movedown
                $item = [];
                $item['hidden_label'] = rex_i18n::msg('module') . ' ' . $moduleName . ' ' . rex_i18n::msg('move_slice_down');
                $item['url'] = $context->getUrl(['upd' => time(), 'direction' => 'movedown'] + rex_api_content_move_slice::getUrlParams()) . $fragment;
                $item['attributes']['class'][] = 'btn-move';
                $item['attributes']['title'] = rex_i18n::msg('move_slice_down');
                $item['icon'] = 'down';
                $menu_items_move[] = $item;
            }
        } else {
            $header_right .= rex_i18n::msg('no_editing_rights') . ' ' . $moduleName;
        }

        // ----- EXTENSION POINT
        $menu_items_ep = [];
        $menu_items_ep = rex_extension::registerPoint(new rex_extension_point(
            'STRUCTURE_CONTENT_SLICE_MENU',
                $menu_items_ep,
            [
                'article_id' => $this->article_id,
                'clang' => $this->clang,
                'ctype' => $sliceCtype,
                'module_id' => $moduleId,
                'slice_id' => $sliceId,
                'perm' => rex::getUser()->getComplexPerm('modules')->hasPerm($moduleId),
            ]
        ));

        if (count($menu_items_action) > 0) {
            $fragment = new rex_fragment();
            $fragment->setVar('items', $menu_items_action, false);
            $header_right .= $fragment->parse('slice_menu_action.php');
        }

        if (count($menu_items_status) > 0) {
            $fragment = new rex_fragment();
            $fragment->setVar('items', $menu_items_status, false);
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

        //$header_right = $header_right != '' ? '<div class="col-md-4 text-right">' . $header_right . '</div>' : '';

        return $header_right;
    }

    /**
     * Wraps the output of a module.
     *
     * @param int    $moduleId     The id of the module
     * @param string $moduleOutput The output of the module
     *
     * @return string
     */
    private function getWrappedModuleOutput($moduleId, $moduleOutput)
    {
        return $this->getStreamOutput('module/' . $moduleId . '/output', $moduleOutput);
    }

    /**
     * @return string
     */
    private function getModuleSelect($sliceId)
    {
        // ----- BLOCKAUSWAHL - SELECT
        $context = new rex_context([
            'page' => rex_be_controller::getCurrentPage(),
            'article_id' => $this->article_id,
            'clang' => $this->clang,
            'ctype' => $this->ctype,
            'slice_id' => $sliceId,
            'function' => 'add',
        ]);

        $position = ++$this->sliceAddPosition;

        $items = [];
        if (isset($this->MODULESELECT[$this->ctype])) {
            foreach ($this->MODULESELECT[$this->ctype] as $module) {
                $item = [];
                $item['id'] = $module['id'];
                $item['title'] = rex_escape($module['name']);
                $item['href'] = $context->getUrl(['module_id' => $module['id']]) . '#slice-add-pos-' . $position;
                $items[] = $item;
            }
        }

        $fragment = new rex_fragment();
        $fragment->setVar('block', true);
        $fragment->setVar('button_label', rex_i18n::msg('add_block'));
        $fragment->setVar('items', $items, false);
        $select = $fragment->parse('module_select.php');
        $select = rex_extension::registerPoint(new rex_extension_point(
            'STRUCTURE_CONTENT_MODULE_SELECT',
                $select,
            [
                'page' => rex_be_controller::getCurrentPage(),
                'article_id' => $this->article_id,
                'clang' => $this->clang,
                'ctype' => $this->ctype,
                'slice_id' => $sliceId,
            ]
        ));
        return '<li class="rex-slice rex-slice-select" id="slice-add-pos-' . $position . '">' . $select . '</li>';
    }

    /**
     * {@inheritdoc}
     */
    protected function preArticle($articleContent, $module_id)
    {
        // ---------- moduleselect: nur module nehmen auf die der user rechte hat
        if ('edit' == $this->mode) {
            $MODULE = rex_sql::factory();
            $modules = $MODULE->getArray('select * from ' . rex::getTablePrefix() . 'module order by name');

            $template_ctypes = isset($this->template_attributes['ctype']) ? $this->template_attributes['ctype'] : [];
            // wenn keine ctyes definiert sind, gibt es immer den CTYPE=1
            if (0 == count($template_ctypes)) {
                $template_ctypes = [1 => 'default'];
            }

            $this->MODULESELECT = [];
            foreach ($template_ctypes as $ct_id => $ct_name) {
                foreach ($modules as $m) {
                    if (rex::getUser()->getComplexPerm('modules')->hasPerm($m['id'])) {
                        if (rex_template::hasModule($this->template_attributes, $ct_id, $m['id'])) {
                            $this->MODULESELECT[$ct_id][] = ['name' => rex_i18n::translate($m['name'], false), 'id' => $m['id']];
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
        if ('edit' == $this->mode) {
            if ('add' == $this->function && $this->slice_id == $LCTSL_ID) {
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

    /**
     * @return string
     */
    protected function addSlice($sliceId, $moduleIdToAdd)
    {
        $MOD = rex_sql::factory();
        $MOD->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'module WHERE id="' . $moduleIdToAdd . '"');

        if (1 != $MOD->getRows()) {
            $slice_content = rex_view::warning(rex_i18n::msg('module_doesnt_exist'));
        } else {
            $initDataSql = rex_sql::factory();
            $initDataSql
                ->setValue('module_id', $moduleIdToAdd)
                ->setValue('ctype_id', $this->ctype);

            // ----- PRE VIEW ACTION [ADD]
            $action = new rex_article_action($moduleIdToAdd, 'add', $initDataSql);
            $action->setRequestValues();
            $action->exec(rex_article_action::PREVIEW);
            // ----- / PRE VIEW ACTION

            $moduleInput = $this->replaceVars($initDataSql, $MOD->getValue('input'));

            $moduleInput = $this->getStreamOutput('module/' . $moduleIdToAdd . '/input', $moduleInput);

            $msg = '';
            if ('' != $this->warning) {
                $msg .= rex_view::warning($this->warning);
            }
            if ('' != $this->info) {
                $msg .= rex_view::success($this->info);
            }

            $formElements = [];

            $n = [];
            $n['field'] = '<a class="btn btn-abort" href="' . rex_url::currentBackendPage(['article_id' => $this->article_id, 'slice_id' => $sliceId, 'clang' => $this->clang, 'ctype' => $this->ctype]) . '#slice-add-pos-' . $this->sliceAddPosition . '">' . rex_i18n::msg('form_abort') . '</a>';
            $formElements[] = $n;

            $n = [];
            $n['field'] = '<button class="btn btn-save" type="submit" name="btn_save" value="1"' . rex::getAccesskey(rex_i18n::msg('add_block'), 'save') . '>' . rex_i18n::msg('add_block') . '</button>';
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

                    <div class="rex-slice-input">
                        ' . $moduleInput . '
                    </div>
                </fieldset>
                        ';

            $fragment = new rex_fragment();
            $fragment->setVar('before', $msg, false);
            $fragment->setVar('class', 'add', false);
            $fragment->setVar('title', rex_i18n::msg('module') . ': ' . rex_i18n::translate($MOD->getValue('name')), false);
            $fragment->setVar('body', $panel, false);
            $fragment->setVar('footer', $slice_footer, false);
            $slice_content = $fragment->parse('core/page/section.php');

            $slice_content = '
                <li class="rex-slice rex-slice-add">
                    <form action="' . rex_url::currentBackendPage(['article_id' => $this->article_id, 'slice_id' => $sliceId, 'clang' => $this->clang, 'ctype' => $this->ctype]) . '#slice-add-pos-' . $this->sliceAddPosition . '" method="post" id="REX_FORM" enctype="multipart/form-data">
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

    /**
     * @return string
     */
    protected function editSlice($RE_CONTS, $RE_MODUL_IN, $RE_CTYPE, $RE_MODUL_ID, $artDataSql)
    {
        $msg = '';
        if ($this->slice_id == $RE_CONTS) {
            if ('' != $this->warning) {
                $msg .= rex_view::warning($this->warning);
            }
            if ('' != $this->info) {
                $msg .= rex_view::success($this->info);
            }
        }

        $formElements = [];

        $n = [];
        $n['field'] = '<a class="btn btn-abort" href="' . rex_url::currentBackendPage(['article_id' => $this->article_id, 'slice_id' => $RE_CONTS, 'ctype' => $RE_CTYPE, 'clang' => $this->clang]) . '#slice' . $RE_CONTS . '">' . rex_i18n::msg('form_abort') . '</a>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="btn btn-save" type="submit" name="btn_save" value="1"' . rex::getAccesskey(rex_i18n::msg('save_and_close_tooltip'), 'save') . '>' . rex_i18n::msg('save_block') . '</button>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="btn btn-apply" type="submit" name="btn_update" value="1"' . rex::getAccesskey(rex_i18n::msg('save_and_goon_tooltip'), 'apply') . '>' . rex_i18n::msg('update_block') . '</button>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $slice_footer = $fragment->parse('core/form/submit.php');

        $panel = '
                <fieldset>
                    <legend>' . rex_i18n::msg('edit_block') . '</legend>
                    <input type="hidden" name="module_id" value="' . $RE_MODUL_ID . '" />
                    <input type="hidden" name="save" value="1" />
                    <input type="hidden" name="update" value="0" />

                    <div class="rex-slice-input">
                        ' . $msg . $this->getStreamOutput('module/' . $RE_MODUL_ID . '/input', $RE_MODUL_IN) . '
                    </div>
                </fieldset>

            </form>';

        $fragment = new rex_fragment();
        $fragment->setVar('class', 'edit', false);
        $fragment->setVar('title', $this->getSliceHeading($artDataSql), false);
        $fragment->setVar('options', $this->getSliceMenu($artDataSql), false);
        $fragment->setVar('body', $panel, false);
        $fragment->setVar('footer', $slice_footer, false);
        $slice_content = $fragment->parse('core/page/section.php');

        $slice_content = '
            <li class="rex-slice rex-slice-edit" id="slice' . $RE_CONTS . '">
                <form enctype="multipart/form-data" action="' . rex_url::currentBackendPage(['article_id' => $this->article_id, 'slice_id' => $RE_CONTS, 'ctype' => $RE_CTYPE, 'clang' => $this->clang, 'function' => 'edit']) . '#slice' . $RE_CONTS . '" method="post" id="REX_FORM">
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
