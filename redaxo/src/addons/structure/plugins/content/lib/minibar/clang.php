<?php

/**
 * @package redaxo\structure\content\minibar
 */
class rex_minibar_element_structure_clang extends rex_minibar_lazy_element
{
    protected function renderFirstView()
    {
        return
        '<div class="rex-minibar-item">
            <span class="rex-minibar-icon">
                <i class="rex-minibar-icon--fa rex-minibar-icon--fa-flag"></i>
            </span>
            <span class="rex-minibar-value">
                '.rex_clang::getCurrent()->getCode().'
            </span>
        </div>';
    }

    protected function renderComplete()
    {
        $clangId = rex_request('clang', 'int');
        $clangId = rex_clang::exists($clangId) ? $clangId : rex_clang::getStartId();

        $article = rex_article::getCurrent();
        if (rex::isBackend()) {
            $article = rex_article::get(rex_request('article_id', 'int'), $clangId);

            if (!$article) {
                $article = rex_article::get(rex_request('category_id', 'int'), $clangId);
            }
        }

        $items = [];
        foreach (rex_clang::getAll() as $id => $clang) {
            if (rex::getUser()->getComplexPerm('clang')->hasPerm($id)) {
                $clangLabel = rex_escape(ucfirst($clang->getName()));

                $editLink = '';
                if (rex::getUser()->isAdmin()) {
                    $params = [
                        'page' => 'system/lang',
                        'func' => 'editclang',
                        'clang_id' => $id,
                    ];
                    $editLink = '<a href="'.rex_url::backendController($params).'#clang">'.rex_i18n::msg('edit').'</a>';
                }

                $item = '
                    <div class="rex-minibar-info-piece">
                        <b><a href="'.rex_getUrl('', $clang->getId()).'">'.$clangLabel.'</a></b>
                        <span><span class="rex-minibar-status-'.($clang->isOnline() ? 'green' : 'red').'">'.($clang->isOnline() ? rex_i18n::msg('status_online') : rex_i18n::msg('status_offline')).'</span> '.$editLink.'</span>                    
                    </div>';

                if (rex::isBackend() && rex::getUser()) {
                    if ($article && in_array(rex_be_controller::getCurrentPagePart(1), ['structure', 'content']) && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($article->getCategoryId())) {
                        $context = new rex_context([
                            'page' => rex_be_controller::getCurrentPage(),
                            'category_id' => $article->getCategoryId(),
                            'clang' => $id,
                        ]);

                        if (rex_be_controller::getCurrentPagePart(1) == 'content') {
                            $context->setParam('article_id', $article->getId());
                        }

                        $clangLabel = '<a href="'.$context->getUrl().'">'.$clangLabel.'</a>';
                    }

                    $item = '
                        <div class="rex-minibar-info-piece">
                            <b>'.$clangLabel.'</b>
                            <span>'.$editLink.' <span class="rex-minibar-status-'.($clang->isOnline() ? 'green' : 'red').'">'.($clang->isOnline() ? rex_i18n::msg('status_online') : rex_i18n::msg('status_offline')).'</span></span>                    
                        </div>';
                }
                $items[] = $item;
            }
        }

        $clangEdit = '';
        if (rex::getUser()->isAdmin()) {
            $clangEdit = '
            <div class="rex-minibar-info-group">
                <div class="rex-minibar-info-piece">
                    <b></b>
                    <span><b><a href="'.rex_url::backendController(['page' => 'system/lang']).'">'.rex_i18n::msg('languages_edit').'</a></b></span>                    
                </div>
            </div>';
        }

        $groups = rex_extension::registerPoint(new rex_extension_point('MINIBAR_CLANG', '', [
            'clang' => rex_clang::getCurrent(),
        ]));

        return
        '<div class="rex-minibar-item">
            <span class="rex-minibar-icon">
                <i class="rex-minibar-icon--fa rex-minibar-icon--fa-flag"></i>
            </span>
            <span class="rex-minibar-value">
                '.rex_clang::getCurrent()->getCode().'
            </span>
        </div>
        <div class="rex-minibar-info">
            '.$clangEdit.'
            <div class="rex-minibar-info-group">
                '.implode('', $items).'
            </div>
            '.$groups.'
        </div>
        ';
    }
}
