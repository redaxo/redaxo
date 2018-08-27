<?php

/**
 * @package redaxo\core
 */
class rex_minibar_structure_content_frontend extends rex_minibar_element
{
    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $article = rex_article::getCurrent();

        if (!$article) {
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
        if (rex::isBackend()) {
            $articleLink = '<a href="'.rex_getUrl($article->getId(), $article->getClangId()).'">'.rex_i18n::msg('structure_content_minibar_article_show').'</a>';
        }

        $articlePath = [];
        foreach ($article->getParentTree() as $tree) {
            $articlePath[] = '<a href="'.$tree->getUrl().'">'.rex_escape($tree->getName()).'</a>';
        }

        return
        '<div class="rex-minibar-item">
            <span class="rex-minibar-icon">
                <i class="rex-icon rex-icon-article"></i>
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
                    <span>'.($article->isOnline() ? rex_i18n::msg('status_online') : rex_i18n::msg('status_offline')).'</span>
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
