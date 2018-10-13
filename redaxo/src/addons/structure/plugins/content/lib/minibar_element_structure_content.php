<?php

/**
 * @package redaxo\core
 */
class rex_minibar_element_structure_content extends rex_minibar_element
{
    /**
     * {@inheritdoc}
     */
    public function render()
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

        if (!$article) {
            $article = rex_article::getSiteStartArticle();
        }

        if (!$article instanceof rex_article) {
            return '';
        }

        // Return if user have no rights to the site start article
        if (rex::isBackend() && rex::getUser() && !rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($article->getCategoryId())) {
            return
                '<div class="rex-minibar-item">
                    <span class="rex-minibar-icon">
                        <i class="rex-icon rex-icon-article"></i>
                    </span>
                    <span class="rex-minibar-value">
                        0
                    </span>
                </div>';
        }

        $articleLink = '<a href="'.rex_url::backendPage('content/edit', ['article_id' => $article->getId(), 'clang' => $article->getClangId(), 'mode' => 'edit']).'">'.rex_i18n::msg('structure_content_minibar_article_edit').' </a>';
        if (rex::getUser() && !rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($article->getCategoryId())) {
            $articleLink = rex_i18n::msg('no_rights_to_edit');
        } elseif (rex::isBackend()) {
            $articleLink = '<a href="'.rex_getUrl($article->getId(), $article->getClangId()).'">'.rex_i18n::msg('structure_content_minibar_article_show').'</a>';
        }

        $articlePath = [];
        $tree = $article->getParentTree();
        if (!$article->isStartarticle()) {
            $tree[] = $article;
        }
        foreach ($tree as $parent) {
            $id = $parent->getId();
            $item = rex_escape($parent->getName());
            if (rex::isBackend() && rex::getUser() && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($id) && $parent->isStartarticle()) {
                $item = '<a href="'.rex_url::backendPage('structure', ['category_id' => $id, 'clang' => $clangId]).'">'.rex_escape($parent->getName()).'</a>';
            } elseif (!rex::isBackend()) {
                $item = '<a href="'.$parent->getUrl().'">'.rex_escape($parent->getName()).'</a>';
            }
            $articlePath[] = $item;
        }

        return
        '<div class="rex-minibar-item">
            <span class="rex-minibar-icon">
                <i class="rex-minibar-icon--fa rex-minibar-icon--fa-file-text-o"></i>
            </span>
            <span class="rex-minibar-value">
                '.$article->getId().'
            </span>
        </div>
        <div class="rex-minibar-info">
            <div class="rex-minibar-info-group">
                <div class="rex-minibar-info-piece">
                    <b>'.rex_i18n::msg('structure_article_name').'</b>
                    <span>'.rex_escape($article->getName()).'</span>
                </div>
                <div class="rex-minibar-info-piece">
                    <b>'.rex_i18n::msg('structure_status').'</b>
                    <span class="rex-minibar-status-'.($article->isOnline() ? 'green' : 'red').'">'.($article->isOnline() ? rex_i18n::msg('status_online') : rex_i18n::msg('status_offline')).'</span>                    
                </div>
                <div class="rex-minibar-info-piece">
                    <b>'.rex_i18n::msg('structure_path').'</b>
                    <span>'.implode(' / ', $articlePath).'</span>
                </div>
                <div class="rex-minibar-info-piece">
                    <b></b>
                    <span>'.$articleLink.'</span>
                </div>
            </div>
        </div>
        ';
    }
}
