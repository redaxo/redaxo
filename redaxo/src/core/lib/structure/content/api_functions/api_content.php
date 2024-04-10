<?php

use Redaxo\Core\Content\Article;
use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_content_move_slice extends rex_api_function
{
    public function execute()
    {
        $articleId = rex_request('article_id', 'int');
        $clang = rex_request('clang', 'int');
        $sliceId = rex_request('slice_id', 'int');
        $direction = rex_request('direction', 'string');

        $ooArt = Article::get($articleId, $clang);
        if (!$ooArt instanceof Article) {
            throw new rex_api_exception('Unable to find article with id "' . $articleId . '" and clang "' . $clang . '"!');
        }
        $categoryId = $ooArt->getCategoryId();

        $user = Core::requireUser();

        // check permissions
        if (!$user->hasPerm('moveSlice[]')) {
            throw new rex_api_exception(I18n::msg('no_rights_to_this_function'));
        }

        if (!$user->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            throw new rex_api_exception(I18n::msg('no_rights_to_this_function'));
        }

        // modul und rechte vorhanden ?
        $CM = Sql::factory();
        $CM->setQuery('select * from ' . Core::getTablePrefix() . 'article_slice left join ' . Core::getTablePrefix() . 'module on ' . Core::getTablePrefix() . 'article_slice.module_id=' . Core::getTablePrefix() . 'module.id where ' . Core::getTablePrefix() . 'article_slice.id=? and clang_id=?', [$sliceId, $clang]);
        if (1 != $CM->getRows()) {
            throw new rex_api_exception(I18n::msg('module_not_found'));
        }
        $moduleId = (int) $CM->getValue(Core::getTablePrefix() . 'article_slice.module_id');

        // ----- RECHTE AM MODUL ?
        if ($user->getComplexPerm('modules')->hasPerm($moduleId)) {
            $message = rex_content_service::moveSlice($sliceId, $clang, $direction);
        } else {
            throw new rex_api_exception(I18n::msg('no_rights_to_this_function'));
        }
        return new rex_api_result(true, $message);
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
